<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShopTest\Yves\SecurityBlockerPage;

use Codeception\Actor;
use Codeception\Stub;
use Generated\Shared\Transfer\SecurityCheckAuthContextTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use SprykerShop\Yves\SecurityBlockerPage\Builder\MessageBuilder;
use SprykerShop\Yves\SecurityBlockerPage\Builder\SecurityCheckAuthContextBuilder;
use SprykerShop\Yves\SecurityBlockerPage\Dependency\Client\SecurityBlockerPageToGlossaryStorageClientInterface;
use SprykerShop\Yves\SecurityBlockerPage\Dependency\Client\SecurityBlockerPageToSecurityBlockerClientInterface;
use SprykerShop\Yves\SecurityBlockerPage\Dependency\Client\SecurityBlockerPageToStoreClientInterface;
use SprykerShop\Yves\SecurityBlockerPage\EventSubscriber\AbstractSecurityBlockerLoginEventSubscriber;
use SprykerShop\Yves\SecurityBlockerPage\Reader\LoginFormReader;
use SprykerShop\Yves\SecurityBlockerPage\SecurityBlockerPageConfig;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

/**
 * Inherited Methods
 *
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 *
 * @SuppressWarnings(PHPMD)
 */
class SecurityBlockerPageTester extends Actor
{
    use _generated\SecurityBlockerPageTesterActions;

    public const string LOGIN_IDENTIFIER = 'test@spryker.com';

    public const string IP = '66.66.66.6';

    public const string LOCALE_NAME = 'en_US';

    protected const string FIREWALL_NAME = 'secured';

    public function getSecurityCheckAuthContextTransfer(string $type): SecurityCheckAuthContextTransfer
    {
        return (new SecurityCheckAuthContextTransfer())
            ->setType($type)
            ->setAccount(static::LOGIN_IDENTIFIER)
            ->setIp(static::IP);
    }

    public function getLoginSecurityCheckAuthContextTransfer(string $type): SecurityCheckAuthContextTransfer
    {
        return $this->getSecurityCheckAuthContextTransfer($type)
            ->setRealm($type === SecurityBlockerPageConfig::SECURITY_BLOCKER_CUSTOMER_ENTITY_TYPE ? SecurityBlockerPageConfig::LOGIN_REALM_CUSTOMER : SecurityBlockerPageConfig::LOGIN_REALM_USER)
            ->setLoginIdentifier(static::LOGIN_IDENTIFIER);
    }

    public function getRequest(SecurityCheckAuthContextTransfer $securityCheckAuthContextTransfer): Request
    {
        return $this->createLegacyLoginRequest($securityCheckAuthContextTransfer, Request::METHOD_POST);
    }

    public function getInvalidRequest(SecurityCheckAuthContextTransfer $securityCheckAuthContextTransfer): Request
    {
        return $this->createLegacyLoginRequest($securityCheckAuthContextTransfer, Request::METHOD_GET);
    }

    /**
     * @param class-string<\SprykerShop\Yves\SecurityBlockerPage\EventSubscriber\AbstractSecurityBlockerLoginEventSubscriber> $subscriberClassName
     */
    public function createLoginEventSubscriber(
        string $subscriberClassName,
        SecurityBlockerPageToSecurityBlockerClientInterface $securityBlockerClient,
        SecurityBlockerPageConfig $securityBlockerPageConfig
    ): AbstractSecurityBlockerLoginEventSubscriber {
        return new $subscriberClassName(
            $securityBlockerClient,
            new MessageBuilder(Stub::makeEmpty(SecurityBlockerPageToGlossaryStorageClientInterface::class, ['translate' => 'Too many login attempts.'])),
            $securityBlockerPageConfig,
            static::LOCALE_NAME,
            Stub::makeEmpty(SecurityBlockerPageToStoreClientInterface::class, ['getCurrentStore' => (new StoreTransfer())->setName('DE')]),
            new LoginFormReader(),
            new SecurityCheckAuthContextBuilder($securityBlockerPageConfig),
        );
    }

    /**
     * @param array<string, mixed> $body
     */
    public function createLoginRequest(string $route, string $method, array $body, string $queryString = ''): Request
    {
        $request = Request::create(
            sprintf('/%s%s', $route, $queryString),
            $method,
            $body,
            [],
            [],
            ['REMOTE_ADDR' => static::IP],
        );
        $request->attributes->set('_route', $route);
        $request->setSession(new Session(new MockArraySessionStorage()));

        return $request;
    }

    public function createRequestEvent(Request $request): RequestEvent
    {
        return new RequestEvent(Stub::makeEmpty(HttpKernelInterface::class), $request, HttpKernelInterface::MAIN_REQUEST);
    }

    public function createLoginFailureEvent(Request $request): LoginFailureEvent
    {
        return new LoginFailureEvent(
            new BadCredentialsException(),
            Stub::makeEmpty(AuthenticatorInterface::class),
            $request,
            null,
            static::FIREWALL_NAME,
        );
    }

    public function createLoginSuccessEvent(Request $request): LoginSuccessEvent
    {
        return new LoginSuccessEvent(
            Stub::makeEmpty(AuthenticatorInterface::class),
            new SelfValidatingPassport(new UserBadge(static::LOGIN_IDENTIFIER)),
            Stub::makeEmpty(TokenInterface::class),
            $request,
            null,
            static::FIREWALL_NAME,
        );
    }

    protected function createLegacyLoginRequest(SecurityCheckAuthContextTransfer $securityCheckAuthContextTransfer, string $method): Request
    {
        $route = 'login_check';

        if ($securityCheckAuthContextTransfer->getType() === SecurityBlockerPageConfig::SECURITY_BLOCKER_AGENT_ENTITY_TYPE) {
            $route = 'agent_login_check';
        }

        $request = Request::create(
            '/' . $route,
            $method,
            [
                'loginForm' => [
                    'email' => $securityCheckAuthContextTransfer->getAccount(),
                ],
            ],
            [],
            [],
            [
                'REMOTE_ADDR' => $securityCheckAuthContextTransfer->getIp(),
            ],
        );
        $request->attributes->add(['_route' => $route]);

        return $request;
    }
}
