<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\ConfigurableBundleCartNoteWidget\Plugin\Router;

use Spryker\Yves\Router\Plugin\RouteProvider\AbstractRouteProviderPlugin;
use Spryker\Yves\Router\Route\RouteCollection;

class ConfigurableBundleCartNoteWidgetRouteProviderPlugin extends AbstractRouteProviderPlugin
{
    protected const ROUTE_CONFIGURABLE_BUNDLE_CART_NOTE_ADD = 'configurable-bundle-cart-note/add';

    /**
     * {@inheritDoc}
     * - Adds configurable bundle cart note add action to RouteCollection.
     *
     * @api
     *
     * @param \Spryker\Yves\Router\Route\RouteCollection $routeCollection
     *
     * @return \Spryker\Yves\Router\Route\RouteCollection
     */
    public function addRoutes(RouteCollection $routeCollection): RouteCollection
    {
        $routeCollection = $this->addConfigurableBundleCartNoteAddRoute($routeCollection);

        return $routeCollection;
    }

    /**
     * @uses \SprykerShop\Yves\ConfigurableBundleCartNoteWidget\Controller\CartNoteController::addAction()
     *
     * @param \Spryker\Yves\Router\Route\RouteCollection $routeCollection
     *
     * @return \Spryker\Yves\Router\Route\RouteCollection
     */
    protected function addConfigurableBundleCartNoteAddRoute(RouteCollection $routeCollection): RouteCollection
    {
        $route = $this->buildRoute(
            '/configurable-bundle-cart-note/add',
            'ConfigurableBundleCartNoteWidget',
            'CartNote',
            'addAction'
        );
        $routeCollection->add(static::ROUTE_CONFIGURABLE_BUNDLE_CART_NOTE_ADD, $route);

        return $routeCollection;
    }
}
