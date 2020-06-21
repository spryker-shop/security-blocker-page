<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\MultiCartWidget;

use Spryker\Shared\Application\ApplicationConstants;
use Spryker\Yves\Kernel\AbstractFactory;
use SprykerShop\Yves\MultiCartWidget\Dependency\Client\MultiCartWidgetToMultiCartClientInterface;
use SprykerShop\Yves\MultiCartWidget\Dependency\Client\MultiCartWidgetToQuoteClientInterface;
use SprykerShop\Yves\MultiCartWidget\Form\MultiCartClearForm;
use SprykerShop\Yves\MultiCartWidget\Form\MultiCartDuplicateForm;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class MultiCartWidgetFactory extends AbstractFactory
{
    /**
     * @return \SprykerShop\Yves\MultiCartWidget\Dependency\Client\MultiCartWidgetToMultiCartClientInterface
     */
    public function getMultiCartClient(): MultiCartWidgetToMultiCartClientInterface
    {
        return $this->getProvidedDependency(MultiCartWidgetDependencyProvider::CLIENT_MULTI_CART);
    }

    /**
     * @return \SprykerShop\Yves\MultiCartWidget\Dependency\Client\MultiCartWidgetToQuoteClientInterface
     */
    public function getQuoteClient(): MultiCartWidgetToQuoteClientInterface
    {
        return $this->getProvidedDependency(MultiCartWidgetDependencyProvider::CLIENT_QUOTE);
    }

    /**
     * @return array
     */
    public function getViewExtendWidgetPlugins(): array
    {
        return $this->getProvidedDependency(MultiCartWidgetDependencyProvider::PLUGINS_VIEW_EXTEND);
    }

    /**
     * @return \Symfony\Component\Form\FormFactoryInterface
     */
    public function getFormFactory(): FormFactoryInterface
    {
        return $this->getProvidedDependency(ApplicationConstants::FORM_FACTORY);
    }

    /**
     * @return \Symfony\Component\Form\FormInterface
     */
    public function getMultiCartClearForm(): FormInterface
    {
        return $this->getFormFactory()->create(MultiCartClearForm::class);
    }

    /**
     * @return \Symfony\Component\Form\FormInterface
     */
    public function getMultiCartDuplicateForm(): FormInterface
    {
        return $this->getFormFactory()->create(MultiCartDuplicateForm::class);
    }
}
