<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShopTest\Yves\SecurityBlockerPage\Plugin;

use Codeception\Test\Unit;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use Spryker\Service\Container\ContainerInterface;
use Spryker\Shared\EventDispatcher\EventDispatcherInterface;
use Spryker\Yves\Kernel\Container;
use SprykerShop\Yves\SecurityBlockerPage\Plugin\EventDispatcher\SecurityBlockerCustomerEventDispatcherPlugin;
use SprykerShop\Yves\SecurityBlockerPage\SecurityBlockerPageConfig;
use SprykerShop\Yves\SecurityBlockerPage\SecurityBlockerPageDependencyProvider;
use SprykerShop\Yves\SecurityBlockerPage\SecurityBlockerPageFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SecurityBlockerCustomerEventDispatcherPluginTest extends Unit
{
    protected const string UNKNOWN_LOGIN_PROTECTION_STRATEGY = 'unknown';

    /**
     * @var \SprykerShopTest\Yves\SecurityBlockerPage\SecurityBlockerPageTester
     */
    protected $tester;

    /**
     * @var \SprykerShop\Yves\SecurityBlockerPage\Plugin\EventDispatcher\SecurityBlockerCustomerEventDispatcherPlugin
     */
    protected $securityBlockerCustomerEventDispatcherPlugin;

    protected function setUp(): void
    {
        parent::setUp();

        $container = new Container();
        $securityBlockerPageDependencyProvider = new SecurityBlockerPageDependencyProvider();
        $securityBlockerPageDependencyProvider->provideDependencies($container);

        $securityBlockerPageFactoryMock = $this->getMockBuilder(SecurityBlockerPageFactory::class)->getMock();
        $securityBlockerPageFactoryMock->setContainer($container);

        $this->securityBlockerCustomerEventDispatcherPlugin = new SecurityBlockerCustomerEventDispatcherPlugin();
        $this->securityBlockerCustomerEventDispatcherPlugin->setFactory($securityBlockerPageFactoryMock);
    }

    public function testSecurityBlockerCustomerEventDispatcherPluginWillAddSubscriber(): void
    {
        // Arrange
        $container = new Container();
        $securityBlockerPageDependencyProvider = new SecurityBlockerPageDependencyProvider();
        $securityBlockerPageDependencyProvider->provideDependencies($container);

        $securityBlockerPageFactoryMock = $this->getMockBuilder(SecurityBlockerPageFactory::class)->getMock();
        $securityBlockerPageFactoryMock->setContainer($container);

        $securityBlockerCustomerEventDispatcherPlugin = new SecurityBlockerCustomerEventDispatcherPlugin();
        $securityBlockerCustomerEventDispatcherPlugin->setFactory($securityBlockerPageFactoryMock);

        $eventDispatcherMock = $this->getMockBuilder(EventDispatcherInterface::class)->getMock();
        $eventDispatcherMock->expects($this->once())
            ->method('addSubscriber');

        // Act
        $securityBlockerCustomerEventDispatcherPlugin->extend(
            $eventDispatcherMock,
            $this->createMock(ContainerInterface::class),
        );
    }

    public function testPluginAddsTheIpOnlySubscriberForTheIpLoginProtectionStrategy(): void
    {
        // Arrange
        $eventSubscriber = $this->createMock(EventSubscriberInterface::class);
        $securityBlockerPageFactoryMock = $this->createFactoryMock(SecurityBlockerPageConfig::LOGIN_PROTECTION_STRATEGY_IP);
        $securityBlockerPageFactoryMock->method('createSecurityBlockerCustomerEventSubscriber')->willReturn($eventSubscriber);
        $eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);

        // Assert
        $securityBlockerPageFactoryMock->expects($this->never())->method('createSecurityBlockerCustomerLoginEventSubscriber');
        $eventDispatcherMock->expects($this->once())->method('addSubscriber')->with($this->identicalTo($eventSubscriber));

        // Act
        $this->createPlugin($securityBlockerPageFactoryMock)->extend($eventDispatcherMock, $this->createMock(ContainerInterface::class));
    }

    public function testPluginAddsTheLoginSubscriberForTheIpAndAccountLoginProtectionStrategy(): void
    {
        // Arrange
        $eventSubscriber = $this->createMock(EventSubscriberInterface::class);
        $securityBlockerPageFactoryMock = $this->createFactoryMock(SecurityBlockerPageConfig::LOGIN_PROTECTION_STRATEGY_IP_AND_ACCOUNT);
        $securityBlockerPageFactoryMock->method('createSecurityBlockerCustomerLoginEventSubscriber')->willReturn($eventSubscriber);
        $eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);

        // Assert
        $securityBlockerPageFactoryMock->expects($this->never())->method('createSecurityBlockerCustomerEventSubscriber');
        $eventDispatcherMock->expects($this->once())->method('addSubscriber')->with($this->identicalTo($eventSubscriber));

        // Act
        $this->createPlugin($securityBlockerPageFactoryMock)->extend($eventDispatcherMock, $this->createMock(ContainerInterface::class));
    }

    public function testPluginFailsForALoginProtectionStrategyWithoutARegisteredSubscriber(): void
    {
        // Arrange
        $securityBlockerPageFactoryMock = $this->createFactoryMock(static::UNKNOWN_LOGIN_PROTECTION_STRATEGY);

        // Expect
        $this->expectException(InvalidArgumentException::class);

        // Act
        $this->createPlugin($securityBlockerPageFactoryMock)->extend($this->createMock(EventDispatcherInterface::class), $this->createMock(ContainerInterface::class));
    }

    protected function createFactoryMock(string $loginProtectionStrategy): SecurityBlockerPageFactory&MockObject
    {
        $securityBlockerPageConfigMock = $this->createPartialMock(SecurityBlockerPageConfig::class, ['getLoginProtectionStrategy']);
        $securityBlockerPageConfigMock->method('getLoginProtectionStrategy')->willReturn($loginProtectionStrategy);

        $securityBlockerPageFactoryMock = $this->createPartialMock(SecurityBlockerPageFactory::class, [
            'createSecurityBlockerCustomerEventSubscriber',
            'createSecurityBlockerCustomerLoginEventSubscriber',
        ]);
        $securityBlockerPageFactoryMock->setConfig($securityBlockerPageConfigMock);

        return $securityBlockerPageFactoryMock;
    }

    protected function createPlugin(SecurityBlockerPageFactory $securityBlockerPageFactory): SecurityBlockerCustomerEventDispatcherPlugin
    {
        $securityBlockerCustomerEventDispatcherPlugin = new SecurityBlockerCustomerEventDispatcherPlugin();
        $securityBlockerCustomerEventDispatcherPlugin->setFactory($securityBlockerPageFactory);

        return $securityBlockerCustomerEventDispatcherPlugin;
    }
}
