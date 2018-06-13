<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\QuickOrderPage\Form\Constraint;

use InvalidArgumentException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class TextOrderCorrectConstraintValidator extends ConstraintValidator
{
    protected const ROWS_SPLITTER_PATTERN = '/\r\n|\r|\n/';

    /**
     * @param mixed $value The value that should be validated
     * @param \Symfony\Component\Validator\Constraint|\SprykerShop\Yves\QuickOrderPage\Form\Constraint\TextOrderCorrectConstraint $constraint The constraint for the validation
     *
     * @throws \InvalidArgumentException
     *
     * @return void
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof TextOrderCorrectConstraint) {
            throw new InvalidArgumentException(sprintf(
                'Expected constraint instance of %s, got %s instead.',
                TextOrderCorrectConstraint::class,
                get_class($constraint)
            ));
        }

        if ($value === null) {
            return;
        }

        if (!$this->checkFormat($value, $constraint->getAllowedSeparators())) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation();
        }
    }

    /**
     * @param string $textOrder
     * @param array $allowedSeparators
     *
     * @return bool
     */
    protected function checkFormat(string $textOrder, array $allowedSeparators): bool
    {
        foreach ($allowedSeparators as $separator) {
            if (strpos($textOrder, $separator) !== false && $this->checkEachRow($textOrder, $separator)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $textOrder
     * @param string $separator
     *
     * @return bool
     */
    protected function checkEachRow(string $textOrder, string $separator): bool
    {
        $rows = $this->getTextOrderRows($textOrder);
        foreach ($rows as $row) {
            if (!preg_match("/\w[$separator]\d+$/", $row)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $textOrder
     *
     * @return array
     */
    protected function getTextOrderRows(string $textOrder): array
    {
        return array_filter(preg_split(static::ROWS_SPLITTER_PATTERN, $textOrder));
    }
}
