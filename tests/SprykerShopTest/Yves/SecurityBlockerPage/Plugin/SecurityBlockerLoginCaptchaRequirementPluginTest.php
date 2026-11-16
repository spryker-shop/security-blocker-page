<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShopTest\Yves\SecurityBlockerPage\Plugin;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CaptchaRequirementRequestTransfer;
use Generated\Shared\Transfer\SecurityCheckAuthContextTransfer;
use Generated\Shared\Transfer\SecurityCheckAuthResponseTransfer;
use RuntimeException;
use Spryker\Client\SecurityBlocker\Exception\SecurityBlockerException;
use SprykerShop\Yves\SecurityBlockerPage\Dependency\Client\SecurityBlockerPageToSecurityBlockerClientInterface;
use SprykerShop\Yves\SecurityBlockerPage\Plugin\Captcha\SecurityBlockerLoginCaptchaRequirementPlugin;
use SprykerShop\Yves\SecurityBlockerPage\SecurityBlockerPageConfig;
use SprykerShop\Yves\SecurityBlockerPage\SecurityBlockerPageDependencyProvider;
use SprykerShop\Yves\SecurityBlockerPage\SecurityBlockerPageFactory;
use SprykerShopTest\Yves\SecurityBlockerPage\SecurityBlockerPageTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerShopTest
 * @group Yves
 * @group SecurityBlockerPage
 * @group Plugin
 * @group SecurityBlockerLoginCaptchaRequirementPluginTest
 * Add your own group annotations below this line
 */
class SecurityBlockerLoginCaptchaRequirementPluginTest extends Unit
{
    /**
     * @uses \Spryker\Shared\Captcha\CaptchaConfig::CONTEXT_BACKOFFICE_USER_LOGIN
     */
    protected const string OTHER_CAPTCHA_CONTEXT = 'backoffice_user_login';

    protected const string IP = '10.0.0.1';

    protected const string LOGIN_IDENTIFIER = 'customer@spryker.com';

    protected SecurityBlockerPageTester $tester;

    public function testCustomerLoginIsCheckedAgainstTheCustomerTypeAndRealm(): void
    {
        // Arrange
        $securityBlockerClientMock = $this->createMock(SecurityBlockerPageToSecurityBlockerClientInterface::class);

        // Assert
        $securityBlockerClientMock->expects($this->once())
            ->method('checkLoginAttempt')
            ->with($this->callback(fn (SecurityCheckAuthContextTransfer $securityCheckAuthContextTransfer): bool => $securityCheckAuthContextTransfer->getType() === SecurityBlockerPageConfig::SECURITY_BLOCKER_CUSTOMER_ENTITY_TYPE
                && $securityCheckAuthContextTransfer->getRealm() === SecurityBlockerPageConfig::LOGIN_REALM_CUSTOMER
                && $securityCheckAuthContextTransfer->getIp() === static::IP
                && $securityCheckAuthContextTransfer->getLoginIdentifier() === static::LOGIN_IDENTIFIER))
            ->willReturn((new SecurityCheckAuthResponseTransfer())->setIsChallengeRequired(true));

        // Act
        $isCaptchaRequired = $this->createPlugin($securityBlockerClientMock)
            ->isCaptchaRequired($this->createCaptchaRequirementRequestTransfer((new SecurityBlockerPageConfig())->getCustomerCaptchaContext()));

        // Assert
        $this->assertTrue($isCaptchaRequired);
    }

    public function testAgentLoginIsCheckedAgainstTheAgentTypeAndTheUserRealm(): void
    {
        // Arrange
        $securityBlockerClientMock = $this->createMock(SecurityBlockerPageToSecurityBlockerClientInterface::class);

        // Assert
        $securityBlockerClientMock->expects($this->once())
            ->method('checkLoginAttempt')
            ->with($this->callback(fn (SecurityCheckAuthContextTransfer $securityCheckAuthContextTransfer): bool => $securityCheckAuthContextTransfer->getType() === SecurityBlockerPageConfig::SECURITY_BLOCKER_AGENT_ENTITY_TYPE
                && $securityCheckAuthContextTransfer->getRealm() === SecurityBlockerPageConfig::LOGIN_REALM_USER))
            ->willReturn((new SecurityCheckAuthResponseTransfer())->setIsChallengeRequired(false));

        // Act
        $isCaptchaRequired = $this->createPlugin($securityBlockerClientMock)
            ->isCaptchaRequired($this->createCaptchaRequirementRequestTransfer((new SecurityBlockerPageConfig())->getAgentCaptchaContext()));

        // Assert
        $this->assertFalse($isCaptchaRequired);
    }

    public function testPluginDoesNotServeAnotherContext(): void
    {
        // Arrange
        $securityBlockerClientMock = $this->createMock(SecurityBlockerPageToSecurityBlockerClientInterface::class);
        $securityBlockerClientMock->expects($this->never())->method('checkLoginAttempt');

        // Act
        $isCaptchaRequired = $this->createPlugin($securityBlockerClientMock)
            ->isCaptchaRequired($this->createCaptchaRequirementRequestTransfer(static::OTHER_CAPTCHA_CONTEXT));

        // Assert
        $this->assertFalse($isCaptchaRequired);
    }

    public function testCaptchaIsRequiredWhenTheSecurityBlockerCannotReadTheCounters(): void
    {
        // Arrange
        $securityBlockerClientMock = $this->createMock(SecurityBlockerPageToSecurityBlockerClientInterface::class);
        $securityBlockerClientMock->method('checkLoginAttempt')->willThrowException(new SecurityBlockerException('Unknown login realm.'));

        // Act
        $isCaptchaRequired = $this->createPlugin($securityBlockerClientMock)
            ->isCaptchaRequired($this->createCaptchaRequirementRequestTransfer((new SecurityBlockerPageConfig())->getCustomerCaptchaContext()));

        // Assert
        $this->assertTrue($isCaptchaRequired);
    }

    /**
     * The security blocker client lets a Redis connection error through as it is, not as a `SecurityBlockerException`.
     */
    public function testCaptchaIsRequiredWhenTheSecurityBlockerRedisCannotBeReached(): void
    {
        // Arrange
        $securityBlockerClientMock = $this->createMock(SecurityBlockerPageToSecurityBlockerClientInterface::class);
        $securityBlockerClientMock->method('checkLoginAttempt')->willThrowException(new RuntimeException('Connection refused [tcp://key_value_store:6379]'));

        // Act
        $isCaptchaRequired = $this->createPlugin($securityBlockerClientMock)->isCaptchaRequired($this->createCaptchaRequirementRequestTransfer((new SecurityBlockerPageConfig())->getCustomerCaptchaContext()));

        // Assert
        $this->assertTrue($isCaptchaRequired);
    }

    protected function createPlugin(SecurityBlockerPageToSecurityBlockerClientInterface $securityBlockerClient): SecurityBlockerLoginCaptchaRequirementPlugin
    {
        $this->tester->setDependency(SecurityBlockerPageDependencyProvider::CLIENT_SECURITY_BLOCKER, $securityBlockerClient);

        $securityBlockerPageFactory = new SecurityBlockerPageFactory();
        $securityBlockerPageFactory->setConfig(new SecurityBlockerPageConfig());

        $securityBlockerLoginCaptchaRequirementPlugin = new SecurityBlockerLoginCaptchaRequirementPlugin();
        $securityBlockerLoginCaptchaRequirementPlugin->setFactory($securityBlockerPageFactory);

        return $securityBlockerLoginCaptchaRequirementPlugin;
    }

    protected function createCaptchaRequirementRequestTransfer(string $context): CaptchaRequirementRequestTransfer
    {
        return (new CaptchaRequirementRequestTransfer())
            ->setContext($context)
            ->setIp(static::IP)
            ->setLoginIdentifier(static::LOGIN_IDENTIFIER);
    }
}
