<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\SecurityBlockerPage\EventSubscriber;

use Generated\Shared\Transfer\SecurityCheckAuthContextTransfer;
use Generated\Shared\Transfer\SecurityCheckAuthResponseTransfer;
use SprykerShop\Yves\SecurityBlockerPage\Builder\MessageBuilderInterface;
use SprykerShop\Yves\SecurityBlockerPage\Builder\SecurityCheckAuthContextBuilderInterface;
use SprykerShop\Yves\SecurityBlockerPage\Dependency\Client\SecurityBlockerPageToSecurityBlockerClientInterface;
use SprykerShop\Yves\SecurityBlockerPage\Dependency\Client\SecurityBlockerPageToStoreClientInterface;
use SprykerShop\Yves\SecurityBlockerPage\Reader\LoginFormReaderInterface;
use SprykerShop\Yves\SecurityBlockerPage\SecurityBlockerPageConfig;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

/**
 * Protects a storefront login form against brute force, per source IP and per account regardless of the IP.
 *
 * The attempt is checked before the Captcha module verifies its CAPTCHA (priority 11 before 10), so a blocked login is
 * answered before its CAPTCHA is used. It is reserved against the account after the CAPTCHA and before the firewall
 * (priority 9), so a submission without a valid CAPTCHA is never counted, and only then authenticated. A failure feeds
 * the per-IP and realm-wide counters, a success resets the account counter and takes the account's own failures back
 * from the per-IP counter.
 *
 * The login form authenticators accept the form on every route of their firewall, so any request carrying the form is
 * an attempt, except on the login-check route of the sibling form: the customer and the agent form both post
 * `loginForm`. Only a success on the own login-check route resets the account counter, because elsewhere the other
 * realm's authenticator may have been the one that ran.
 */
abstract class AbstractSecurityBlockerLoginEventSubscriber implements EventSubscriberInterface
{
    protected const int KERNEL_REQUEST_CHECK_SUBSCRIBER_PRIORITY = 11;

    protected const int KERNEL_REQUEST_RESERVE_SUBSCRIBER_PRIORITY = 9;

    public function __construct(
        protected SecurityBlockerPageToSecurityBlockerClientInterface $securityBlockerClient,
        protected MessageBuilderInterface $messageBuilder,
        protected SecurityBlockerPageConfig $securityBlockerPageConfig,
        protected string $localeName,
        protected SecurityBlockerPageToStoreClientInterface $storeClient,
        protected LoginFormReaderInterface $loginFormReader,
        protected SecurityCheckAuthContextBuilderInterface $securityCheckAuthContextBuilder
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            LoginFailureEvent::class => 'onAuthenticationFailure',
            LoginSuccessEvent::class => 'onAuthenticationSuccess',
            KernelEvents::REQUEST => [
                ['onKernelRequestCheckLoginAttempt', static::KERNEL_REQUEST_CHECK_SUBSCRIBER_PRIORITY],
                ['onKernelRequestReserveLoginAttempt', static::KERNEL_REQUEST_RESERVE_SUBSCRIBER_PRIORITY],
            ],
        ];
    }

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function onKernelRequestCheckLoginAttempt(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!$this->isLoginAttempt($request)) {
            return;
        }

        $securityCheckAuthResponseTransfer = $this->securityBlockerClient->checkLoginAttempt($this->createSecurityCheckAuthContextTransfer($request));

        if ($securityCheckAuthResponseTransfer->getIsBlocked()) {
            throw $this->createTooManyRequestsException($securityCheckAuthResponseTransfer);
        }
    }

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function onKernelRequestReserveLoginAttempt(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!$this->isLoginAttempt($request)) {
            return;
        }

        $securityCheckAuthResponseTransfer = $this->securityBlockerClient->reserveLoginAttempt($this->createSecurityCheckAuthContextTransfer($request));

        if ($securityCheckAuthResponseTransfer->getIsBlocked()) {
            throw $this->createTooManyRequestsException($securityCheckAuthResponseTransfer);
        }
    }

    public function onAuthenticationFailure(LoginFailureEvent $event): void
    {
        $request = $event->getRequest();

        if (!$this->isLoginAttempt($request)) {
            return;
        }

        $securityCheckAuthContextTransfer = $this->createSecurityCheckAuthContextTransfer($request);
        $this->securityBlockerClient->registerFailedLoginAttempt($securityCheckAuthContextTransfer);

        $loginIdentifier = $securityCheckAuthContextTransfer->getLoginIdentifier();

        if ($loginIdentifier === null) {
            return;
        }

        $this->loginFormReader->saveLastLoginIdentifier($request, $loginIdentifier);
    }

    public function onAuthenticationSuccess(LoginSuccessEvent $event): void
    {
        $request = $event->getRequest();

        if (!$this->isLoginAttemptOnOwnLoginRoute($request)) {
            return;
        }

        $this->securityBlockerClient->registerSuccessfulLoginAttempt($this->createSecurityCheckAuthContextTransfer($request));
    }

    abstract protected function getLoginRoute(): string;

    abstract protected function getSecurityBlockerEntityType(): string;

    abstract protected function getLoginRealm(): string;

    abstract protected function isLocaleInLoginCheckPath(): bool;

    abstract protected function getSiblingLoginRoute(): string;

    abstract protected function isLocaleInSiblingLoginCheckPath(): bool;

    protected function isLoginAttempt(Request $request): bool
    {
        return $this->loginFormReader->isLoginFormSubmitted($request)
            && !$this->isRoute($request, $this->getSiblingLoginRoute(), $this->isLocaleInSiblingLoginCheckPath());
    }

    protected function isLoginAttemptOnOwnLoginRoute(Request $request): bool
    {
        return $this->loginFormReader->isLoginFormSubmitted($request)
            && $this->isRoute($request, $this->getLoginRoute(), $this->isLocaleInLoginCheckPath());
    }

    protected function isRoute(Request $request, string $route, bool $isLocaleInPath): bool
    {
        $currentRoute = $request->attributes->get('_route');

        if (!is_string($currentRoute)) {
            return false;
        }

        if ($this->securityBlockerPageConfig->isStoreRoutingEnabled()) {
            $currentRoute = $this->removePrefix($currentRoute, $this->storeClient->getCurrentStore()->getNameOrFail());
        }

        if ($isLocaleInPath) {
            $currentRoute = $this->removePrefix($currentRoute, mb_substr($this->localeName, 0, 2));
        }

        return $currentRoute === $route;
    }

    protected function createSecurityCheckAuthContextTransfer(Request $request): SecurityCheckAuthContextTransfer
    {
        return $this->securityCheckAuthContextBuilder->createSecurityCheckAuthContextTransfer(
            $request,
            $this->getSecurityBlockerEntityType(),
            $this->getLoginRealm(),
            $this->loginFormReader->findSubmittedLoginIdentifier($request),
        );
    }

    protected function createTooManyRequestsException(SecurityCheckAuthResponseTransfer $securityCheckAuthResponseTransfer): HttpException
    {
        return new HttpException(
            Response::HTTP_TOO_MANY_REQUESTS,
            $this->messageBuilder->getExceptionMessage($securityCheckAuthResponseTransfer, $this->localeName),
        );
    }

    protected function removePrefix(string $route, string $prefix): string
    {
        return ltrim(mb_substr($route, mb_strlen($prefix)), '_');
    }
}
