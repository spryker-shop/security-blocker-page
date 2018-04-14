<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CompanyUserInvitationPage\Model\Errors;

use Generated\Shared\Transfer\CompanyUserInvitationImportResultTransfer;
use League\Csv\Reader;
use League\Csv\Writer;
use SprykerShop\Shared\CompanyUserInvitationPage\CompanyUserInvitationPageConstants;
use SprykerShop\Yves\CompanyUserInvitationPage\Dependency\Client\CompanyUserInvitationPageToSessionClientInterface;

class ImportErrorsHandler implements ImportErrorsHandlerInterface
{
    /**
     * @var \SprykerShop\Yves\CompanyUserInvitationPage\Dependency\Client\CompanyUserInvitationPageToSessionClientInterface
     */
    protected $sessionClient;

    /**
     * @param \SprykerShop\Yves\CompanyUserInvitationPage\Dependency\Client\CompanyUserInvitationPageToSessionClientInterface $sessionClient
     */
    public function __construct(
        CompanyUserInvitationPageToSessionClientInterface $sessionClient
    ) {
        $this->sessionClient = $sessionClient;
    }

    /**
     * @param \Generated\Shared\Transfer\CompanyUserInvitationImportResultTransfer $companyUserInvitationImportResultTransfer
     *
     * @return mixed
     */
    public function storeCompanyUserInvitationImportErrors(
        CompanyUserInvitationImportResultTransfer $companyUserInvitationImportResultTransfer
    ) {
        $importErrorsFile = $this->getImportErrorsFile();
        $writer = Writer::createFromPath($importErrorsFile, 'w+');
        foreach ($companyUserInvitationImportResultTransfer->getErrors() as $error) {
            $writer->insertOne($error);
        }

        return $this->sessionClient->set(
            CompanyUserInvitationPageConstants::INVITATION_IMPORT_ERRORS_FILE,
            $importErrorsFile
        );
    }

    /**
     * @return array
     */
    public function retrieveCompanyUserInvitationImportErrors(): array
    {
        $importErrorsFile = $this->sessionClient->get(CompanyUserInvitationPageConstants::INVITATION_IMPORT_ERRORS_FILE, null);
        if ($importErrorsFile) {
            return Reader::createFromPath($importErrorsFile, 'r')->fetchAll();
        }

        return [];
    }

    /**
     * @return string
     */
    protected function getImportErrorsFile(): string
    {
        return tempnam(
            CompanyUserInvitationPageConstants::IMPORT_ERRORS_FILE_PATH,
            CompanyUserInvitationPageConstants::IMPORT_ERRORS_FILE_PREFIX
        );
    }
}
