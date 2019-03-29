<?php

namespace FroshTinyPngMediaOptimizer\Components;

use Shopware\Components\CacheManager;

/**
 * Class TinyPngServiceFactory
 */
class TinyPngServiceFactory
{
    /**
     * @param array $config
     * @param CacheManager $cacheManager
     * @return TinyPngService
     */
    public static function factory(array $config, CacheManager $cacheManager)
    {
        return new TinyPngService($config['apiKey'], $config['limit'], $cacheManager->getCoreCache());
    }
}
