<?php
namespace App\Controller;

use App\Entity\Estudiante;
use App\Repository\EstudianteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/estudiantes')]
class EstudianteController extends AbstractController
{
    public function __construct(
        private EstudianteRepository $estudianteRepo,
        private EntityManagerInterface $entityManager
    ) {}

    // 1. LISTAR TODOS LOS ESTUDIANTES (GET) - Vital para los desplegables (selects) de Angular
    #[Route('', methods: ['GET'])]
    public function listar(): JsonResponse
    {
        $estudiantes = $this->estudianteRepo->findAll();

        $data = array_map(function($estudiante) {
            return [
                'id' => $estudiante->getId(),
                'nombre' => $estudiante->getNombre()
            ];
        }, $estudiantes);

        return $this->json($data);
    }

    // 2. CREAR UN ESTUDIANTE NUEVO (POST)
    #[Route('', methods: ['POST'])]
    public function crear(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['nombre'])) {
            return $this->json(['message' => 'El nombre del estudiante es obligatorio'], 400);
        }

        $estudiante = new Estudiante();
        $estudiante->setNombre($data['nombre']);

        $this->entityManager->persist($estudiante);
        $this->entityManager->flush();

        return $this->json([
            'status' => 201,
            'message' => 'Estudiante creado con éxito',
            'id' => $estudiante->getId(),
            'nombre' => $estudiante->getNombre()
        ], 201);
    }

    // 3. OBTENER UN ESTUDIANTE POR ID (GET)
    #[Route('/{id}', methods: ['GET'])]
    public function obtenerPorId(int $id): JsonResponse
    {
        $estudiante = $this->estudianteRepo->find($id);

        if (!$estudiante) {
            return $this->json(['message' => 'Estudiante no encontrado'], 404);
        }

        return $this->json([
            'id' => $estudiante->getId(),
            'nombre' => $estudiante->getNombre()
        ]);
    }

    // 4. ACTUALIZAR UN ESTUDIANTE (PUT)
    #[Route('/{id}', methods: ['PUT'])]
    public function actualizar(int $id, Request $request): JsonResponse
    {
        $estudiante = $this->estudianteRepo->find($id);

        if (!$estudiante) {
            return $this->json(['message' => 'Estudiante no encontrado'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!empty($data['nombre'])) {
            $estudiante->setNombre($data['nombre']);
        }

        $this->entityManager->flush();

        return $this->json(['message' => 'Estudiante actualizado con éxito']);
    }

    // 5. ELIMINAR UN ESTUDIANTE (DELETE)
    #[Route('/{id}', methods: ['DELETE'])]
    public function eliminar(int $id): JsonResponse
    {
        $estudiante = $this->estudianteRepo->find($id);

        if (!$estudiante) {
            return $this->json(['message' => 'Estudiante no encontrado'], 404);
        }

        $this->entityManager->remove($estudiante);
        $this->entityManager->flush();

        return $this->json(['message' => 'Estudiante eliminado con éxito']);
    }
}
