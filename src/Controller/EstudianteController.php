<?php

namespace App\Controller;

use App\Service\EstudianteService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/estudiantes')]
class EstudianteController extends AbstractController
{
    public function __construct(
        private EstudianteService $estudianteService,
        private LoggerInterface $logger
    ) {}

    #[Route('', methods: ['GET'])]
    public function listar(): JsonResponse
    {
        return $this->respond(fn (): JsonResponse => $this->json($this->estudianteService->listar()));
    }

    #[Route('', methods: ['POST'])]
    public function crear(Request $request): JsonResponse
    {
        return $this->respond(function () use ($request): JsonResponse {
            $data = $this->getInputData($request);
            $estudiante = $this->estudianteService->crear($data['nombre'] ?? null);

            return $this->json([
                'status' => 201,
                'message' => 'Estudiante creado con éxito',
                'id' => $estudiante->getId(),
                'nombre' => $estudiante->getNombre(),
            ], 201);
        });
    }

    #[Route('/{id}', methods: ['GET'])]
    public function obtenerPorId(int $id): JsonResponse
    {
        return $this->respond(function () use ($id): JsonResponse {
            $estudiante = $this->estudianteService->obtener($id);

            return $this->json([
                'id' => $estudiante->getId(),
                'nombre' => $estudiante->getNombre(),
            ]);
        });
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function actualizar(int $id, Request $request): JsonResponse
    {
        return $this->respond(function () use ($id, $request): JsonResponse {
            $data = $this->getInputData($request);
            $this->estudianteService->actualizar($id, $data['nombre'] ?? null);

            return $this->json(['message' => 'Estudiante actualizado con éxito']);
        });
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function eliminar(int $id): JsonResponse
    {
        return $this->respond(function () use ($id): JsonResponse {
            $this->estudianteService->eliminar($id);

            return $this->json(['message' => 'Estudiante eliminado con éxito']);
        });
    }

    private function getInputData(Request $request): array
    {
        if ($request->getContentTypeFormat() !== 'json'
            && ($request->request->count() > 0 || $request->getContent() === '')
        ) {
            return $request->request->all();
        }

        try {
            $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new BadRequestHttpException('El cuerpo de la solicitud debe ser JSON válido');
        }

        if (!is_array($data)) {
            throw new BadRequestHttpException('El cuerpo de la solicitud debe ser un objeto JSON');
        }

        return $data;
    }

    private function respond(callable $action): JsonResponse
    {
        try {
            return $action();
        } catch (HttpExceptionInterface $exception) {
            return $this->json(
                ['message' => $exception->getMessage()],
                $exception->getStatusCode(),
                $exception->getHeaders()
            );
        } catch (\Throwable $exception) {
            $this->logger->error('Error inesperado al procesar un estudiante', [
                'exception' => $exception,
            ]);

            return $this->json(['message' => 'Error interno del servidor'], 500);
        }
    }
}
