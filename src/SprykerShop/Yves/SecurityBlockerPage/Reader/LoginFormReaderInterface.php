<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\SecurityBlockerPage\Reader;

use Symfony\Component\HttpFoundation\Request;

interface LoginFormReaderInterface
{
    public function isLoginFormSubmitted(Request $request): bool;

    public function findSubmittedLoginIdentifier(Request $request): ?string;

    public function saveLastLoginIdentifier(Request $request, string $loginIdentifier): void;
}
