<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\SecurityBlockerPage\Reader;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

class LoginFormReader implements LoginFormReaderInterface
{
    /**
     * @uses \SprykerShop\Yves\CustomerPage\Form\LoginForm::FORM_NAME
     * @uses \SprykerShop\Yves\AgentPage\Form\AgentLoginForm::FORM_NAME
     */
    protected const string FORM_LOGIN_FORM = 'loginForm';

    /**
     * @uses \SprykerShop\Yves\CustomerPage\Form\LoginForm::FIELD_EMAIL
     * @uses \SprykerShop\Yves\AgentPage\Form\AgentLoginForm::FIELD_EMAIL
     */
    protected const string FORM_FIELD_EMAIL = 'email';

    public function isLoginFormSubmitted(Request $request): bool
    {
        return $request->request->has(static::FORM_LOGIN_FORM);
    }

    public function findSubmittedLoginIdentifier(Request $request): ?string
    {
        return $this->findSubmittedField($request, static::FORM_FIELD_EMAIL);
    }

    public function saveLastLoginIdentifier(Request $request, string $loginIdentifier): void
    {
        if (!$request->hasSession()) {
            return;
        }

        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $loginIdentifier);
    }

    protected function findSubmittedField(Request $request, string $fieldName): ?string
    {
        $loginForm = $request->request->all()[static::FORM_LOGIN_FORM] ?? null;
        $value = is_array($loginForm) ? ($loginForm[$fieldName] ?? null) : null;

        return is_string($value) ? $value : null;
    }
}
