<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CompanyBusinessUnitWidget\Expander;

use Symfony\Component\Form\FormBuilderInterface;

interface OrderSearchFormFormExpanderInterface
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     *
     * @return void
     */
    public function expandOrderSearchFormWithBusinessUnitField(FormBuilderInterface $builder, array $options): void;
}
