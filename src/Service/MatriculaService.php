<?php

namespace App\Service;



use App\Entity\Matricula;

use App\Repository\CursoRepository;

use App\Repository\EstudianteRepository;

use Doctrine\ORM\EntityManagerInterface;



class MatriculaService

{

    public function __construct(

        private CursoRepository $cursoRepo,

        private EstudianteRepository $estudianteRepo,

        private EntityManagerInterface $entityManager

    ) {}



    public function registrarMatricula(int $estudianteId, int $cursoId): array

    {

        $estudiante = $this->estudianteRepo->find($estudianteId);

        $curso = $this->cursoRepo->find($cursoId);



        if (!$estudiante || !$curso) {

            return ['status' => 404, 'message' => 'Estudiante o curso no encontrado'];

        }



        // 1. Validar estado del curso

        if ($curso->getEstado() !== 'Disponible') {

            return ['status' => 400, 'message' => 'El curso no está disponible'];

        }



        // 2. Validar control de cupos (Regla de oro de la rúbrica)

        $matriculasActuales = count($curso->getMatriculas());

        if ($matriculasActuales >= $curso->getCupoMaximo()) {

            return ['status' => 400, 'message' => 'El curso ha alcanzado su cupo máximo'];

        }



        // 3. Crear y persistir la matrícula

        $matricula = new Matricula();

        $matricula->setEstudiante($estudiante);

        $matricula->setCurso($curso);

        $matricula->setFechaMatricula(new \DateTime());

        $matricula->setEstado('Activa');



        $this->entityManager->persist($matricula);

        $this->entityManager->flush();



        return ['status' => 201, 'message' => 'Matrícula registrada con éxito'];

    }

}

