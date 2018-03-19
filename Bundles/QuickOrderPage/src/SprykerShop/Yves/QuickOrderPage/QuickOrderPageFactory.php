<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\QuickOrderPage;

use Spryker\Yves\Kernel\AbstractFactory;
use SprykerShop\Yves\QuickOrderPage\Dependency\Client\QuickOrderPageToCartClientInterface;
use SprykerShop\Yves\QuickOrderPage\Dependency\Client\QuickOrderPageToMessengerClientInterface;
use SprykerShop\Yves\QuickOrderPage\Form\FormFactory;
use SprykerShop\Yves\QuickOrderPage\Form\Handler\QuickOrderFormOperationHandler;
use SprykerShop\Yves\QuickOrderPage\Form\Handler\QuickOrderFormOperationHandlerInterface;
use SprykerShop\Yves\QuickOrderPage\Model\TextOrderParser;
use SprykerShop\Yves\QuickOrderPage\Model\TextOrderParserInterface;

/**
 * @method \SprykerShop\Yves\QuickOrderPage\QuickOrderPageConfig getConfig()
 */
class QuickOrderPageFactory extends AbstractFactory
{
    /**
     * @return \SprykerShop\Yves\QuickOrderPage\QuickOrderPageConfig
     */
    public function getBundleConfig(): QuickOrderPageConfig
    {
        return $this->getConfig();
    }

    /**
     * @return \SprykerShop\Yves\QuickOrderPage\Form\FormFactory
     */
    public function createQuickOrderFormFactory(): FormFactory
    {
        return new FormFactory();
    }

    /**
     * @return \SprykerShop\Yves\QuickOrderPage\Form\Handler\QuickOrderFormOperationHandlerInterface
     */
    public function createFormOperationHandler(): QuickOrderFormOperationHandlerInterface
    {
        return new QuickOrderFormOperationHandler($this->getCartClient(), $this->getMessengerClient());
    }

    /**
     * @param string $textOrder
     *
     * @return \SprykerShop\Yves\QuickOrderPage\Model\TextOrderParserInterface
     */
    public function createTextOrderParser(string $textOrder): TextOrderParserInterface
    {
        return new TextOrderParser($textOrder, $this->getConfig());
    }

    /**
     * @return \SprykerShop\Yves\QuickOrderPage\Dependency\Client\QuickOrderPageToCartClientInterface
     */
    public function getCartClient(): QuickOrderPageToCartClientInterface
    {
        return $this->getProvidedDependency(QuickOrderPageDependencyProvider::CLIENT_CART);
    }

    /**
     * @return \SprykerShop\Yves\QuickOrderPage\Dependency\Client\QuickOrderPageToMessengerClientInterface
     */
    public function getMessengerClient(): QuickOrderPageToMessengerClientInterface
    {
        return $this->getProvidedDependency(QuickOrderPageDependencyProvider::CLIENT_MESSENGER);
    }
}
