<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\ResourceSharePage\Controller;

use Generated\Shared\Transfer\ResourceShareRequestTransfer;
use Generated\Shared\Transfer\RouteTransfer;
use SprykerShop\Yves\ShopApplication\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method \SprykerShop\Yves\ResourceSharePage\ResourceSharePageFactory getFactory()
 */
class LinkController extends AbstractController
{
    protected const MESSAGE_TYPE_ERROR = 'error';
    protected const MESSAGE_TYPE_SUCCESS = 'success';

    /**
     * @see \SprykerShop\Yves\CustomerPage\Plugin\Provider\CustomerPageControllerProvider::ROUTE_LOGIN
     */
    protected const ROUTE_LOGIN = 'login';

    /**
     * @see \SprykerShop\Yves\ResourceSharePage\Plugin\Provider\ResourceSharePageControllerProvider::ROUTE_RESOURCE_SHARE_LINK
     */
    protected const ROUTE_RESOURCE_SHARE_LINK = 'link';
    protected const PARAM_RESOURCE_SHARE_UUID = 'resourceShareUuid';
    protected const LINK_REDIRECT_URL = 'LinkRedirectUrl';

    /**
     * @param string $resourceShareUuid
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function indexAction(string $resourceShareUuid, Request $request): RedirectResponse
    {
        $response = $this->executeIndexAction($resourceShareUuid, $request);

        return $response;
    }

    /**
     * @param string $resourceShareUuid
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function executeIndexAction(string $resourceShareUuid, Request $request): RedirectResponse
    {
        $resourceShareResponseTransfer = $this->getFactory()->createResourceShareActivator()
            ->activateResourceShare($resourceShareUuid);

        foreach ($resourceShareResponseTransfer->getMessages() as $messageTransfer) {
            if ($messageTransfer->getType() === static::MESSAGE_TYPE_ERROR) {
                $this->addErrorMessage($messageTransfer->getValue());
                continue;
            }

            $this->addSuccessMessage($messageTransfer->getValue());
        }

        if ($resourceShareResponseTransfer->getIsLoginRequired()) {
            $routeTransfer = (new RouteTransfer())
                ->setRoute(static::ROUTE_RESOURCE_SHARE_LINK)
                ->setParameters([static::PARAM_RESOURCE_SHARE_UUID => $resourceShareUuid]);

            return $this->redirectResponseInternal(static::ROUTE_LOGIN, [
                static::LINK_REDIRECT_URL => $this->getApplication()->path(
                    $routeTransfer->getRoute(),
                    $routeTransfer->getParameters()
                ),
            ]);
        }

        if (!$resourceShareResponseTransfer->getIsSuccessful()) {
            throw new NotFoundHttpException();
        }

        $routeTransfer = $this->getFactory()->createRouteResolver()
            ->resolveRoute(
                (new ResourceShareRequestTransfer())
                    ->setResourceShare($resourceShareResponseTransfer->getResourceShare())
                    ->setCustomer($this->getFactory()->getCustomerClient()->getCustomer())
            );

        return $this->redirectResponseInternal($routeTransfer->getRoute(), $routeTransfer->getParameters());
    }
}
