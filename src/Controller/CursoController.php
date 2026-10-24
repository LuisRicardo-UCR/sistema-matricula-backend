<?php
namespace App\Controller;

use App\Entity\Curso;
use App\Repository\CursoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/cursos')]
class CursoController extends AbstractController
{
    public function __construct(
        private CursoRepository $cursoRepo,
        private EntityManagerInterface $entityManager
    ) {}

    // 1. Listar todos los cursos (GET) - Para la pantalla principal de Angular
    #[Route('', methods: ['GET'])]
    public function listar(): JsonResponse
    {
        $cursos = $this->cursoRepo->findAll();

        $data = array_map(function($curso) {
            return [
                'id' => $curso->getId(),
                'codigo' => $curso->getCodigo(),
                'nombre' => $curso->getNombre(),
                'descripcion' => $curso->getDescripcion(),
                'creditos' => $curso->getCreditos(),
                'horario' => $curso->getHorario(),
                'grupo' => $curso->getGrupo(),
                'cupoMaximo' => $curso->getCupoMaximo(),
                'cuposActuales' => count($curso->getMatriculas()),
                'estado' => $curso->getEstado()
            ];
        }, $cursos);

        return $this->json($data);
    }

    // 2. Crear un curso nuevo (POST)
    #[Route('', methods: ['POST'])]
    public function crear(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $curso = new Curso();
        $curso->setCodigo($data['codigo'] ?? '');
        $curso->setNombre($data['nombre'] ?? '');
        $curso->setDescripcion($data['descripcion'] ?? null);
        $curso->setCreditos($data['creditos'] ?? 0);
        $curso->setFotografia($data['fotografia'] ?? null);
        $curso->setHorario($data['horario'] ?? '');
        $curso->setGrupo($data['grupo'] ?? '');
        $curso->setCupoMaximo($data['cupoMaximo'] ?? 0);
        $curso->setEstado($data['estado'] ?? 'Disponible');

        $this->entityManager->persist($curso);
        $this->entityManager->flush();

        return $this->json([
            'status' => 201,
            'message' => 'Curso creado con éxito',
            'id' => $curso->getId()
        ], 201);
    }

    // 3. Ver detalles / Estudiantes matriculados (GET) - Requerimiento del Modal
    #[Route('/{id}/estudiantes', methods: ['GET'])]
    public function estudiantesPorCurso(int $id): JsonResponse
    {
        $curso = $this->cursoRepo->find($id);
        if (!$curso) {
            return $this->json(['message' => 'Curso no encontrado'], 404);
        }

        $estudiantes = [];
        foreach ($curso->getMatriculas() as $matricula) {
            $estudiantes[] = [
                'id' => $matricula->getEstudiante()->getId(),
                'nombre' => $matricula->getEstudiante()->getNombre(),
                'fechaMatricula' => $matricula->getFechaMatricula()->format('Y-m-d H:i:s'),
                'estadoMatricula' => $matricula->getEstado()
            ];
        }

        return $this->json($estudiantes);
    }

    // 4. Actualizar un curso (PUT)
    #[Route('/{id}', methods: ['PUT'])]
    public function actualizar(int $id, Request $request): JsonResponse
    {
        $curso = $this->cursoRepo->find($id);
        if (!$curso) {
            return $this->json(['message' => 'Curso no encontrado'], 404);
        }

        $data = json_decode($request->getContent(), true);

        $curso->setCodigo($data['codigo'] ?? $curso->getCodigo());
        $curso->setNombre($data['nombre'] ?? $curso->getNombre());
        $curso->setDescripcion($data['descripcion'] ?? $curso->getDescripcion());
        $curso->setCreditos($data['creditos'] ?? $curso->getCreditos());
        $curso->setHorario($data['horario'] ?? $curso->getHorario());
        $curso->setGrupo($data['grupo'] ?? $curso->getGrupo());
        $curso->setCupoMaximo($data['cupoMaximo'] ?? $curso->getCupoMaximo());
        $curso->setEstado($data['estado'] ?? $curso->getEstado());

        $this->entityManager->flush();

        return $this->json(['message' => 'Curso actualizado con éxito']);
    }

    // 5. Eliminar un curso (DELETE)
    #[Route('/{id}', methods: ['DELETE'])]
    public function eliminar(int $id): JsonResponse
    {
        $curso = $this->cursoRepo->find($id);
        if (!$curso) {
            return $this->json(['message' => 'Curso no encontrado'], 404);
        }

        $this->entityManager->remove($curso);
        $this->entityManager->flush();

        return $this->json(['message' => 'Curso eliminado con éxito']);
    }
}
