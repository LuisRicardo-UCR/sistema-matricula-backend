<?php
namespace App\Controller;

use App\Entity\Curso;
use App\Repository\CursoRepository;
use App\Service\FileUploader; // 1. Importar tu servicio de archivos
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/cursos')]
class CursoController extends AbstractController
{
    // 2. Inyectar FileUploader en el constructor
    public function __construct(
        private CursoRepository $cursoRepo,
        private EntityManagerInterface $entityManager,
        private FileUploader $fileUploader
    ) {}

    // 1. Listar todos los cursos (GET) - Sin cambios
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
                'fotografia' => $curso->getFotografia(), // Incluir la ruta en la respuesta
                'horario' => $curso->getHorario(),
                'grupo' => $curso->getGrupo(),
                'cupoMaximo' => $curso->getCupoMaximo(),
                'cuposActuales' => count($curso->getMatriculas()),
                'estado' => $curso->getEstado()
            ];
        }, $cursos);

        return $this->json($data);
    }

    // 2. Crear un curso nuevo con Fotografía (POST vía form-data)
    #[Route('', methods: ['POST'])]
    public function crear(Request $request): JsonResponse
    {
        // 3. Leer los campos de texto usando $request->request (en lugar de json_decode)
        $curso = new Curso();
        $curso->setCodigo($request->request->get('codigo', ''));
        $curso->setNombre($request->request->get('nombre', ''));
        $curso->setDescripcion($request->request->get('descripcion'));
        $curso->setCreditos((int) $request->request->get('creditos', 0));
        $curso->setHorario($request->request->get('horario', ''));
        $curso->setGrupo($request->request->get('grupo', ''));
        $curso->setCupoMaximo((int) $request->request->get('cupoMaximo', 0));
        $curso->setEstado($request->request->get('estado', 'Disponible'));

        // 4. Capturar y procesar el archivo de imagen usando $request->files y tu Service
        $fotografiaFile = $request->files->get('fotografia');
        if ($fotografiaFile) {
            try {
                $fileName = $this->fileUploader->upload($fotografiaFile);
                // Guardar la ruta relativa que se usará en el frontend
                $curso->setFotografia('uploads/cursos/' . $fileName);
            } catch (\Exception $e) {
                return $this->json(['error' => 'Error al subir la fotografía: ' . $e->getMessage()], 500);
            }
        }

        $this->entityManager->persist($curso);
        $this->entityManager->flush();

        return $this->json([
            'status' => 201,
            'message' => 'Curso creado con éxito',
            'id' => $curso->getId(),
            'fotografia' => $curso->getFotografia()
        ], 201);
    }

    // 3. Ver detalles / Estudiantes matriculados (GET) - Sin cambios
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

    // 4. Actualizar un curso (PUT o POST con form-data si se actualiza imagen)
    #[Route('/{id}', methods: ['PUT', 'POST'])]
    public function actualizar(int $id, Request $request): JsonResponse
    {
        $curso = $this->cursoRepo->find($id);
        if (!$curso) {
            return $this->json(['message' => 'Curso no encontrado'], 404);
        }

        // Si mandan form-data o JSON, adaptamos según corresponda
        $codigo = $request->request->get('codigo') ?? json_decode($request->getContent(), true)['codigo'] ?? null;
        if ($codigo) $curso->setCodigo($codigo);

        $nombre = $request->request->get('nombre') ?? json_decode($request->getContent(), true)['nombre'] ?? null;
        if ($nombre) $curso->setNombre($nombre);

        $descripcion = $request->request->get('descripcion') ?? json_decode($request->getContent(), true)['descripcion'] ?? null;
        if ($descripcion !== null) $curso->setDescripcion($descripcion);

        $creditos = $request->request->get('creditos') ?? json_decode($request->getContent(), true)['creditos'] ?? null;
        if ($creditos !== null) $curso->setCreditos((int)$creditos);

        $horario = $request->request->get('horario') ?? json_decode($request->getContent(), true)['horario'] ?? null;
        if ($horario) $curso->setHorario($horario);

        $grupo = $request->request->get('grupo') ?? json_decode($request->getContent(), true)['grupo'] ?? null;
        if ($grupo) $curso->setGrupo($grupo);

        $cupoMaximo = $request->request->get('cupoMaximo') ?? json_decode($request->getContent(), true)['cupoMaximo'] ?? null;
        if ($cupoMaximo !== null) $curso->setCupoMaximo((int)$cupoMaximo);

        $estado = $request->request->get('estado') ?? json_decode($request->getContent(), true)['estado'] ?? null;
        if ($estado) $curso->setEstado($estado);

        // Procesar nueva fotografía si se adjunta en la actualización
        $fotografiaFile = $request->files->get('fotografia');
        if ($fotografiaFile) {
            try {
                $fileName = $this->fileUploader->upload($fotografiaFile);
                $curso->setFotografia('uploads/cursos/' . $fileName);
            } catch (\Exception $e) {
                return $this->json(['error' => 'Error al actualizar la fotografía'], 500);
            }
        }

        $this->entityManager->flush();

        return $this->json([
            'message' => 'Curso actualizado con éxito',
            'fotografia' => $curso->getFotografia()
        ]);
    }

    // 5. Eliminar un curso (DELETE) - Sin cambios
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
