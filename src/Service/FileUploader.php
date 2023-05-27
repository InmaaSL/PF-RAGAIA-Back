<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Psr\Log\LoggerInterface;

class FileUploader
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function uploadImage($uploadDir, $file, $filename)
    {
        $ext = $file->guessExtension();

        if($ext != "png" && $ext != "jpg" && $ext != "jpeg" && $ext != "svg")
        {
            throw new FileException('Failed to upload file');
            return;
        }

        try {

            $file->move($uploadDir, $filename);
        } catch (FileException $e){

            $this->logger->error('failed to upload image: ' . $e->getMessage());
            throw new FileException('Failed to upload file');
        }
    }

    public function uploadDocument($uploadDir, $file, $filename)
    {
        $ext = $file->guessExtension();

        if($ext != "pdf" )
        {
            throw new FileException('Failed to upload file');
            return;
        }

        try {

            $file->move($uploadDir, $filename);
        } catch (FileException $e){

            $this->logger->error('failed to upload pdf: ' . $e->getMessage());
            throw new FileException('Failed to upload file');
        }
    }

}