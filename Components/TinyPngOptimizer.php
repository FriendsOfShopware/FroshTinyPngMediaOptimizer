<?php

namespace FroshTinyPngMediaOptimizer\Components;

use Shopware;
use Shopware\Bundle\MediaBundle\Optimizer\OptimizerInterface;

/**
 * Class TinyPngOptimizer
 */
class TinyPngOptimizer implements OptimizerInterface
{
    /**
     * @var array
     */
    public static $supportedMimeTypes;

    /**
     * @var TinyPngService
     */
    private $tinyPngService;

    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var array
     */
    private $pluginConfig;

    /**
     * OptimusOptimizer constructor.
     *
     * @param TinyPngService $tinyPngService
     * @param string         $rootDir
     * @param array          $pluginConfig
     */
    public function __construct(TinyPngService $tinyPngService, string $rootDir, array $pluginConfig)
    {
        $this->tinyPngService = $tinyPngService;
        $this->rootDir = $rootDir;
        $this->pluginConfig = $pluginConfig;
        self::$supportedMimeTypes = $pluginConfig['mimeTypes'];

        if(is_string(self::$supportedMimeTypes)) {
            self::$supportedMimeTypes = [$pluginConfig['mimeTypes']];
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'TinyPng.com by Frosh';
    }

    /**
     * @param string $filepath
     *
     * @throws TinyPngApiException
     * @throws TinyPngPersistanceException
     */
    public function run($filepath)
    {
        //ass of SW5.3 media optimizer uses tmp-Folder
        if (!$this->isShopware53()) {
            $filepath = $this->rootDir . $filepath;
        }

        $this->tinyPngService->optimize($filepath);
    }

    /**
     * @return array
     */
    public function getSupportedMimeTypes()
    {
        return self::$supportedMimeTypes;
    }

    /**
     * @return bool
     */
    public function isRunnable()
    {
        return $this->tinyPngService->verifyApiKey(true);
    }

    /**
     * Check if current environment is shopware 5.
     *
     * @return bool
     */
    public function isShopware53()
    {
        return version_compare(Shopware::VERSION, '5.3.0', '>=');
    }
}
