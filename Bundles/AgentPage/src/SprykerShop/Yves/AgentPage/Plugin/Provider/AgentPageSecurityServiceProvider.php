<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\AgentPage\Plugin\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Spryker\Yves\Kernel\AbstractPlugin;
use SprykerShop\Shared\AgentPage\AgentPageConfig;
use SprykerShop\Shared\CustomerPage\CustomerPageConfig;
use SprykerShop\Yves\AgentPage\Form\AgentLoginForm;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Http\Firewall\UsernamePasswordFormAuthenticationListener;

/**
 * @method \SprykerShop\Yves\AgentPage\AgentPageFactory getFactory()
 * @method \SprykerShop\Yves\AgentPage\AgentPageConfig getConfig()
 */
class AgentPageSecurityServiceProvider extends AbstractPlugin implements ServiceProviderInterface
{
    public const ROLE_AGENT = 'ROLE_AGENT';
    public const ROLE_ALLOWED_TO_SWITCH = 'ROLE_ALLOWED_TO_SWITCH';
    public const ROLE_USER = 'ROLE_USER';
    public const ROLE_PREVIOUS_ADMIN = 'ROLE_PREVIOUS_ADMIN';

    /**
     * @param \Silex\Application $app
     *
     * @return void
     */
    public function register(Application $app)
    {
        $this->setSecurityFirewalls($app);
        $this->setSecurityAccessRules($app);
        $this->setAuthenticationSuccessHandler($app);
        $this->setAuthenticationFailureHandler($app);
        $this->setSwitchUserEventSubscriber($app);
    }

    /**
     * @param \Silex\Application $app
     *
     * @return void
     */
    public function boot(Application $app)
    {
    }

    /**
     * @param \Silex\Application $app
     *
     * @return void
     */
    protected function setSecurityFirewalls(Application $app)
    {
        $selectedLanguage = $this->findSelectedLanguage($app);

        $app['security.firewalls'] = array_merge_recursive([
            AgentPageConfig::SECURITY_FIREWALL_NAME => [
                'context' => AgentPageConfig::SECURITY_FIREWALL_NAME,
                'anonymous' => false,
                'pattern' => $this->getConfig()->getAgentFirewallRegex(),
                'form' => [
                    'login_path' => '/agent/login',
                    'check_path' => '/agent/login_check',
                    'username_parameter' => AgentLoginForm::FORM_NAME . '[' . AgentLoginForm::FIELD_EMAIL . ']',
                    'password_parameter' => AgentLoginForm::FORM_NAME . '[' . AgentLoginForm::FIELD_PASSWORD . ']',
                    'listener_class' => UsernamePasswordFormAuthenticationListener::class,
                ],
                'logout' => [
                    'logout_path' => '/agent/logout',
                    'target_url' => $this->buildLogoutTargetUrl($selectedLanguage),
                ],
                'users' => $app->share(function () {
                    return $this->getFactory()->createAgentUserProvider();
                }),
                'switch_user' => [
                    'parameter' => '_switch_user',
                    'role' => static::ROLE_PREVIOUS_ADMIN,
                ],
            ],
            CustomerPageConfig::SECURITY_FIREWALL_NAME => [
                'context' => AgentPageConfig::SECURITY_FIREWALL_NAME,
                'switch_user' => [
                    'parameter' => '_switch_user',
                    'role' => static::ROLE_ALLOWED_TO_SWITCH,
                ],
            ],
        ], $app['security.firewalls']);
    }

    /**
     * @param \Silex\Application $app
     *
     * @return void
     */
    protected function setSecurityAccessRules(Application $app)
    {
        $app['security.access_rules'] = array_merge([
            [
                $this->getConfig()->getAgentFirewallRegex(),
                [
                    static::ROLE_AGENT,
                    static::ROLE_PREVIOUS_ADMIN,
                ],
            ],
        ], $app['security.access_rules']);
    }

    /**
     * @param \Silex\Application $app
     *
     * @return void
     */
    protected function setAuthenticationSuccessHandler(Application $app): void
    {
        $app['security.authentication.success_handler.' . AgentPageConfig::SECURITY_FIREWALL_NAME] = $app->share(function () {
            return $this->getFactory()->createAgentAuthenticationSuccessHandler();
        });
    }

    /**
     * @param \Silex\Application $app
     *
     * @return void
     */
    protected function setAuthenticationFailureHandler(Application $app): void
    {
        $app['security.authentication.failure_handler.' . AgentPageConfig::SECURITY_FIREWALL_NAME] = $app->share(function () {
            return $this->getFactory()->createAgentAuthenticationFailureHandler();
        });
    }

    /**
     * @param \Silex\Application $app
     *
     * @return void
     */
    protected function setSwitchUserEventSubscriber(Application $app): void
    {
        $this->getDispatcher($app)->addSubscriber(
            $this->getFactory()->createSwitchUserEventSubscriber()
        );
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     *
     * @param \Silex\Application $app
     *
     * @return string|null
     */
    protected function findSelectedLanguage(Application $app)
    {
        $currentLocale = $app['locale'];
        $requestUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';

        $prefixLocale = mb_substr($currentLocale, 0, 2);
        $localePath = mb_substr($requestUri, 1, 3);

        if ($prefixLocale . '/' !== $localePath) {
            return null;
        }
        return $prefixLocale;
    }

    /**
     * @param string $selectedLanguage
     *
     * @return string
     */
    protected function buildLogoutTargetUrl($selectedLanguage)
    {
        $logoutTarget = '/';
        if ($selectedLanguage) {
            $logoutTarget .= $selectedLanguage;
        }
        return $logoutTarget;
    }

    /**
     * @param \Silex\Application $app
     *
     * @return \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected function getDispatcher(Application $app): EventDispatcherInterface
    {
        return $app['dispatcher'];
    }
}
