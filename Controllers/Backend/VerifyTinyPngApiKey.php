<?php

use FroshTinyPngMediaOptimizer\Components\TinyPngService;

class Shopware_Controllers_Backend_VerifyTinyPngApiKey extends \Enlight_Controller_Action implements \Shopware\Components\CSRFWhitelistAware
{
    public function getWhitelistedCSRFActions()
    {
        return ['index'];
    }

    public function indexAction()
    {
        $this->container->get('front')->Plugins()->ViewRenderer()->setNoRender();
        $config = $this->container->get('frosh_tinypng_optimizer.config');

        if (!$config['apiKey']) {
            $this->response->setBody('Key is missing! Saved?');
        } else {
            $cache = $this->container->get('shopware.cache_manager');
            $optimus = new TinyPngService($config['apiKey'], PHP_INT_MAX, $cache);

            if ($optimus->verifyApiKey()) {
                $optimusLimit = new TinyPngService($config['apiKey'], $config['limit'], $cache);

                if ($optimusLimit->verifyApiKey()) {
                    $this->response->setBody($config['apiKey'] . ' is valid');
                } else {
                    $this->response->setBody($config['apiKey'] . ' is valid, but limit ' . $config['limit'] . ' reached!');
                }
            } else {
                $this->response->setBody($config['apiKey'] . ' is NOT valid');
            }
        }
    }
}
