<?php

namespace App\Services;

use App\Exceptions\UploadException;
use App\Models\Image;
use Doctrine\ORM\EntityManagerInterface;

class ImageService
{
    /**
     * @var array
     */
    const VALID_IMAGES = ['image/png', 'image/jpg', 'image/gif', 'image/svg'];
    /**
     * @var array
     */
    const VALID_FILE_SUFFIX = ['jpg', 'jpeg', 'gif', 'png', 'svg'];
    /**
     * @var string
     */
    const MEDIA_IMAGE_DIR = 'media/images';
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var NiceUrlConverter
     */
    private $niceUrlConverter;
    /**
     * @var ImageResizeService
     */
    private $imageResizeService;

    /**
     * ImageService constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param NiceUrlConverter       $niceUrlConverter
     * @param ImageResizeService     $imageResizeService
     */
    public function __construct(EntityManagerInterface $entityManager, NiceUrlConverter $niceUrlConverter, ImageResizeService $imageResizeService)
    {
        $this->entityManager = $entityManager;
        $this->niceUrlConverter = $niceUrlConverter;
        $this->imageResizeService = $imageResizeService;
    }

    /**
     * @param array $images
     * @param array $imageData
     *
     * @throws UploadException
     * @throws \Exception
     */
    public function uploadImages(array $images, array $imageData)
    {
        foreach ($images as $counter => $rawImage) {
            $targetPath = $this->getTargetPath($rawImage['name']);
            $this->uploadImage($rawImage, $targetPath);
            $imageTitle = $counter > 0 ? $imageData['title'] . ' ' . $counter : $imageData['title'];

            $image = new Image();
            $image->setTitle($imageTitle);
            $image->setFilename($targetPath);

            $this->entityManager->persist($image);
            $this->entityManager->flush();
        }
    }

    /**
     * @return string
     */
    public function getMediaPath(): string
    {
        return $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . self::MEDIA_IMAGE_DIR;
    }

    /**
     * @param array  $rawImage
     * @param string $targetPath
     *
     * @return bool
     * @throws UploadException
     * @throws \Exception
     */
    private function uploadImage(array $rawImage, string $targetPath): bool
    {
        if ($rawImage['error'] > 0) {
            throw new UploadException($rawImage['error']);
        }

        if (!$this->isValidFilename($rawImage['name'])) {
            throw new \Exception(sprintf('Invalid Filename for image %s', $rawImage['name']));
        }

        if (!$this->isValidType($rawImage['type'])) {
            throw new \Exception(sprintf('Invalid Filetype for image %s. Got type %s.', $rawImage['name'], $rawImage['type']));
        }

        if (!move_uploaded_file($rawImage['tmp_name'], $targetPath)) {
            throw new \Exception(sprintf('Unable to move file %s to %s', $rawImage['tmp_name'], $targetPath));
        }

        return true;
    }

    /**
     * @param string $fileName
     *
     * @return bool
     */
    private function isValidFilename(string $fileName): bool
    {
        $fileNameParts = explode('.', $fileName);
        $fileSuffix = array_pop($fileNameParts);

        return in_array($fileSuffix, self::VALID_FILE_SUFFIX);
    }

    /**
     * @param string $fileType
     *
     * @return bool
     */
    private function isValidType(string $fileType): bool
    {
        return in_array($fileType, self::VALID_IMAGES);
    }

    /**
     * @param string $rawFileName
     * @param int    $recursionCounter
     *
     * @return string
     * @throws \Exception
     */
    private function getTargetPath(string $rawFileName, int $recursionCounter = 0): string
    {
        $maxRecustions = 100;

        $targetPath = $this->getMediaPath() . DIRECTORY_SEPARATOR . date('Y-m');

        if (!$this->createDirsIfNotExists($targetPath)) {
            throw new \Exception(sprintf('Could not create directory %s', $targetPath));
        }

        $niceUrl = $this->niceUrlConverter($rawFileName);
        $targetPath .= DIRECTORY_SEPARATOR . $niceUrl;

        if (file_exists($targetPath) && $recursionCounter <= $maxRecustions) {
            $targetPath = $this->getTargetPath($recursionCounter . '_' . $niceUrl);
        }

        if ($recursionCounter > $maxRecustions) {
            throw new \Exception(sprintf('Max recustions reached for file %s.', $rawFileName));
        }

        return $targetPath;
    }

    /**
     * @param string $filepath
     *
     * @return bool
     */
    private function createDirsIfNotExists(string $filepath): bool
    {
        if (is_dir($filepath)) {
            return true;
        }

        return mkdir($filepath, 0777, true);
    }
}
