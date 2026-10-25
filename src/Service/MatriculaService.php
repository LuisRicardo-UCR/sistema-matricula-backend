<?php

namespace App\Service;

use App\Entity\Curso;
use App\Entity\Matricula;
use App\Repository\CursoRepository;
use App\Repository\EstudianteRepository;
use App\Repository\MatriculaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MatriculaService
{
    public function __construct(
        private CursoRepository $cursoRepo,
        private EstudianteRepository $estudianteRepo,
        private MatriculaRepository $matriculaRepo,
        private EntityManagerInterface $entityManager
    ) {}

    public function listar(): array
    {
        return array_map(static fn (Matricula $matricula): array => [
            'id' => $matricula->getId(),
            'estudiante' => [
                'id' => $matricula->getEstudiante()?->getId(),
                'nombre' => $matricula->getEstudiante()?->getNombre(),
            ],
            'curso' => [
                'id' => $matricula->getCurso()?->getId(),
                'codigo' => $matricula->getCurso()?->getCodigo(),
                'nombre' => $matricula->getCurso()?->getNombre(),
            ],
            'fechaMatricula' => $matricula->getFechaMatricula()?->format('Y-m-d H:i:s'),
            'estado' => $matricula->getEstado(),
        ], $this->matriculaRepo->findAll());
    }

    public function registrarMatricula(mixed $estudianteId, mixed $cursoId): void
    {
        $estudianteId = $this->validarId($estudianteId, 'estudianteId');
        $cursoId = $this->validarId($cursoId, 'cursoId');
        $estudiante = $this->estudianteRepo->find($estudianteId);
        $curso = $this->buscarCurso($cursoId);

        if (!$estudiante) {
            throw new NotFoundHttpException('Estudiante no encontrado');
        }

        $this->validarCupoDisponible($curso);

        $matricula = new Matricula();
        $matricula->setEstudiante($estudiante);
        $matricula->setCurso($curso);
        $matricula->setFechaMatricula(new \DateTime());
        $matricula->setEstado('Activa');
        $curso->addMatricula($matricula);

        $this->actualizarEstadoCurso($curso, count($curso->getMatriculas()));
        $this->entityManager->persist($matricula);
        $this->entityManager->flush();
    }

    public function actualizarMatricula(int $matriculaId, mixed $estudianteId, mixed $cursoId): void
    {
        $estudianteId = $this->validarId($estudianteId, 'estudianteId');
        $cursoId = $this->validarId($cursoId, 'cursoId');
        $matricula = $this->matriculaRepo->find($matriculaId);
        if (!$matricula) {
            throw new NotFoundHttpException('Matrícula no encontrada');
        }

        $estudiante = $this->estudianteRepo->find($estudianteId);
        if (!$estudiante) {
            throw new NotFoundHttpException('Estudiante no encontrado');
        }

        $nuevoCurso = $this->buscarCurso($cursoId);
        $cursoAnterior = $matricula->getCurso();

        if ($cursoAnterior !== $nuevoCurso) {
            $this->validarCupoDisponible($nuevoCurso);
            $cursoAnterior?->removeMatricula($matricula);
            $nuevoCurso->addMatricula($matricula);
            $this->actualizarEstadoCurso($nuevoCurso, count($nuevoCurso->getMatriculas()));
            if ($cursoAnterior !== null) {
                $this->actualizarEstadoCurso($cursoAnterior, count($cursoAnterior->getMatriculas()));
            }
        }

        $matricula->setEstudiante($estudiante);
        $matricula->setCurso($nuevoCurso);
        $this->entityManager->flush();
    }

    public function eliminarMatricula(int $matriculaId): void
    {
        $matricula = $this->matriculaRepo->find($matriculaId);
        if (!$matricula) {
            throw new NotFoundHttpException('Matrícula no encontrada');
        }

        $curso = $matricula->getCurso();
        $curso?->removeMatricula($matricula);
        $this->entityManager->remove($matricula);

        if ($curso !== null) {
            $this->actualizarEstadoCurso($curso, count($curso->getMatriculas()));
        }

        $this->entityManager->flush();
    }

    private function buscarCurso(int $cursoId): Curso
    {
        $curso = $this->cursoRepo->find($cursoId);
        if (!$curso) {
            throw new NotFoundHttpException('Curso no encontrado');
        }

        return $curso;
    }

    private function validarId(mixed $id, string $campo): int
    {
        $idValidado = filter_var($id, FILTER_VALIDATE_INT);
        if ($idValidado === false || $idValidado < 1) {
            throw new BadRequestHttpException(sprintf('El campo %s debe ser un entero válido', $campo));
        }

        return $idValidado;
    }

    private function validarCupoDisponible(Curso $curso): void
    {
        $matriculasActuales = count($curso->getMatriculas());
        if ($matriculasActuales >= $curso->getCupoMaximo()) {
            $this->actualizarEstadoCurso($curso, $matriculasActuales);
            $this->entityManager->flush();
            throw new BadRequestHttpException('El curso ha alcanzado su cupo máximo');
        }
    }

    private function actualizarEstadoCurso(Curso $curso, int $matriculasActuales): void
    {
        $curso->setEstado(
            $matriculasActuales >= $curso->getCupoMaximo() ? 'Lleno' : 'Disponible'
        );
    }
}
