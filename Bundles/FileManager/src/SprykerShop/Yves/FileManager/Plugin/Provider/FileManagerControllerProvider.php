<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\FileManager\Plugin\Provider;

use Silex\Application;
use SprykerShop\Yves\ShopApplication\Plugin\Provider\AbstractYvesControllerProvider;

class FileManagerControllerProvider extends AbstractYvesControllerProvider
{
    const ROUTE_FILES_DOWNLOAD = 'files/download';

    /**
     * @var string
     */
    protected $allowedLocalesPattern;

    /**
     * @inheritdoc
     */
    public function __construct(?bool $sslEnabled = null)
    {
        parent::__construct($sslEnabled);

        $this->allowedLocalesPattern = $this->getAllowedLocalesPattern();
    }

    /**
     * @param \Silex\Application $app
     *
     * @return void
     */
    protected function defineControllers(Application $app)
    {
        $this->createFilesController('/download', self::ROUTE_FILES_DOWNLOAD, 'Download');
    }

    /**
     * @param string $path
     * @param string $name
     * @param string $controllerName
     * @param string $action
     *
     * @return void
     */
    protected function createFilesController(string $path, string $name, string $controllerName, $action = 'index')
    {
        $urlPath = '/{files}' . $path;

        $this->createController($urlPath, $name, 'FileManager', $controllerName, $action)
            ->assert('files', $this->allowedLocalesPattern . 'files|files')
            ->value('files', 'files');
    }
}
