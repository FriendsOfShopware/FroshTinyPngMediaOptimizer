<?php

namespace FroshTinyPngMediaOptimizer\Components;

use Shopware\Components\Plugin\CachedConfigReader;

/**
 * Class OptimusServiceFactory
 */
class TinyPngServiceFactory
{
    /**
     * @param CachedConfigReader $cachedConfigReader
     *
     * @return TinyPngService
     */
    public static function factory(CachedConfigReader $cachedConfigReader)
    {
        $config = $cachedConfigReader->getByPluginName('TinectTinyPngOptimizer');

        return new TinyPngService($config['apiKey'], $config['limit']);
    }
}
