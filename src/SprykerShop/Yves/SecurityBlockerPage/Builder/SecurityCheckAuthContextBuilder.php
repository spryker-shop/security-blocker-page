<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\SecurityBlockerPage\Builder;

use Generated\Shared\Transfer\SecurityCheckAuthContextTransfer;
use SprykerShop\Yves\SecurityBlockerPage\SecurityBlockerPageConfig;
use Symfony\Component\HttpFoundation\Request;

class SecurityCheckAuthContextBuilder implements SecurityCheckAuthContextBuilderInterface
{
    public function __construct(protected SecurityBlockerPageConfig $securityBlockerPageConfig)
    {
    }

    public function createSecurityCheckAuthContextTransfer(
        Request $request,
        string $type,
        string $realm,
        ?string $loginIdentifier
    ): SecurityCheckAuthContextTransfer {
        return $this->createSecurityCheckAuthContextTransferForIp($request->getClientIp(), $type, $realm, $loginIdentifier);
    }

    public function createSecurityCheckAuthContextTransferForIp(
        ?string $ip,
        string $type,
        string $realm,
        ?string $loginIdentifier
    ): SecurityCheckAuthContextTransfer {
        $securityCheckAuthContextTransfer = (new SecurityCheckAuthContextTransfer())
            ->setType($type)
            ->setIp($ip)
            ->setRealm($realm)
            ->setLoginIdentifier($loginIdentifier);

        if (!$this->securityBlockerPageConfig->useEmailContextForLoginSecurityBlocker()) {
            return $securityCheckAuthContextTransfer;
        }

        return $securityCheckAuthContextTransfer->setAccount($loginIdentifier ?? '');
    }
}
