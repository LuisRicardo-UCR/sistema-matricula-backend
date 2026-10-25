<?php
namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    public function __construct(
        private string $targetDirectory,
        private SluggerInterface $slugger
    ) {}

    public function upload(UploadedFile $file): string
    {
        // La carpeta se crea al primer uso si todavía no existe.
        if (!is_dir($this->getTargetDirectory())
            && !mkdir($this->getTargetDirectory(), 0775, true)
            && !is_dir($this->getTargetDirectory())
        ) {
            throw new \RuntimeException(sprintf(
                'No se pudo crear el directorio de carga: "%s".',
                $this->getTargetDirectory()
            ));
        }

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        try {
            $file->move($this->getTargetDirectory(), $fileName);
        } catch (FileException $e) {
            throw new \RuntimeException('Error al subir el archivo al servidor.', 0, $e);
        }

        return $fileName;
    }

    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }
}
