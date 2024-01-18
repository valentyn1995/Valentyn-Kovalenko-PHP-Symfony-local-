<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadFileService
{
    public function __construct(
        private string $targetDirectory,
    ) {
    }

    public function uploadFile(?UploadedFile $file): ?string
    {
        $newFileName = uniqid() . '.' . $file->guessExtension();
        $file->move($this->getTargetDirectory(), $newFileName);

        return $newFileName;
    }

    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }
}