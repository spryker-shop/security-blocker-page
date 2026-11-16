<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\SecurityBlockerPage;

use Spryker\Yves\Kernel\AbstractBundleConfig;
use SprykerShop\Shared\SecurityBlockerPage\SecurityBlockerPageConstants;

class SecurityBlockerPageConfig extends AbstractBundleConfig
{
    /**
     * Specification:
     * - Login protection strategy that counts and blocks the failed customer and agent logins per source IP,
     *   or per source IP and email when `useEmailContextForLoginSecurityBlocker()` returns true.
     *
     * @api
     */
    public const string LOGIN_PROTECTION_STRATEGY_IP = 'ip';

    /**
     * Specification:
     * - Login protection strategy with two separate counters of failed customer and agent logins: one per source IP and one per account.
     * - Blocks the login when either counter reaches its limit. The account counter ignores the source IP,
     *   so changing the IP does not unlock the account.
     * - The per-IP counter also includes the email when `useEmailContextForLoginSecurityBlocker()` returns true.
     *
     * @api
     */
    public const string LOGIN_PROTECTION_STRATEGY_IP_AND_ACCOUNT = 'ip-and-account';

    /**
     * @uses \Spryker\Client\SecurityBlockerStorefrontCustomer\SecurityBlockerStorefrontCustomerConfig::SECURITY_BLOCKER_CUSTOMER_ENTITY_TYPE
     *
     * @var string
     */
    public const SECURITY_BLOCKER_CUSTOMER_ENTITY_TYPE = 'customer';

    /**
     * @uses \Spryker\Client\SecurityBlockerStorefrontAgent\SecurityBlockerStorefrontAgentConfig::SECURITY_BLOCKER_AGENT_ENTITY_TYPE
     *
     * @var string
     */
    public const SECURITY_BLOCKER_AGENT_ENTITY_TYPE = 'agent';

    /**
     * @uses \Spryker\Shared\SecurityBlockerStorefrontCustomer\SecurityBlockerStorefrontCustomerConfig::LOGIN_REALM_CUSTOMER
     */
    public const string LOGIN_REALM_CUSTOMER = 'customer';

    /**
     * @uses \Spryker\Shared\SecurityBlockerBackoffice\SecurityBlockerBackofficeConfig::LOGIN_REALM_USER
     */
    public const string LOGIN_REALM_USER = 'user';

    /**
     * @uses \Spryker\Shared\Captcha\CaptchaConfig::CONTEXT_STOREFRONT_CUSTOMER_LOGIN
     */
    protected const string CUSTOMER_CAPTCHA_CONTEXT = 'storefront_customer_login';

    /**
     * @uses \Spryker\Shared\Captcha\CaptchaConfig::CONTEXT_STOREFRONT_AGENT_LOGIN
     */
    protected const string AGENT_CAPTCHA_CONTEXT = 'storefront_agent_login';

    /**
     * @var bool
     */
    protected const USE_EMAIL_CONTEXT_FOR_LOGIN_SECURITY_BLOCKER = true;

    protected const string LOGIN_PROTECTION_STRATEGY = self::LOGIN_PROTECTION_STRATEGY_IP;

    /**
     * Specification:
     * - Controls if local prefix is used in the /login_check path used for customer.
     *
     * @api
     *
     * @uses {@link \SprykerShop\Yves\CustomerPage\CustomerPageConfig::isLocaleInLoginCheckPath()}
     *
     * @deprecated Will be removed without replacement. In the future, the locale-specific URL will be used.
     */
    public function isLocaleInCustomerLoginCheckPath(): bool
    {
        return false;
    }

    /**
     * Specification:
     * - Controls if local prefix is used in the /agent/login_check path used for the agent.
     *
     * @api
     *
     * @uses {@link \SprykerShop\Yves\AgentPage\AgentPageConfig::isLocaleInLoginCheckPath()}
     *
     * @deprecated Will be removed without replacement. In the future, the locale-specific URL will be used.
     */
    public function isLocaleInAgentLoginCheckPath(): bool
    {
        return false;
    }

    /**
     * Specification:
     * - Defines whether the email context needs to be included in the login security blocker mechanism.
     * - Affects only the per-IP counter key, the per-account counter always uses the submitted email.
     *
     * @api
     */
    public function useEmailContextForLoginSecurityBlocker(): bool
    {
        return static::USE_EMAIL_CONTEXT_FOR_LOGIN_SECURITY_BLOCKER;
    }

    /**
     * Specification:
     * - Returns the key of the login protection strategy of the customer and agent login forms.
     * - {@link LOGIN_PROTECTION_STRATEGY_IP} (default): failed logins are counted and blocked per source IP only.
     * - {@link LOGIN_PROTECTION_STRATEGY_IP_AND_ACCOUNT}: failed logins also lock the account regardless of the source IP.
     * - A project strategy adds its key to `SecurityBlockerPageFactory::createCustomerLoginProtectionEventSubscriberStrategyResolver()`
     *   and `SecurityBlockerPageFactory::createAgentLoginProtectionEventSubscriberStrategyResolver()`.
     * - A key no strategy resolver knows fails with an exception.
     *
     * @api
     */
    public function getLoginProtectionStrategy(): string
    {
        return static::LOGIN_PROTECTION_STRATEGY;
    }

    /**
     * @api
     */
    public function isStoreRoutingEnabled(): bool
    {
        return $this->get(SecurityBlockerPageConstants::IS_STORE_ROUTING_ENABLED, false);
    }

    /**
     * Specification:
     * - Returns the CAPTCHA context of the Storefront customer login form, served by `SecurityBlockerLoginCaptchaRequirementPlugin`.
     *
     * @api
     */
    public function getCustomerCaptchaContext(): string
    {
        return static::CUSTOMER_CAPTCHA_CONTEXT;
    }

    /**
     * Specification:
     * - Returns the CAPTCHA context of the Storefront agent login form, served by `SecurityBlockerLoginCaptchaRequirementPlugin`.
     *
     * @api
     */
    public function getAgentCaptchaContext(): string
    {
        return static::AGENT_CAPTCHA_CONTEXT;
    }
}
