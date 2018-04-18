<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CompanyUserInvitationPage\Model\Errors;

use Generated\Shared\Transfer\CompanyUserInvitationImportResponseTransfer;

interface ImportErrorsHandlerInterface
{
    /**
     * @param \Generated\Shared\Transfer\CompanyUserInvitationImportResponseTransfer $companyUserInvitationImportReportTransfer
     *
     * @return mixed
     */
    public function storeCompanyUserInvitationImportErrors(
        CompanyUserInvitationImportResponseTransfer $companyUserInvitationImportReportTransfer
    );

    /**
     * @return array
     */
    public function retrieveCompanyUserInvitationImportErrors(): array;
}
