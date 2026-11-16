<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShopTest\Yves\SecurityBlockerPage\EventSubscriber;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\SecurityCheckAuthContextTransfer;
use Generated\Shared\Transfer\SecurityCheckAuthResponseTransfer;
use PHPUnit\Framework\MockObject\MockObject;
use SprykerShop\Yves\SecurityBlockerPage\Dependency\Client\SecurityBlockerPageToSecurityBlockerClientInterface;
use SprykerShop\Yves\SecurityBlockerPage\EventSubscriber\AbstractSecurityBlockerLoginEventSubscriber;
use SprykerShop\Yves\SecurityBlockerPage\SecurityBlockerPageConfig;
use SprykerShopTest\Yves\SecurityBlockerPage\SecurityBlockerPageTester;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

abstract class AbstractSecurityBlockerLoginEventSubscriberTestCase extends Unit
{
    protected const string ROUTE_OTHER = 'home';

    /**
     * @uses \Spryker\Yves\Captcha\EventSubscriber\LoginCaptchaEventSubscriber::KERNEL_REQUEST_SUBSCRIBER_PRIORITY
     */
    protected const int CAPTCHA_SUBSCRIBER_PRIORITY = 10;

    protected const string CAPTCHA_REJECTION_URL = '/captcha-rejected';

    protected SecurityBlockerPageTester $tester;

    /**
     * @return class-string<\SprykerShop\Yves\SecurityBlockerPage\EventSubscriber\AbstractSecurityBlockerLoginEventSubscriber>
     */
    abstract protected function getSubscriberClassName(): string;

    abstract protected function getLoginRoute(): string;

    abstract protected function getSecurityBlockerEntityType(): string;

    abstract protected function getSiblingLoginRoute(): string;

    public function testLoginAttemptIsCheckedAndThenReservedAgainstTheAccount(): void
    {
        // Arrange
        $securityBlockerClientMock = $this->createSecurityBlockerClientMock(false, false);

        // Assert
        $securityBlockerClientMock->expects($this->once())->method('checkLoginAttempt')->with($this->getExpectedContext())->willReturn($this->createResponse(false));
        $securityBlockerClientMock->expects($this->once())->method('reserveLoginAttempt')->with($this->getExpectedContext())->willReturn($this->createResponse(false));

        // Act
        $this->dispatchKernelRequest($this->createSubscriber($securityBlockerClientMock), $this->tester->createRequestEvent($this->createLoginRequest()));
    }

    public function testBlockedLoginAttemptIsRejectedWithTooManyRequestsBeforeTheReservation(): void
    {
        // Arrange
        $securityBlockerClientMock = $this->createSecurityBlockerClientMock(true, false);

        // Assert
        $securityBlockerClientMock->expects($this->never())->method('reserveLoginAttempt');
        $this->expectExceptionObject(new HttpException(Response::HTTP_TOO_MANY_REQUESTS, 'Too many login attempts.'));

        // Act
        $this->dispatchKernelRequest($this->createSubscriber($securityBlockerClientMock), $this->tester->createRequestEvent($this->createLoginRequest()));
    }

    public function testLoginAttemptBeyondTheReservedAccountLimitIsRejectedWithTooManyRequests(): void
    {
        // Arrange
        $securityBlockerClientMock = $this->createSecurityBlockerClientMock(false, true);

        // Assert
        $this->expectException(HttpException::class);

        // Act
        $this->dispatchKernelRequest($this->createSubscriber($securityBlockerClientMock), $this->tester->createRequestEvent($this->createLoginRequest()));
    }

    public function testRequestThatIsNotALoginAttemptIsIgnored(): void
    {
        // Arrange
        $securityBlockerClientMock = $this->createSecurityBlockerClientMock(false, false);
        $request = $this->tester->createLoginRequest($this->getLoginRoute(), Request::METHOD_GET, $this->createLoginBody(SecurityBlockerPageTester::LOGIN_IDENTIFIER));

        // Assert
        $securityBlockerClientMock->expects($this->never())->method('checkLoginAttempt');
        $securityBlockerClientMock->expects($this->never())->method('reserveLoginAttempt');

        // Act
        $this->dispatchKernelRequest($this->createSubscriber($securityBlockerClientMock), $this->tester->createRequestEvent($request));
    }

    public function testLoginFormPostedToAnotherRouteIsCheckedAndReserved(): void
    {
        // Arrange
        $securityBlockerClientMock = $this->createSecurityBlockerClientMock(false, false);
        $request = $this->tester->createLoginRequest(static::ROUTE_OTHER, Request::METHOD_POST, $this->createLoginBody(SecurityBlockerPageTester::LOGIN_IDENTIFIER));

        // Assert
        $securityBlockerClientMock->expects($this->once())->method('checkLoginAttempt')->with($this->getExpectedContext())->willReturn($this->createResponse(false));
        $securityBlockerClientMock->expects($this->once())->method('reserveLoginAttempt')->with($this->getExpectedContext())->willReturn($this->createResponse(false));

        // Act
        $this->dispatchKernelRequest($this->createSubscriber($securityBlockerClientMock), $this->tester->createRequestEvent($request));
    }

    public function testBlockedAccountIsRejectedWhenTheLoginFormIsPostedToAnotherRoute(): void
    {
        // Arrange
        $request = $this->tester->createLoginRequest(static::ROUTE_OTHER, Request::METHOD_POST, $this->createLoginBody(SecurityBlockerPageTester::LOGIN_IDENTIFIER));

        // Assert
        $this->expectException(HttpException::class);

        // Act
        $this->dispatchKernelRequest($this->createSubscriber($this->createSecurityBlockerClientMock(true, false)), $this->tester->createRequestEvent($request));
    }

    public function testFailedLoginOnAnotherRouteIsRegistered(): void
    {
        // Arrange
        $securityBlockerClientMock = $this->createSecurityBlockerClientMock(false, false);
        $request = $this->tester->createLoginRequest(static::ROUTE_OTHER, Request::METHOD_POST, $this->createLoginBody(SecurityBlockerPageTester::LOGIN_IDENTIFIER));

        // Assert
        $securityBlockerClientMock->expects($this->once())->method('registerFailedLoginAttempt')->with($this->getExpectedContext());

        // Act
        $this->createSubscriber($securityBlockerClientMock)->onAuthenticationFailure($this->tester->createLoginFailureEvent($request));
    }

    public function testSuccessfulLoginOnAnotherRouteResetsNothing(): void
    {
        // Arrange
        $securityBlockerClientMock = $this->createSecurityBlockerClientMock(false, false);
        $request = $this->tester->createLoginRequest(static::ROUTE_OTHER, Request::METHOD_POST, $this->createLoginBody(SecurityBlockerPageTester::LOGIN_IDENTIFIER));

        // Assert
        $securityBlockerClientMock->expects($this->never())->method('registerSuccessfulLoginAttempt');

        // Act
        $this->createSubscriber($securityBlockerClientMock)->onAuthenticationSuccess($this->tester->createLoginSuccessEvent($request));
    }

    public function testLoginFormPostedToTheSiblingLoginRouteIsLeftToTheSiblingSubscriber(): void
    {
        // Arrange
        $securityBlockerClientMock = $this->createSecurityBlockerClientMock(false, false);
        $request = $this->tester->createLoginRequest($this->getSiblingLoginRoute(), Request::METHOD_POST, $this->createLoginBody(SecurityBlockerPageTester::LOGIN_IDENTIFIER));

        // Assert
        $securityBlockerClientMock->expects($this->never())->method('checkLoginAttempt');
        $securityBlockerClientMock->expects($this->never())->method('reserveLoginAttempt');
        $securityBlockerClientMock->expects($this->never())->method('registerFailedLoginAttempt');
        $securityBlockerClientMock->expects($this->never())->method('registerSuccessfulLoginAttempt');

        // Act
        $subscriber = $this->createSubscriber($securityBlockerClientMock);
        $this->dispatchKernelRequest($subscriber, $this->tester->createRequestEvent($request));
        $subscriber->onAuthenticationFailure($this->tester->createLoginFailureEvent($request));
        $subscriber->onAuthenticationSuccess($this->tester->createLoginSuccessEvent($request));
    }

    public function testLoginCheckRequestWithoutTheLoginFormIsIgnored(): void
    {
        // Arrange
        $securityBlockerClientMock = $this->createSecurityBlockerClientMock(false, false);
        $request = $this->tester->createLoginRequest($this->getLoginRoute(), Request::METHOD_POST, []);

        // Assert
        $securityBlockerClientMock->expects($this->never())->method('checkLoginAttempt');

        // Act
        $this->dispatchKernelRequest($this->createSubscriber($securityBlockerClientMock), $this->tester->createRequestEvent($request));
    }

    public function testIdentifierOfAQueryStringDecoyIsIgnored(): void
    {
        // Arrange
        $securityBlockerClientMock = $this->createSecurityBlockerClientMock(false, false);
        $request = $this->tester->createLoginRequest(
            $this->getLoginRoute(),
            Request::METHOD_POST,
            $this->createLoginBody(SecurityBlockerPageTester::LOGIN_IDENTIFIER),
            '?loginForm[email]=decoy@example.com',
        );

        // Assert
        $securityBlockerClientMock->expects($this->once())
            ->method('reserveLoginAttempt')
            ->with($this->callback(fn (SecurityCheckAuthContextTransfer $securityCheckAuthContextTransfer): bool => $securityCheckAuthContextTransfer->getLoginIdentifier() === SecurityBlockerPageTester::LOGIN_IDENTIFIER))
            ->willReturn($this->createResponse(false));

        // Act
        $this->dispatchKernelRequest($this->createSubscriber($securityBlockerClientMock), $this->tester->createRequestEvent($request));
    }

    public function testIdentifierSubmittedAsAnArrayIsTreatedAsMissing(): void
    {
        // Arrange
        $securityBlockerClientMock = $this->createSecurityBlockerClientMock(false, false);
        $request = $this->tester->createLoginRequest($this->getLoginRoute(), Request::METHOD_POST, ['loginForm' => ['email' => ['x@example.com']]]);

        // Assert
        $securityBlockerClientMock->expects($this->once())
            ->method('reserveLoginAttempt')
            ->with($this->callback(fn (SecurityCheckAuthContextTransfer $securityCheckAuthContextTransfer): bool => $securityCheckAuthContextTransfer->getLoginIdentifier() === null))
            ->willReturn($this->createResponse(false));

        // Act
        $this->dispatchKernelRequest($this->createSubscriber($securityBlockerClientMock), $this->tester->createRequestEvent($request));
    }

    public function testAccountIsProtectedEvenWhenTheEmailIsNotPartOfThePerIpKey(): void
    {
        // Arrange
        $securityBlockerClientMock = $this->createSecurityBlockerClientMock(false, false);

        // Assert
        $securityBlockerClientMock->expects($this->once())
            ->method('reserveLoginAttempt')
            ->with($this->callback(fn (SecurityCheckAuthContextTransfer $securityCheckAuthContextTransfer): bool => $securityCheckAuthContextTransfer->getAccount() === null
                && $securityCheckAuthContextTransfer->getLoginIdentifier() === SecurityBlockerPageTester::LOGIN_IDENTIFIER))
            ->willReturn($this->createResponse(false));

        // Act
        $this->dispatchKernelRequest($this->createSubscriber($securityBlockerClientMock, false), $this->tester->createRequestEvent($this->createLoginRequest()));
    }

    public function testFailedLoginIsRegisteredAndTheIdentifierIsRememberedForTheNextForm(): void
    {
        // Arrange
        $securityBlockerClientMock = $this->createSecurityBlockerClientMock(false, false);
        $request = $this->createLoginRequest();

        // Assert
        $securityBlockerClientMock->expects($this->once())->method('registerFailedLoginAttempt')->with($this->getExpectedContext());
        $securityBlockerClientMock->expects($this->never())->method('registerSuccessfulLoginAttempt');

        // Act
        $this->createSubscriber($securityBlockerClientMock)->onAuthenticationFailure($this->tester->createLoginFailureEvent($request));

        // Assert
        $this->assertSame(SecurityBlockerPageTester::LOGIN_IDENTIFIER, $request->getSession()->get(SecurityRequestAttributes::LAST_USERNAME));
    }

    public function testSuccessfulLoginResetsTheAccountCounter(): void
    {
        // Arrange
        $securityBlockerClientMock = $this->createSecurityBlockerClientMock(false, false);

        // Assert
        $securityBlockerClientMock->expects($this->once())->method('registerSuccessfulLoginAttempt')->with($this->getExpectedContext());
        $securityBlockerClientMock->expects($this->never())->method('registerFailedLoginAttempt');

        // Act
        $this->createSubscriber($securityBlockerClientMock)->onAuthenticationSuccess($this->tester->createLoginSuccessEvent($this->createLoginRequest()));
    }

    public function testAuthenticationEventsOutsideTheLoginFormAreIgnored(): void
    {
        // Arrange
        $securityBlockerClientMock = $this->createSecurityBlockerClientMock(false, false);
        $request = $this->tester->createLoginRequest('home', Request::METHOD_GET, []);

        // Assert
        $securityBlockerClientMock->expects($this->never())->method('registerFailedLoginAttempt');
        $securityBlockerClientMock->expects($this->never())->method('registerSuccessfulLoginAttempt');

        // Act
        $subscriber = $this->createSubscriber($securityBlockerClientMock);
        $subscriber->onAuthenticationFailure($this->tester->createLoginFailureEvent($request));
        $subscriber->onAuthenticationSuccess($this->tester->createLoginSuccessEvent($request));
    }

    public function testBlockedLoginAttemptIsRejectedBeforeItsCaptchaIsVerified(): void
    {
        // Arrange
        $eventDispatcher = $this->createEventDispatcherWithCaptchaListener($this->createSubscriber($this->createSecurityBlockerClientMock(true, false)), $this->createFailingCaptchaListener());

        // Assert
        $this->expectExceptionObject(new HttpException(Response::HTTP_TOO_MANY_REQUESTS, 'Too many login attempts.'));

        // Act
        $eventDispatcher->dispatch($this->tester->createRequestEvent($this->createLoginRequest()), KernelEvents::REQUEST);
    }

    public function testLoginAttemptRejectedByTheCaptchaIsNotReserved(): void
    {
        // Arrange
        $securityBlockerClientMock = $this->createSecurityBlockerClientMock(false, false);
        $eventDispatcher = $this->createEventDispatcherWithCaptchaListener($this->createSubscriber($securityBlockerClientMock), $this->createRejectingCaptchaListener());

        // Assert
        $securityBlockerClientMock->expects($this->never())->method('reserveLoginAttempt');

        // Act
        $eventDispatcher->dispatch($this->tester->createRequestEvent($this->createLoginRequest()), KernelEvents::REQUEST);
    }

    protected function createSubscriber(
        SecurityBlockerPageToSecurityBlockerClientInterface $securityBlockerClient,
        bool $useEmailContextForLoginSecurityBlocker = true
    ): AbstractSecurityBlockerLoginEventSubscriber {
        return $this->tester->createLoginEventSubscriber(
            $this->getSubscriberClassName(),
            $securityBlockerClient,
            $this->createSecurityBlockerPageConfigMock($useEmailContextForLoginSecurityBlocker),
        );
    }

    protected function createSecurityBlockerPageConfigMock(bool $useEmailContextForLoginSecurityBlocker): SecurityBlockerPageConfig&MockObject
    {
        $securityBlockerPageConfigMock = $this->createMock(SecurityBlockerPageConfig::class);
        $securityBlockerPageConfigMock->method('useEmailContextForLoginSecurityBlocker')->willReturn($useEmailContextForLoginSecurityBlocker);

        return $securityBlockerPageConfigMock;
    }

    protected function createSecurityBlockerClientMock(
        bool $isBlockedOnCheck,
        bool $isBlockedOnReservation,
        bool $isChallengeRequired = false
    ): SecurityBlockerPageToSecurityBlockerClientInterface&MockObject {
        $securityBlockerClientMock = $this->createMock(SecurityBlockerPageToSecurityBlockerClientInterface::class);
        $securityBlockerClientMock->method('checkLoginAttempt')->willReturn($this->createResponse($isBlockedOnCheck)->setIsChallengeRequired($isChallengeRequired));
        $securityBlockerClientMock->method('reserveLoginAttempt')->willReturn($this->createResponse($isBlockedOnReservation));

        return $securityBlockerClientMock;
    }

    protected function createResponse(bool $isBlocked): SecurityCheckAuthResponseTransfer
    {
        return (new SecurityCheckAuthResponseTransfer())->setIsBlocked($isBlocked)->setBlockedFor(360);
    }

    protected function createLoginRequest(): Request
    {
        $loginBody = $this->createLoginBody(SecurityBlockerPageTester::LOGIN_IDENTIFIER);

        return $this->tester->createLoginRequest($this->getLoginRoute(), Request::METHOD_POST, $loginBody);
    }

    /**
     * @return array<string, array<string, string>>
     */
    protected function createLoginBody(string $loginIdentifier): array
    {
        return ['loginForm' => ['email' => $loginIdentifier, 'password' => 'secret']];
    }

    protected function getExpectedContext(): SecurityCheckAuthContextTransfer
    {
        return $this->tester->getLoginSecurityCheckAuthContextTransfer($this->getSecurityBlockerEntityType());
    }

    protected function dispatchKernelRequest(EventSubscriberInterface $subscriber, RequestEvent $event): void
    {
        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addSubscriber($subscriber);
        $eventDispatcher->dispatch($event, KernelEvents::REQUEST);
    }

    protected function createEventDispatcherWithCaptchaListener(EventSubscriberInterface $subscriber, callable $captchaListener): EventDispatcher
    {
        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addSubscriber($subscriber);
        $eventDispatcher->addListener(KernelEvents::REQUEST, $captchaListener, static::CAPTCHA_SUBSCRIBER_PRIORITY);

        return $eventDispatcher;
    }

    protected function createFailingCaptchaListener(): callable
    {
        return function (): void {
            $this->fail('The CAPTCHA was verified before the blocked login attempt was rejected.');
        };
    }

    protected function createRejectingCaptchaListener(): callable
    {
        return static function (RequestEvent $event): void {
            $event->setResponse(new RedirectResponse(static::CAPTCHA_REJECTION_URL));
        };
    }
}
