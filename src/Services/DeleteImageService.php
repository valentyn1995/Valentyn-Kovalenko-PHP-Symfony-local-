<?php

namespace App\Services;

use App\Repository\ImageRepository;
use App\Entity\Image;

class DeleteImageService
{
    public function __construct(private string $targetDirectory, private ImageRepository $imageRepository)
    {
    }

    public function deleteImage(Image $image): void
    {
        $pathToFileForDelete = $this->getTargetDirectory() . '/' . $image->getImageData();

        if (file_exists($pathToFileForDelete)) {
            unlink($pathToFileForDelete);
        }

        $this->imageRepository->delete($image);
    }

    private function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }
}
