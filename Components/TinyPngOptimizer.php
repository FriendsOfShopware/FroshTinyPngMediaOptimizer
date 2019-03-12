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
    public static $supportedMimeTypes = ['image/png'];
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
    public function __construct(TinyPngService $tinyPngService, $rootDir, array $pluginConfig)
    {
        $this->tinyPngService = $tinyPngService;
        $this->rootDir = $rootDir;
        $this->pluginConfig = $pluginConfig;
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
        /*
         * TODO: consider using cache
         */
        return $this->tinyPngService->verifyApiKey();
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
