<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\SecurityBlockerPage\Checker;

use Exception;
use Generated\Shared\Transfer\CaptchaRequirementRequestTransfer;
use Psr\Log\LoggerInterface;
use SprykerShop\Yves\SecurityBlockerPage\Builder\SecurityCheckAuthContextBuilderInterface;
use SprykerShop\Yves\SecurityBlockerPage\Dependency\Client\SecurityBlockerPageToSecurityBlockerClientInterface;
use SprykerShop\Yves\SecurityBlockerPage\SecurityBlockerPageConfig;

class LoginAttemptCaptchaRequirementChecker implements LoginAttemptCaptchaRequirementCheckerInterface
{
    protected const string LOG_MESSAGE_LOGIN_ATTEMPT_NOT_READABLE = 'The failed Storefront login attempts could not be read, so a CAPTCHA is required.';

    public function __construct(
        protected SecurityBlockerPageToSecurityBlockerClientInterface $securityBlockerClient,
        protected SecurityCheckAuthContextBuilderInterface $securityCheckAuthContextBuilder,
        protected SecurityBlockerPageConfig $securityBlockerPageConfig,
        protected LoggerInterface $logger
    ) {
    }

    public function isCaptchaRequired(CaptchaRequirementRequestTransfer $captchaRequirementRequestTransfer): bool
    {
        $context = $captchaRequirementRequestTransfer->getContextOrFail();

        if ($context === $this->securityBlockerPageConfig->getCustomerCaptchaContext()) {
            return $this->isChallengeRequired(
                $captchaRequirementRequestTransfer,
                SecurityBlockerPageConfig::SECURITY_BLOCKER_CUSTOMER_ENTITY_TYPE,
                SecurityBlockerPageConfig::LOGIN_REALM_CUSTOMER,
            );
        }

        if ($context === $this->securityBlockerPageConfig->getAgentCaptchaContext()) {
            return $this->isChallengeRequired(
                $captchaRequirementRequestTransfer,
                SecurityBlockerPageConfig::SECURITY_BLOCKER_AGENT_ENTITY_TYPE,
                SecurityBlockerPageConfig::LOGIN_REALM_USER,
            );
        }

        return false;
    }

    protected function isChallengeRequired(CaptchaRequirementRequestTransfer $captchaRequirementRequestTransfer, string $type, string $realm): bool
    {
        $securityCheckAuthContextTransfer = $this->securityCheckAuthContextBuilder->createSecurityCheckAuthContextTransferForIp(
            $captchaRequirementRequestTransfer->getIp(),
            $type,
            $realm,
            $captchaRequirementRequestTransfer->getLoginIdentifier(),
        );

        try {
            return (bool)$this->securityBlockerClient->checkLoginAttempt($securityCheckAuthContextTransfer)->getIsChallengeRequired();
        } catch (Exception $exception) {
            // The security blocker client wraps its own failures in SecurityBlockerException, but lets a Redis error through as it is.
            $this->logger->error(static::LOG_MESSAGE_LOGIN_ATTEMPT_NOT_READABLE, ['exception' => $exception]);

            return true;
        }
    }
}
