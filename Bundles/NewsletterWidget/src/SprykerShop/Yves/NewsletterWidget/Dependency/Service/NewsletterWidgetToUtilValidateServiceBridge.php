<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\NewsletterWidget\Dependency\Service;

class NewsletterWidgetToUtilValidateServiceBridge implements NewsletterWidgetToUtilValidateServiceInterface
{
    /**
     * @var \Spryker\Service\UtilValidate\UtilValidateServiceInterface
     */
    protected $utilValidateService;

    /**
     * @param \Spryker\Service\UtilValidate\UtilValidateServiceInterface $utilValidateService
     */
    public function __construct($utilValidateService)
    {
        $this->utilValidateService = $utilValidateService;
    }

    /**
     * @param string $email
     *
     * @return bool
     */
    public function isEmailFormatValid($email)
    {
        return $this->utilValidateService->isEmailFormatValid($email);
    }
}
