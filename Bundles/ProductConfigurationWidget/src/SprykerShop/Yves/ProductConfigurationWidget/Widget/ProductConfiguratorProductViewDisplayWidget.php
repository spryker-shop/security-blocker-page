<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\ProductConfigurationWidget\Widget;

use Generated\Shared\Transfer\ProductViewTransfer;
use Spryker\Yves\Kernel\Widget\AbstractWidget;

/**
 * @method \SprykerShop\Yves\ProductConfigurationWidget\ProductConfigurationWidgetFactory getFactory()
 * @method \SprykerShop\Yves\ProductConfigurationWidget\ProductConfigurationWidgetConfig getConfig()
 */
class ProductConfiguratorProductViewDisplayWidget extends AbstractWidget
{
    protected const PARAMETER_IS_VISIBLE = 'isVisible';
    protected const PARAMETER_PRODUCT_CONFIGURATION_INSTANCE = 'productConfigurationInstance';
    protected const PARAMETER_PRODUCT_CONFIGURATION_TEMPLATE = 'productConfigurationTemplate';

    /**
     * @param \Generated\Shared\Transfer\ProductViewTransfer $productViewTransfer
     */
    public function __construct(ProductViewTransfer $productViewTransfer)
    {
        $this->addIsVisibleParameter($productViewTransfer);

        if (!$productViewTransfer->getProductConfigurationInstance()) {
            return;
        }

        $this->addProductConfigurationInstanceParameter($productViewTransfer);
        $this->addProductConfigurationTemplateParameter($productViewTransfer);
    }

    /**
     * @return string
     */
    public static function getName(): string
    {
        return 'ProductConfiguratorProductViewDisplayWidget';
    }

    /**
     * @return string
     */
    public static function getTemplate(): string
    {
        return '@ProductConfigurationWidget/views/product-configurator-product-view-display-widget/product-configurator-product-view-display-widget.twig';
    }

    /**
     * @param \Generated\Shared\Transfer\ProductViewTransfer $productViewTransfer
     *
     * @return void
     */
    protected function addIsVisibleParameter(ProductViewTransfer $productViewTransfer): void
    {
        $this->addParameter(static::PARAMETER_IS_VISIBLE, $productViewTransfer->getProductConfigurationInstance());
    }

    /**
     * @param \Generated\Shared\Transfer\ProductViewTransfer $productViewTransfer
     *
     * @return void
     */
    protected function addProductConfigurationInstanceParameter(ProductViewTransfer $productViewTransfer): void
    {
        $this->addParameter(static::PARAMETER_PRODUCT_CONFIGURATION_INSTANCE, $productViewTransfer->getProductConfigurationInstance());
    }

    /**
     * @param \Generated\Shared\Transfer\ProductViewTransfer $productViewTransfer
     *
     * @return void
     */
    protected function addProductConfigurationTemplateParameter(ProductViewTransfer $productViewTransfer): void
    {
        $productConfigurationTemplateTransfer = $this->getFactory()
            ->createProductConfigurationTemplateResolver()
            ->resolveProductConfigurationTemplate($productViewTransfer->getProductConfigurationInstance());

        $this->addParameter(static::PARAMETER_PRODUCT_CONFIGURATION_TEMPLATE, $productConfigurationTemplateTransfer);
    }
}
