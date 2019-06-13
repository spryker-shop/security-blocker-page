<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\ProductQuantityRestrictionWidget\QuantityRestrictionReader;

use Generated\Shared\Transfer\ProductQuantityStorageTransfer;

interface QuantityRestrictionReaderInterface
{
    /**
     * @param int $idProductConcrete
     *
     * @return \Generated\Shared\Transfer\ProductQuantityStorageTransfer
     */
    public function getQuantityRestrictions(int $idProductConcrete): ProductQuantityStorageTransfer;
}
