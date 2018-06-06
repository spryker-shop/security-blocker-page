<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\FileManager\Dependency\Service;

use Spryker\Service\FileManager\FileManagerServiceInterface;

class FileManagerToFileManagerServiceBridge implements FileManagerToFileManagerServiceInterface
{
    /**
     * @var \Spryker\Service\FileManager\FileManagerServiceInterface $fileManagerService
     */
    protected $fileManagerService;

    /**
     * @param \Spryker\Service\FileManager\FileManagerServiceInterface $fileManagerService
     */
    public function __construct(FileManagerServiceInterface $fileManagerService)
    {
        $this->fileManagerService = $fileManagerService;
    }

    /**
     * @param string $fileName
     *
     * @return \Generated\Shared\Transfer\FileManagerDataTransfer
     */
    public function read(string $fileName)
    {
        return $this->fileManagerService->read($fileName);
    }

    /**
     * @param string $fileName
     *
     * @return mixed
     */
    public function readStream(string $fileName)
    {
        return $this->fileManagerService->readStream($fileName);
    }
}
