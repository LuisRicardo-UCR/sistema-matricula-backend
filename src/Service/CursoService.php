<?php

namespace App\Service;

use App\Entity\Curso;
use App\Repository\CursoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\String\UnicodeString;

class CursoService
{
    private const TEXT_LENGTHS = [
        'codigo' => 50,
        'nombre' => 150,
        'horario' => 100,
        'grupo' => 20,
    ];

    public function __construct(
        private CursoRepository $cursoRepo,
        private EntityManagerInterface $entityManager,
        private FileUploader $fileUploader
    ) {}

    public function listar(): array
    {
        return array_map(static fn (Curso $curso): array => [
            'id' => $curso->getId(),
            'codigo' => $curso->getCodigo(),
            'nombre' => $curso->getNombre(),
            'descripcion' => $curso->getDescripcion(),
            'creditos' => $curso->getCreditos(),
            'fotografia' => $curso->getFotografia(),
            'horario' => $curso->getHorario(),
            'grupo' => $curso->getGrupo(),
            'cupoMaximo' => $curso->getCupoMaximo(),
            'cuposActuales' => count($curso->getMatriculas()),
            'estado' => $curso->getEstado(),
        ], $this->cursoRepo->findAll());
    }

    public function estudiantesPorCurso(int $id): array
    {
        $curso = $this->buscar($id);
        $estudiantes = [];

        foreach ($curso->getMatriculas() as $matricula) {
            $estudiantes[] = [
                'id' => $matricula->getEstudiante()?->getId(),
                'nombre' => $matricula->getEstudiante()?->getNombre(),
                'fechaMatricula' => $matricula->getFechaMatricula()?->format('Y-m-d H:i:s'),
                'estadoMatricula' => $matricula->getEstado(),
            ];
        }

        return $estudiantes;
    }

    public function crear(array $data, ?UploadedFile $fotografia = null): Curso
    {
        $curso = new Curso();
        $this->aplicarDatos($curso, $data, true);
        $this->guardarFotografia($curso, $fotografia);
        $curso->setEstado(count($curso->getMatriculas()) >= $curso->getCupoMaximo() ? 'Lleno' : 'Disponible');

        $this->entityManager->persist($curso);
        $this->entityManager->flush();

        return $curso;
    }

    public function actualizar(int $id, array $data, ?UploadedFile $fotografia = null): Curso
    {
        $curso = $this->buscar($id);
        $this->aplicarDatos($curso, $data, false);
        $this->guardarFotografia($curso, $fotografia);
        $this->actualizarEstado($curso);
        $this->entityManager->flush();

        return $curso;
    }

    public function eliminar(int $id): void
    {
        $curso = $this->buscar($id);
        if (!$curso->getMatriculas()->isEmpty()) {
            throw new ConflictHttpException('No se puede eliminar un curso que tiene matrículas asociadas');
        }

        $this->entityManager->remove($curso);
        $this->entityManager->flush();
    }

    private function buscar(int $id): Curso
    {
        $curso = $this->cursoRepo->find($id);
        if (!$curso) {
            throw new NotFoundHttpException('Curso no encontrado');
        }

        return $curso;
    }

    private function aplicarDatos(Curso $curso, array $data, bool $crear): void
    {
        foreach (self::TEXT_LENGTHS as $campo => $maximo) {
            if (!array_key_exists($campo, $data)) {
                if ($crear && in_array($campo, ['codigo', 'nombre'], true)) {
                    throw new BadRequestHttpException(sprintf('El campo %s es obligatorio', $campo));
                }
                if ($crear) {
                    match ($campo) {
                        'horario' => $curso->setHorario(''),
                        'grupo' => $curso->setGrupo(''),
                    };
                }
                continue;
            }

            $valor = $data[$campo];
            if (!is_string($valor) || trim($valor) === '') {
                throw new BadRequestHttpException(sprintf('El campo %s debe ser texto no vacío', $campo));
            }
            if ((new UnicodeString($valor))->length() > $maximo) {
                throw new BadRequestHttpException(sprintf(
                    'El campo %s no puede superar %d caracteres',
                    $campo,
                    $maximo
                ));
            }

            match ($campo) {
                'codigo' => $curso->setCodigo(trim($valor)),
                'nombre' => $curso->setNombre(trim($valor)),
                'horario' => $curso->setHorario(trim($valor)),
                'grupo' => $curso->setGrupo(trim($valor)),
            };
        }

        foreach (['creditos', 'cupoMaximo'] as $campo) {
            if (!array_key_exists($campo, $data)) {
                if ($crear) {
                    if ($campo === 'creditos') {
                        $curso->setCreditos(0);
                        continue;
                    }
                    throw new BadRequestHttpException(sprintf('El campo %s es obligatorio', $campo));
                }
                continue;
            }

            $valor = filter_var($data[$campo], FILTER_VALIDATE_INT);
            if ($valor === false || $valor < ($campo === 'cupoMaximo' ? 1 : 0)) {
                throw new BadRequestHttpException(sprintf('El campo %s debe ser un entero válido', $campo));
            }

            if ($campo === 'creditos') {
                $curso->setCreditos($valor);
            } else {
                $curso->setCupoMaximo($valor);
            }
        }

        if (array_key_exists('descripcion', $data)) {
            if ($data['descripcion'] !== null && !is_string($data['descripcion'])) {
                throw new BadRequestHttpException('La descripción debe ser texto');
            }
            $curso->setDescripcion($data['descripcion']);
        }
    }

    private function actualizarEstado(Curso $curso): void
    {
        $curso->setEstado(
            count($curso->getMatriculas()) >= $curso->getCupoMaximo() ? 'Lleno' : 'Disponible'
        );
    }

    private function guardarFotografia(Curso $curso, ?UploadedFile $fotografia): void
    {
        if ($fotografia !== null) {
            $fileName = $this->fileUploader->upload($fotografia);
            $curso->setFotografia('uploads/cursos/' . $fileName);
        }
    }
}
