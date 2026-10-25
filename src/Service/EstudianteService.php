<?php

namespace App\Service;

use App\Entity\Estudiante;
use App\Repository\EstudianteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\String\UnicodeString;

class EstudianteService
{
    private const NOMBRE_MAX_LENGTH = 150;

    public function __construct(
        private EstudianteRepository $estudianteRepo,
        private EntityManagerInterface $entityManager
    ) {}

    public function listar(): array
    {
        return array_map(static fn (Estudiante $estudiante): array => [
            'id' => $estudiante->getId(),
            'nombre' => $estudiante->getNombre(),
        ], $this->estudianteRepo->findAll());
    }

    public function obtener(int $id): Estudiante
    {
        return $this->buscar($id);
    }

    public function crear(mixed $nombre): Estudiante
    {
        $nombre = $this->validarNombre($nombre);
        $estudiante = new Estudiante();
        $estudiante->setNombre($nombre);

        $this->entityManager->persist($estudiante);
        $this->entityManager->flush();

        return $estudiante;
    }

    public function actualizar(int $id, mixed $nombre): Estudiante
    {
        $estudiante = $this->buscar($id);
        if ($nombre !== null) {
            $estudiante->setNombre($this->validarNombre($nombre));
            $this->entityManager->flush();
        }

        return $estudiante;
    }

    public function eliminar(int $id): void
    {
        $estudiante = $this->buscar($id);
        if (!$estudiante->getMatriculas()->isEmpty()) {
            throw new BadRequestHttpException(
                'No se puede eliminar un estudiante que tiene matrículas asociadas'
            );
        }

        $this->entityManager->remove($estudiante);
        $this->entityManager->flush();
    }

    private function buscar(int $id): Estudiante
    {
        $estudiante = $this->estudianteRepo->find($id);
        if (!$estudiante) {
            throw new NotFoundHttpException('Estudiante no encontrado');
        }

        return $estudiante;
    }

    private function validarNombre(mixed $nombre): string
    {
        if (!is_string($nombre) || trim($nombre) === '') {
            throw new BadRequestHttpException('El nombre del estudiante es obligatorio');
        }

        if ((new UnicodeString($nombre))->length() > self::NOMBRE_MAX_LENGTH) {
            throw new BadRequestHttpException('El nombre del estudiante no puede superar 150 caracteres');
        }

        return trim($nombre);
    }
}
