<?php

namespace FroshTinyPngMediaOptimizer\Subscriber;

use Enlight\Event\SubscriberInterface;
use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware\Bundle\MediaBundle\OptimizerServiceInterface;
use Symfony\Component\HttpFoundation\File\File;

class MediaModel implements SubscriberInterface
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var OptimizerServiceInterface
     */
    private $optimizerService;

    /**
     * @var MediaServiceInterface
     */
    private $mediaService;

    /**
     * @param array $config
     * @param OptimizerServiceInterface $optimizerService
     * @param MediaServiceInterface $mediaService
     */
    public function __construct(array $config, OptimizerServiceInterface $optimizerService, MediaServiceInterface $mediaService)
    {
        $this->config = $config;
        $this->optimizerService = $optimizerService;
        $this->mediaService = $mediaService;
    }

    public static function getSubscribedEvents()
    {
        return [
            'Shopware\Models\Media\Media::prePersist' => 'optimizeOriginalImage',
        ];
    }

    public function optimizeOriginalImage(\Enlight_Event_EventArgs $args)
    {
        /** @var \Shopware\Models\Media\Media $media */
        $media = $args->getEntity();

        if(!$this->config['optimizeOriginal'] && $this->mediaService->has($media->getPath()))
            return;

        $basePath = $this->mediaService->getFilesystem()->getAdapter()->getPathPrefix();
        $realPath = $basePath . $this->mediaService->encode($media->getPath());
        $this->optimizerService->optimize($realPath);
        $media->setFile(new File($realPath));
    }
}