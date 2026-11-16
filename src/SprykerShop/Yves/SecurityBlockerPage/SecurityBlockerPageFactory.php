<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\SecurityBlockerPage;

use Spryker\Shared\Kernel\StrategyResolver;
use Spryker\Shared\Kernel\StrategyResolverInterface;
use Spryker\Shared\Log\LoggerTrait;
use Spryker\Yves\Kernel\AbstractFactory;
use SprykerShop\Yves\SecurityBlockerPage\Builder\MessageBuilder;
use SprykerShop\Yves\SecurityBlockerPage\Builder\MessageBuilderInterface;
use SprykerShop\Yves\SecurityBlockerPage\Builder\SecurityCheckAuthContextBuilder;
use SprykerShop\Yves\SecurityBlockerPage\Builder\SecurityCheckAuthContextBuilderInterface;
use SprykerShop\Yves\SecurityBlockerPage\Checker\LoginAttemptCaptchaRequirementChecker;
use SprykerShop\Yves\SecurityBlockerPage\Checker\LoginAttemptCaptchaRequirementCheckerInterface;
use SprykerShop\Yves\SecurityBlockerPage\Dependency\Client\SecurityBlockerPageToGlossaryStorageClientInterface;
use SprykerShop\Yves\SecurityBlockerPage\Dependency\Client\SecurityBlockerPageToSecurityBlockerClientInterface;
use SprykerShop\Yves\SecurityBlockerPage\Dependency\Client\SecurityBlockerPageToStoreClientInterface;
use SprykerShop\Yves\SecurityBlockerPage\EventSubscriber\SecurityBlockerAgentEventSubscriber;
use SprykerShop\Yves\SecurityBlockerPage\EventSubscriber\SecurityBlockerAgentLoginEventSubscriber;
use SprykerShop\Yves\SecurityBlockerPage\EventSubscriber\SecurityBlockerCustomerEventSubscriber;
use SprykerShop\Yves\SecurityBlockerPage\EventSubscriber\SecurityBlockerCustomerLoginEventSubscriber;
use SprykerShop\Yves\SecurityBlockerPage\Reader\LoginFormReader;
use SprykerShop\Yves\SecurityBlockerPage\Reader\LoginFormReaderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @method \SprykerShop\Yves\SecurityBlockerPage\SecurityBlockerPageConfig getConfig()
 */
class SecurityBlockerPageFactory extends AbstractFactory
{
    use LoggerTrait;

    public function createCustomerLoginProtectionEventSubscriber(): EventSubscriberInterface
    {
        return $this->createCustomerLoginProtectionEventSubscriberStrategyResolver()->get($this->getConfig()->getLoginProtectionStrategy());
    }

    /**
     * @return \Spryker\Shared\Kernel\StrategyResolverInterface<\Symfony\Component\EventDispatcher\EventSubscriberInterface>
     */
    public function createCustomerLoginProtectionEventSubscriberStrategyResolver(): StrategyResolverInterface
    {
        return new StrategyResolver([
            SecurityBlockerPageConfig::LOGIN_PROTECTION_STRATEGY_IP => fn (): EventSubscriberInterface => $this->createSecurityBlockerCustomerEventSubscriber(),
            SecurityBlockerPageConfig::LOGIN_PROTECTION_STRATEGY_IP_AND_ACCOUNT => fn (): EventSubscriberInterface => $this->createSecurityBlockerCustomerLoginEventSubscriber(),
        ]);
    }

    public function createAgentLoginProtectionEventSubscriber(): EventSubscriberInterface
    {
        return $this->createAgentLoginProtectionEventSubscriberStrategyResolver()->get($this->getConfig()->getLoginProtectionStrategy());
    }

    /**
     * @return \Spryker\Shared\Kernel\StrategyResolverInterface<\Symfony\Component\EventDispatcher\EventSubscriberInterface>
     */
    public function createAgentLoginProtectionEventSubscriberStrategyResolver(): StrategyResolverInterface
    {
        return new StrategyResolver([
            SecurityBlockerPageConfig::LOGIN_PROTECTION_STRATEGY_IP => fn (): EventSubscriberInterface => $this->createSecurityBlockerAgentEventSubscriber(),
            SecurityBlockerPageConfig::LOGIN_PROTECTION_STRATEGY_IP_AND_ACCOUNT => fn (): EventSubscriberInterface => $this->createSecurityBlockerAgentLoginEventSubscriber(),
        ]);
    }

    public function createSecurityBlockerCustomerEventSubscriber(): EventSubscriberInterface
    {
        return new SecurityBlockerCustomerEventSubscriber(
            $this->getRequestStack(),
            $this->getSecurityBlockerClient(),
            $this->createMessageBuilder(),
            $this->getConfig(),
            $this->getLocale(),
            $this->getStoreClient(),
        );
    }

    public function createSecurityBlockerAgentEventSubscriber(): EventSubscriberInterface
    {
        return new SecurityBlockerAgentEventSubscriber(
            $this->getRequestStack(),
            $this->getSecurityBlockerClient(),
            $this->createMessageBuilder(),
            $this->getConfig(),
            $this->getLocale(),
            $this->getStoreClient(),
        );
    }

    public function createSecurityBlockerCustomerLoginEventSubscriber(): EventSubscriberInterface
    {
        return new SecurityBlockerCustomerLoginEventSubscriber(
            $this->getSecurityBlockerClient(),
            $this->createMessageBuilder(),
            $this->getConfig(),
            $this->getLocale(),
            $this->getStoreClient(),
            $this->createLoginFormReader(),
            $this->createSecurityCheckAuthContextBuilder(),
        );
    }

    public function createSecurityBlockerAgentLoginEventSubscriber(): EventSubscriberInterface
    {
        return new SecurityBlockerAgentLoginEventSubscriber(
            $this->getSecurityBlockerClient(),
            $this->createMessageBuilder(),
            $this->getConfig(),
            $this->getLocale(),
            $this->getStoreClient(),
            $this->createLoginFormReader(),
            $this->createSecurityCheckAuthContextBuilder(),
        );
    }

    public function createLoginAttemptCaptchaRequirementChecker(): LoginAttemptCaptchaRequirementCheckerInterface
    {
        return new LoginAttemptCaptchaRequirementChecker(
            $this->getSecurityBlockerClient(),
            $this->createSecurityCheckAuthContextBuilder(),
            $this->getConfig(),
            $this->getLogger(),
        );
    }

    public function createLoginFormReader(): LoginFormReaderInterface
    {
        return new LoginFormReader();
    }

    public function createSecurityCheckAuthContextBuilder(): SecurityCheckAuthContextBuilderInterface
    {
        return new SecurityCheckAuthContextBuilder($this->getConfig());
    }

    public function createMessageBuilder(): MessageBuilderInterface
    {
        return new MessageBuilder($this->getGlossaryStorageClient());
    }

    public function getSecurityBlockerClient(): SecurityBlockerPageToSecurityBlockerClientInterface
    {
        return $this->getProvidedDependency(SecurityBlockerPageDependencyProvider::CLIENT_SECURITY_BLOCKER);
    }

    public function getGlossaryStorageClient(): SecurityBlockerPageToGlossaryStorageClientInterface
    {
        return $this->getProvidedDependency(SecurityBlockerPageDependencyProvider::CLIENT_GLOSSARY_STORAGE);
    }

    public function getRequestStack(): RequestStack
    {
        return $this->getProvidedDependency(SecurityBlockerPageDependencyProvider::SERVICE_REQUEST_STACK);
    }

    public function getLocale(): string
    {
        return $this->getProvidedDependency(SecurityBlockerPageDependencyProvider::SERVICE_LOCALE);
    }

    public function getStoreClient(): SecurityBlockerPageToStoreClientInterface
    {
        return $this->getProvidedDependency(SecurityBlockerPageDependencyProvider::CLIENT_STORE);
    }
}
