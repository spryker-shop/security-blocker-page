<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\SecurityBlockerPage\EventSubscriber;

use SprykerShop\Yves\SecurityBlockerPage\SecurityBlockerPageConfig;

class SecurityBlockerCustomerLoginEventSubscriber extends AbstractSecurityBlockerLoginEventSubscriber
{
    /**
     * @uses \SprykerShop\Yves\CustomerPage\Formatter\LoginCheckUrlFormatter::ROUTE_CHECK_PATH
     */
    public const string LOGIN_ROUTE = 'login_check';

    protected function getLoginRoute(): string
    {
        return static::LOGIN_ROUTE;
    }

    protected function getSiblingLoginRoute(): string
    {
        return SecurityBlockerAgentLoginEventSubscriber::LOGIN_ROUTE;
    }

    protected function isLocaleInSiblingLoginCheckPath(): bool
    {
        return $this->securityBlockerPageConfig->isLocaleInAgentLoginCheckPath();
    }

    protected function getSecurityBlockerEntityType(): string
    {
        return SecurityBlockerPageConfig::SECURITY_BLOCKER_CUSTOMER_ENTITY_TYPE;
    }

    protected function getLoginRealm(): string
    {
        return SecurityBlockerPageConfig::LOGIN_REALM_CUSTOMER;
    }

    protected function isLocaleInLoginCheckPath(): bool
    {
        return $this->securityBlockerPageConfig->isLocaleInCustomerLoginCheckPath();
    }
}
