<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\ContentProductSetWidget\Plugin\Twig;

use Spryker\Service\Container\ContainerInterface;
use Spryker\Shared\TwigExtension\Dependency\Plugin\TwigPluginInterface;
use Spryker\Yves\Kernel\AbstractPlugin;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;

/**
 * @method \SprykerShop\Yves\ContentProductSetWidget\ContentProductSetWidgetFactory getFactory()
 */
class ContentProductSetTwigPlugin extends AbstractPlugin implements TwigPluginInterface
{
    /**
     * @uses \Spryker\Shared\Kernel\Communication\Application::REQUEST
     */
    protected const SERVICE_REQUEST = 'request';

    /**
     * {@inheritdoc}
     * - The plugin displays a content product set.
     *
     * @api
     *
     * @param \Twig\Environment $twig
     * @param \Spryker\Service\Container\ContainerInterface $container
     *
     * @return \Twig\Environment
     */
    public function extend(Environment $twig, ContainerInterface $container): Environment
    {
        $twig->addFunction(
            $this->getFactory()->createContentProductSetTwigFunction(
                $twig,
                $this->getRequest($container),
                $this->getLocale()
            )
        );

        return $twig;
    }

    /**
     * @param \Spryker\Service\Container\ContainerInterface $container
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    protected function getRequest(ContainerInterface $container): Request
    {
        return $container->get(static::SERVICE_REQUEST);
    }
}
