<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CustomerPage\Form;

use Spryker\Yves\Kernel\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @method \SprykerShop\Yves\CustomerPage\CustomerPageFactory getFactory()
 */
class PasswordForm extends AbstractType
{
    public const FIELD_NEW_PASSWORD = 'new_password';
    public const FIELD_PASSWORD = 'password';

    public const OPTION_MIN_LENGTH_CUSTOMER_PASSWORD = 'OPTION_MIN_LENGTH_CUSTOMER_PASSWORD';

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'passwordForm';
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this
            ->addPasswordField($builder)
            ->addNewPasswordField($builder, $options);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     *
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(static::OPTION_MIN_LENGTH_CUSTOMER_PASSWORD);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     *
     * @return $this
     */
    protected function addNewPasswordField(FormBuilderInterface $builder, array $options)
    {
        $builder->add(self::FIELD_NEW_PASSWORD, RepeatedType::class, [
            'first_name' => self::FIELD_PASSWORD,
            'second_name' => 'confirm',
            'type' => PasswordType::class,
            'invalid_message' => 'validator.constraints.password.do_not_match',
            'required' => true,
            'first_options' => [
                'label' => 'customer.password.request.new_password',
                'attr' => ['autocomplete' => 'off'],
            ],
            'second_options' => [
                'label' => 'customer.password.confirm.new_password',
                'attr' => ['autocomplete' => 'off'],
            ],
            'constraints' => [
                new NotBlank(),
                new Length([
                    'min' => $options[static::OPTION_MIN_LENGTH_CUSTOMER_PASSWORD],
                ]),
            ],
        ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addPasswordField(FormBuilderInterface $builder)
    {
        $builder->add(self::FIELD_PASSWORD, PasswordType::class, [
            'label' => 'customer.password.old_password',
            'required' => true,
            'attr' => [
                'autocomplete' => 'off',
            ],
        ]);

        return $this;
    }
}
