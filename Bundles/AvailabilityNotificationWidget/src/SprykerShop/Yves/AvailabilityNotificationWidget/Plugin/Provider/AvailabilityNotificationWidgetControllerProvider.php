<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\AvailabilityNotificationWidget\Plugin\Provider;

use Silex\Application;
use SprykerShop\Yves\ShopApplication\Plugin\Provider\AbstractYvesControllerProvider;

class AvailabilityNotificationWidgetControllerProvider extends AbstractYvesControllerProvider
{
    public const ROUTE_AVAILABILITY_NOTIFICATION_UNSUBSCRIBE = 'availability-notification/unsubscribe';
    public const ROUTE_AVAILABILITY_NOTIFICATION_SUBSCRIBE = 'availability-notification/subscribe';

    /**
     * @param \Silex\Application $app
     *
     * @return void
     */
    protected function defineControllers(Application $app): void
    {
        $this
            ->addAvailabilityNotificationSubscribeRoute()
            ->addAvailabilityNotificationUnsubscribeRoute();
    }

    /**
     * @return $this
     */
    protected function addAvailabilityNotificationSubscribeRoute()
    {
        $this->createPostController('/{availabilityNotification}/subscribe', static::ROUTE_AVAILABILITY_NOTIFICATION_SUBSCRIBE, 'AvailabilityNotificationWidget', 'Subscription', 'subscribe')
            ->assert('availabilityNotification', $this->getAllowedLocalesPattern() . 'availability-notification|availability-notification')
            ->value('availabilityNotification', 'availability-notification');

        return $this;
    }

    /**
     * @return $this
     */
    protected function addAvailabilityNotificationUnsubscribeRoute()
    {
        $this->createPostController('/{availabilityNotification}/unsubscribe', self::ROUTE_AVAILABILITY_NOTIFICATION_UNSUBSCRIBE, 'AvailabilityNotificationWidget', 'Subscription', 'unsubscribe')
            ->assert('availabilityNotification', $this->getAllowedLocalesPattern() . 'availability-notification|availability-notification')
            ->value('availabilityNotification', 'availability-notification');

        return $this;
    }
}
