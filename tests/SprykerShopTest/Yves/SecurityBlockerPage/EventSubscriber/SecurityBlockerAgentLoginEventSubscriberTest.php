<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShopTest\Yves\SecurityBlockerPage\EventSubscriber;

use SprykerShop\Yves\SecurityBlockerPage\EventSubscriber\SecurityBlockerAgentLoginEventSubscriber;
use SprykerShop\Yves\SecurityBlockerPage\SecurityBlockerPageConfig;
use SprykerShopTest\Yves\SecurityBlockerPage\SecurityBlockerPageTester;
use Symfony\Component\HttpFoundation\Request;

/**
 * Auto-generated group annotations
 *
 * @group SprykerShopTest
 * @group Yves
 * @group SecurityBlockerPage
 * @group EventSubscriber
 * @group SecurityBlockerAgentLoginEventSubscriberTest
 * Add your own group annotations below this line
 */
class SecurityBlockerAgentLoginEventSubscriberTest extends AbstractSecurityBlockerLoginEventSubscriberTestCase
{
    public function testLocalePrefixedAgentRouteIsRecognizedByTheAgentLocaleSetting(): void
    {
        // Arrange
        $securityBlockerClientMock = $this->createSecurityBlockerClientMock(false, false);
        $securityBlockerPageConfigMock = $this->createSecurityBlockerPageConfigMock(true);
        $securityBlockerPageConfigMock->method('isLocaleInAgentLoginCheckPath')->willReturn(true);
        $securityBlockerPageConfigMock->method('isLocaleInCustomerLoginCheckPath')->willReturn(false);
        $request = $this->tester->createLoginRequest('en_agent_login_check', Request::METHOD_POST, $this->createLoginBody(SecurityBlockerPageTester::LOGIN_IDENTIFIER));

        // Assert
        $securityBlockerClientMock->expects($this->once())->method('registerSuccessfulLoginAttempt');

        // Act
        $this->tester->createLoginEventSubscriber($this->getSubscriberClassName(), $securityBlockerClientMock, $securityBlockerPageConfigMock)
            ->onAuthenticationSuccess($this->tester->createLoginSuccessEvent($request));
    }

    protected function getSubscriberClassName(): string
    {
        return SecurityBlockerAgentLoginEventSubscriber::class;
    }

    protected function getLoginRoute(): string
    {
        return 'agent_login_check';
    }

    protected function getSiblingLoginRoute(): string
    {
        return 'login_check';
    }

    protected function getSecurityBlockerEntityType(): string
    {
        return SecurityBlockerPageConfig::SECURITY_BLOCKER_AGENT_ENTITY_TYPE;
    }
}
