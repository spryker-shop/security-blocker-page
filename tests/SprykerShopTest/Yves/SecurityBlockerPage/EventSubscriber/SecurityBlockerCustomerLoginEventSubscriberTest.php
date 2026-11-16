<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShopTest\Yves\SecurityBlockerPage\EventSubscriber;

use SprykerShop\Yves\SecurityBlockerPage\EventSubscriber\SecurityBlockerCustomerLoginEventSubscriber;
use SprykerShop\Yves\SecurityBlockerPage\SecurityBlockerPageConfig;

/**
 * Auto-generated group annotations
 *
 * @group SprykerShopTest
 * @group Yves
 * @group SecurityBlockerPage
 * @group EventSubscriber
 * @group SecurityBlockerCustomerLoginEventSubscriberTest
 * Add your own group annotations below this line
 */
class SecurityBlockerCustomerLoginEventSubscriberTest extends AbstractSecurityBlockerLoginEventSubscriberTestCase
{
    protected function getSubscriberClassName(): string
    {
        return SecurityBlockerCustomerLoginEventSubscriber::class;
    }

    protected function getLoginRoute(): string
    {
        return 'login_check';
    }

    protected function getSiblingLoginRoute(): string
    {
        return 'agent_login_check';
    }

    protected function getSecurityBlockerEntityType(): string
    {
        return SecurityBlockerPageConfig::SECURITY_BLOCKER_CUSTOMER_ENTITY_TYPE;
    }
}
