<?php

namespace App\Entity;

use App\Repository\MatriculaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MatriculaRepository::class)]
class Matricula
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'matriculas')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Estudiante $estudiante = null;

    #[ORM\ManyToOne(inversedBy: 'matriculas')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Curso $curso = null;

    #[ORM\Column]
    private ?\DateTime $fechaMatricula = null;

    #[ORM\Column(length: 50)]
    private ?string $estado = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEstudiante(): ?Estudiante
    {
        return $this->estudiante;
    }

    public function setEstudiante(?Estudiante $estudiante): static
    {
        $this->estudiante = $estudiante;

        return $this;
    }

    public function getCurso(): ?Curso
    {
        return $this->curso;
    }

    public function setCurso(?Curso $curso): static
    {
        $this->curso = $curso;

        return $this;
    }

    public function getFechaMatricula(): ?\DateTime
    {
        return $this->fechaMatricula;
    }

    public function setFechaMatricula(\DateTime $fechaMatricula): static
    {
        $this->fechaMatricula = $fechaMatricula;

        return $this;
    }

    public function getEstado(): ?string
    {
        return $this->estado;
    }

    public function setEstado(string $estado): static
    {
        $this->estado = $estado;

        return $this;
    }
}
