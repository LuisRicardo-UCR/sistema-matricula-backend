<?php

namespace App\Controller;

use App\Service\CursoService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/cursos')]
class CursoController extends AbstractController
{
    public function __construct(
        private CursoService $cursoService,
        private LoggerInterface $logger
    ) {}

    #[Route('', methods: ['GET'])]
    public function listar(): JsonResponse
    {
        return $this->respond(fn (): JsonResponse => $this->json($this->cursoService->listar()));
    }

    #[Route('', methods: ['POST'])]
    public function crear(Request $request): JsonResponse
    {
        return $this->respond(function () use ($request): JsonResponse {
            $curso = $this->cursoService->crear(
                $this->getInputData($request),
                $this->getFotografia($request)
            );

            return $this->json([
                'status' => 201,
                'message' => 'Curso creado con éxito',
                'id' => $curso->getId(),
                'fotografia' => $curso->getFotografia(),
            ], 201);
        });
    }

    #[Route('/{id}/estudiantes', methods: ['GET'])]
    public function estudiantesPorCurso(int $id): JsonResponse
    {
        return $this->respond(
            fn (): JsonResponse => $this->json($this->cursoService->estudiantesPorCurso($id))
        );
    }

    #[Route('/{id}', methods: ['PUT', 'POST'])]
    public function actualizar(int $id, Request $request): JsonResponse
    {
        return $this->respond(function () use ($id, $request): JsonResponse {
            $curso = $this->cursoService->actualizar(
                $id,
                $this->getInputData($request),
                $this->getFotografia($request)
            );

            return $this->json([
                'message' => 'Curso actualizado con éxito',
                'fotografia' => $curso->getFotografia(),
            ]);
        });
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function eliminar(int $id): JsonResponse
    {
        return $this->respond(function () use ($id): JsonResponse {
            $this->cursoService->eliminar($id);

            return $this->json(['message' => 'Curso eliminado con éxito']);
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

    private function getFotografia(Request $request): ?UploadedFile
    {
        $fotografia = $request->files->get('fotografia');
        if ($fotografia !== null && !$fotografia instanceof UploadedFile) {
            throw new BadRequestHttpException('La fotografía enviada no es válida');
        }

        return $fotografia;
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
            $this->logger->error('Error inesperado al procesar un curso', [
                'exception' => $exception,
            ]);

            return $this->json(['message' => 'Error interno del servidor'], 500);
        }
    }
}
