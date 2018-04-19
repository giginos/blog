<?php

namespace App\Factory;

use App\Services\ImageService;
use App\Services\ImageResizeService;
use App\Services\NiceUrlConverter;

use Doctrine\ORM\EntityManagerInterface;

class ImageServiceFactory {
    /**
     * @param EntityManagerInterface $entityManager
     *
     * @return ImageService
     */
    public static function build(EntityManagerInterface $entityManager): ImageService
    {
        $imageResizeService = new ImageResizeService();
        $niceUrlConverter = new NiceUrlConverter();

        return new ImageService($entityManager, $niceUrlConverter, $imageResizeService);
    }
}
