<?php

/**
 * Copyright © 2017-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\ShopApplication\Twig;

use Spryker\Shared\Kernel\Communication\Application;
use Spryker\Yves\Application\Routing\Helper;

class TwigRenderer implements TwigRendererInterface
{
    /**
     * @var \Spryker\Yves\Application\Routing\Helper
     */
    protected $routingHelper;

    /**
     * @param \Spryker\Yves\Application\Routing\Helper $routingHelper
     */
    public function __construct(Helper $routingHelper)
    {
        $this->routingHelper = $routingHelper;
    }

    /**
     * Renders the template for the current controller/action
     *
     * @param \Spryker\Shared\Kernel\Communication\Application $application
     * @param array $parameters
     *
     * @return null|\Symfony\Component\HttpFoundation\Response
     */
    public function render(Application $application, array $parameters = [])
    {
        $request = $application['request_stack']->getCurrentRequest();
        $controller = $request->attributes->get('_controller');

        if (!is_string($controller) || empty($controller)) {
            return null;
        }

        $route = $this->getRoute($parameters, $controller);

        return $application->render('@' . $route . '.twig', $parameters);
    }

    /**
     * @param array $parameters
     * @param string $controller
     *
     * @return string
     */
    protected function getRoute(array $parameters, $controller)
    {
        if (isset($parameters['alternativeRoute'])) {
            return (string)$parameters['alternativeRoute'];
        }

        return $this->routingHelper->getRouteFromDestination($controller);
    }
}
