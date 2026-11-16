<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\SecurityBlockerPage\Plugin\Captcha;

use Generated\Shared\Transfer\CaptchaRequirementRequestTransfer;
use Spryker\Shared\CaptchaExtension\Dependency\Plugin\CaptchaRequirementPluginInterface;
use Spryker\Yves\Kernel\AbstractPlugin;

/**
 * @method \SprykerShop\Yves\SecurityBlockerPage\SecurityBlockerPageFactory getFactory()
 * @method \SprykerShop\Yves\SecurityBlockerPage\SecurityBlockerPageConfig getConfig()
 */
class SecurityBlockerLoginCaptchaRequirementPlugin extends AbstractPlugin implements CaptchaRequirementPluginInterface
{
    /**
     * {@inheritDoc}
     * - Serves the `storefront_customer_login` and `storefront_agent_login` contexts; returns `false` for any other context.
     * - Returns `true` while the security blocker requires a challenge for the next login attempt of the client IP and login identifier:
     *   the per-IP, the account or the realm-wide failure counter reached its challenge threshold (realm `customer` for the customer
     *   login, realm `user` for the agent login).
     * - Uses the same per-IP counter key as the counted failed logins, including the email when `useEmailContextForLoginSecurityBlocker()` returns `true`.
     * - Returns `true` and logs an error when the security blocker cannot read the counters.
     *
     * @api
     */
    public function isCaptchaRequired(CaptchaRequirementRequestTransfer $captchaRequirementRequestTransfer): bool
    {
        return $this->getFactory()
            ->createLoginAttemptCaptchaRequirementChecker()
            ->isCaptchaRequired($captchaRequirementRequestTransfer);
    }
}
