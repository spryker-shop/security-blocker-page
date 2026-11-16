<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\SecurityBlockerPage\Plugin\EventDispatcher;

use Spryker\Service\Container\ContainerInterface;
use Spryker\Shared\EventDispatcher\EventDispatcherInterface;
use Spryker\Shared\EventDispatcherExtension\Dependency\Plugin\EventDispatcherPluginInterface;
use Spryker\Yves\Kernel\AbstractPlugin;

/**
 * @method \SprykerShop\Yves\SecurityBlockerPage\SecurityBlockerPageFactory getFactory()
 * @method \SprykerShop\Yves\SecurityBlockerPage\SecurityBlockerPageConfig getConfig()
 */
class SecurityBlockerCustomerEventDispatcherPlugin extends AbstractPlugin implements EventDispatcherPluginInterface
{
    /**
     * {@inheritDoc}
     * - Adds a listeners to log the failed login attempts and deny customers access in case the limit is exceeded.
     * - Adds the listeners of the login protection strategy {@link \SprykerShop\Yves\SecurityBlockerPage\SecurityBlockerPageConfig::getLoginProtectionStrategy()}
     *   selects, by default the per-IP ones above.
     * - With the `ip-and-account` strategy, failed logins also lock the account regardless of the source IP.
     * - Throws an exception when no strategy is registered for the selected key.
     *
     * @api
     *
     * @param \Spryker\Shared\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param \Spryker\Service\Container\ContainerInterface $container
     *
     * @return \Spryker\Shared\EventDispatcher\EventDispatcherInterface
     */
    public function extend(
        EventDispatcherInterface $eventDispatcher,
        ContainerInterface $container
    ): EventDispatcherInterface {
        $eventDispatcher->addSubscriber(
            $this->getFactory()->createCustomerLoginProtectionEventSubscriber(),
        );

        return $eventDispatcher;
    }
}
