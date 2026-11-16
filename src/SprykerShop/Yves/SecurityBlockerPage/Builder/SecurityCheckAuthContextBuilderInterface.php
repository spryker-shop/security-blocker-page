<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\SecurityBlockerPage\Builder;

use Generated\Shared\Transfer\SecurityCheckAuthContextTransfer;
use Symfony\Component\HttpFoundation\Request;

interface SecurityCheckAuthContextBuilderInterface
{
    public function createSecurityCheckAuthContextTransfer(
        Request $request,
        string $type,
        string $realm,
        ?string $loginIdentifier
    ): SecurityCheckAuthContextTransfer;

    public function createSecurityCheckAuthContextTransferForIp(
        ?string $ip,
        string $type,
        string $realm,
        ?string $loginIdentifier
    ): SecurityCheckAuthContextTransfer;
}
