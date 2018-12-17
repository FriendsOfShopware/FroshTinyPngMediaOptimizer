<?php


use TinectTinyPngOptimizer\Components\TinyPngService;

class Shopware_Controllers_Backend_VerifyTinyPngApiKey extends \Enlight_Controller_Action implements \Shopware\Components\CSRFWhitelistAware
{
    public function getWhitelistedCSRFActions()
    {
        return ['index'];
    }

    public function indexAction()
    {

        $this->container->get('front')->Plugins()->ViewRenderer()->setNoRender();
        $config = Shopware()->Container()->get('shopware.plugin.config_reader')->getByPluginName('TinectTinyPngOptimizer');

        if (!$config['apiKey']) {
            echo "Key is missing! Saved?";
        } else {
            $optimus = new TinyPngService($config['apiKey'], PHP_INT_MAX);
            if ($optimus->verifyApiKey()) {
                $optimusLimit = new TinyPngService($config['apiKey'], $config['limit']);

                if ($optimusLimit->verifyApiKey()) {
                    $this->response->setBody($config['apiKey'] . " is valid");
                } else {
                    $this->response->setBody($config['apiKey'] . " is valid, but limit $config[limit] reached!");
                }
            } else {
                $this->response->setBody($config['apiKey'] . " is NOT valid");
            }
        }
    }


}

?>