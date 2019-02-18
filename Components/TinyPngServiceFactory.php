<?php

namespace FroshTinyPngMediaOptimizer\Components;

/**
 * Class OptimusServiceFactory
 */
class TinyPngServiceFactory
{
    /**
     * @param $pluginconfig
     * @return TinyPngService
     */
    public static function factory($pluginconfig)
    {
        return new TinyPngService($pluginconfig['apiKey'], $pluginconfig['limit']);
    }
}
