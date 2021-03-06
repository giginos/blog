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
     * @var array
     */
    const THUMBNAIL_DIMENSIONS = [100, 300, 600, 900, 1200];
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
            $image->setFilename(str_replace($_SERVER['DOCUMENT_ROOT'], '', $targetPath));

            $this->entityManager->persist($image);
            $this->entityManager->flush();

            $this->createThumbnails($targetPath);
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
     * @param int    $start
     * @param int    $amount
     * @param string $orderBy
     * @param string $orderDirection
     *
     * @return Image[]
     */
    public function listImages($start = 0, $amount = 20, $orderBy = 'id', $orderDirection = 'ASC'): array
    {
        $imageRepro = $this->entityManager->getRepository(Image::class);

        return $imageRepro->findBy([], [$orderBy => $orderDirection], $amount, $start);
    }

    /**
     * @param int $imageId
     *
     * @return null|Image
     */
    public function getImage(int $imageId)
    {
        $imageRepro = $this->entityManager->getRepository(Image::class);

        return $imageRepro->find($imageId);
    }

    /**
     * @param string $imageFileName
     *
     * @return array
     */
    public function getThumbnailsForImage(string $imageFileName)
    {
        $thumbs = [];
        $thumbnailPath = array_shift(array_values(explode('.', $imageFileName)));

        foreach (self::THUMBNAIL_DIMENSIONS as $thumbnailMaxDimension) {
            $thumbs[] = $thumbnailPath . '_' . $thumbnailMaxDimension . '.jpg';
        }

        return $thumbs;
    }

    /**
     * @param int $imageId
     */
    public function deleteImage(int $imageId)
    {
        $imageRepro = $this->entityManager->getRepository(Image::class);
        // $postRepro = $this->entityManager->getRepository(Post::class);

        $image = $imageRepro->find($imageId);

        // Cleanup post relations
        // @TODO: Check if cleanup is required

        // Delete images and thumbnails on filesystem
        $fileName = $this->getMediaPath() . DIRECTORY_SEPARATOR . $image->getFilename();
        unlink($fileName);

        foreach ($this->getThumbnailsForImage($image->getFilename()) as $thumb) {
            $fileName = $this->getMediaPath() . DIRECTORY_SEPARATOR . $thumb;
            unlink($fileName);
        }

        $this->entityManager->remove($image);
        $this->entityManager->flush();
    }

    public function searchImage(string $searchString)
    {
        $query = $this->entityManager->createQuery(
            'SELECT i.id, i.title
            FROM App\\Models\\Image i
            WHERE i.title LIKE :title
            ORDER BY i.title ASC'
        )->setParameter('title', $searchString . '%');

        return $query->getResult();
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
        $maxRecursions = 100;

        if ($recursionCounter > $maxRecursions) {
            throw new \Exception(sprintf('Max recursions reached for file %s.', $rawFileName));
        }

        $targetPath = $this->getMediaPath() . DIRECTORY_SEPARATOR . date('Y-m');

        if (!$this->createDirsIfNotExists($targetPath)) {
            throw new \Exception(sprintf('Could not create directory %s', $targetPath));
        }

        $niceFilename = $this->niceUrlConverter->getCleanFilename($rawFileName);
        $targetPath .= DIRECTORY_SEPARATOR . $niceFilename;

        if (file_exists($targetPath)) {
            $recursionCounter++;
            $targetPath = $this->getTargetPath($recursionCounter . '_' . $niceFilename, $recursionCounter);
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

    /**
     * @param string $filepath
     *
     * @throws \Exception
     */
    private function createThumbnails(string $filepath)
    {
        $thumbnailPath = array_shift(array_values(explode('.', $filepath)));

        foreach (self::THUMBNAIL_DIMENSIONS as $thumbnailMaxDimension) {
            $this->imageResizeService->init($filepath);
            $this->imageResizeService->resizeWithinDimensions($thumbnailMaxDimension, $thumbnailMaxDimension);

            $this->imageResizeService->saveImageFile(
                $thumbnailPath . '_' . $thumbnailMaxDimension,
                IMAGETYPE_JPEG
            );
        }
    }
}
