<?php
namespace App\Controller;

use App\Repository\MatriculaRepository;
use App\Service\MatriculaService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/matriculas')]
class MatriculaController extends AbstractController
{
    public function __construct(
        private MatriculaService $matriculaService,
        private MatriculaRepository $matriculaRepo,
        private EntityManagerInterface $entityManager
    ) {}

    // 1. REGISTRAR UNA MATRÍCULA (POST) - Utiliza el Servicio con control de cupos
    #[Route('', methods: ['POST'])]
    public function registrar(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $estudianteId = $data['estudianteId'] ?? null;
        $cursoId = $data['cursoId'] ?? null;

        if (!$estudianteId || !$cursoId) {
            return $this->json([
                'status' => 400,
                'message' => 'Faltan datos obligatorios: estudianteId y cursoId'
            ], 400);
        }

        // Llamamos al servicio que valida cupos y estado del curso
        $resultado = $this->matriculaService->registrarMatricula($estudianteId, $cursoId);

        return $this->json($resultado, $resultado['status']);
    }

    // 2. LISTAR TODAS LAS MATRÍCULAS (GET)
    #[Route('', methods: ['GET'])]
    public function listar(): JsonResponse
    {
        $matriculas = $this->matriculaRepo->findAll();

        $data = array_map(function($matricula) {
            return [
                'id' => $matricula->getId(),
                'estudiante' => [
                    'id' => $matricula->getEstudiante()->getId(),
                    'nombre' => $matricula->getEstudiante()->getNombre()
                ],
                'curso' => [
                    'id' => $matricula->getCurso()->getId(),
                    'codigo' => $matricula->getCurso()->getCodigo(),
                    'nombre' => $matricula->getCurso()->getNombre()
                ],
                'fechaMatricula' => $matricula->getFechaMatricula()->format('Y-m-d H:i:s'),
                'estado' => $matricula->getEstado()
            ];
        }, $matriculas);

        return $this->json($data);
    }

    // 3. ELIMINAR / RETIRAR UNA MATRÍCULA (DELETE)
    #[Route('/{id}', methods: ['DELETE'])]
    public function eliminar(int $id): JsonResponse
    {
        $matricula = $this->matriculaRepo->find($id);

        if (!$matricula) {
            return $this->json(['message' => 'Matrícula no encontrada'], 404);
        }

        $this->entityManager->remove($matricula);
        $this->entityManager->flush();

        return $this->json(['message' => 'Matrícula eliminada con éxito']);
    }
}
