<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\SecurityBlockerPage\Dependency\Client;

use Generated\Shared\Transfer\SecurityCheckAuthContextTransfer;
use Generated\Shared\Transfer\SecurityCheckAuthResponseTransfer;

class SecurityBlockerPageToSecurityBlockerClientBridge implements SecurityBlockerPageToSecurityBlockerClientInterface
{
    /**
     * @var \Spryker\Client\SecurityBlocker\SecurityBlockerClientInterface
     */
    protected $securityBlockerClient;

    /**
     * @param \Spryker\Client\SecurityBlocker\SecurityBlockerClientInterface $securityBlockerClient
     */
    public function __construct($securityBlockerClient)
    {
        $this->securityBlockerClient = $securityBlockerClient;
    }

    public function incrementLoginAttemptCount(SecurityCheckAuthContextTransfer $securityCheckAuthContextTransfer): SecurityCheckAuthResponseTransfer
    {
        return $this->securityBlockerClient->incrementLoginAttemptCount($securityCheckAuthContextTransfer);
    }

    public function isAccountBlocked(SecurityCheckAuthContextTransfer $securityCheckAuthContextTransfer): SecurityCheckAuthResponseTransfer
    {
        return $this->securityBlockerClient->isAccountBlocked($securityCheckAuthContextTransfer);
    }

    public function checkLoginAttempt(SecurityCheckAuthContextTransfer $securityCheckAuthContextTransfer): SecurityCheckAuthResponseTransfer
    {
        return $this->securityBlockerClient->checkLoginAttempt($securityCheckAuthContextTransfer);
    }

    public function reserveLoginAttempt(SecurityCheckAuthContextTransfer $securityCheckAuthContextTransfer): SecurityCheckAuthResponseTransfer
    {
        return $this->securityBlockerClient->reserveLoginAttempt($securityCheckAuthContextTransfer);
    }

    public function registerFailedLoginAttempt(SecurityCheckAuthContextTransfer $securityCheckAuthContextTransfer): SecurityCheckAuthResponseTransfer
    {
        return $this->securityBlockerClient->registerFailedLoginAttempt($securityCheckAuthContextTransfer);
    }

    public function registerSuccessfulLoginAttempt(SecurityCheckAuthContextTransfer $securityCheckAuthContextTransfer): SecurityCheckAuthResponseTransfer
    {
        return $this->securityBlockerClient->registerSuccessfulLoginAttempt($securityCheckAuthContextTransfer);
    }
}
