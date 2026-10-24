<?php

namespace App\Entity;

use App\Repository\CursoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CursoRepository::class)]
class Curso
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $codigo = null;

    #[ORM\Column(length: 150)]
    private ?string $nombre = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $descripcion = null;

    #[ORM\Column]
    private ?int $creditos = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fotografia = null;

    #[ORM\Column(length: 100)]
    private ?string $horario = null;

    #[ORM\Column(length: 20)]
    private ?string $grupo = null;

    #[ORM\Column]
    private ?int $cupoMaximo = null;

    #[ORM\Column(length: 50)]
    private ?string $estado = null;

    /**
     * @var Collection<int, Matricula>
     */
    #[ORM\OneToMany(targetEntity: Matricula::class, mappedBy: 'curso')]
    private Collection $matriculas;

    public function __construct()
    {
        $this->matriculas = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodigo(): ?string
    {
        return $this->codigo;
    }

    public function setCodigo(string $codigo): static
    {
        $this->codigo = $codigo;

        return $this;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(?string $descripcion): static
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getCreditos(): ?int
    {
        return $this->creditos;
    }

    public function setCreditos(int $creditos): static
    {
        $this->creditos = $creditos;

        return $this;
    }

    public function getFotografia(): ?string
    {
        return $this->fotografia;
    }

    public function setFotografia(?string $fotografia): static
    {
        $this->fotografia = $fotografia;

        return $this;
    }

    public function getHorario(): ?string
    {
        return $this->horario;
    }

    public function setHorario(string $horario): static
    {
        $this->horario = $horario;

        return $this;
    }

    public function getGrupo(): ?string
    {
        return $this->grupo;
    }

    public function setGrupo(string $grupo): static
    {
        $this->grupo = $grupo;

        return $this;
    }

    public function getCupoMaximo(): ?int
    {
        return $this->cupoMaximo;
    }

    public function setCupoMaximo(int $cupoMaximo): static
    {
        $this->cupoMaximo = $cupoMaximo;

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

    /**
     * @return Collection<int, Matricula>
     */
    public function getMatriculas(): Collection
    {
        return $this->matriculas;
    }

    public function addMatricula(Matricula $matricula): static
    {
        if (!$this->matriculas->contains($matricula)) {
            $this->matriculas->add($matricula);
            $matricula->setCurso($this);
        }

        return $this;
    }

    public function removeMatricula(Matricula $matricula): static
    {
        if ($this->matriculas->removeElement($matricula)) {
            // set the owning side to null (unless already changed)
            if ($matricula->getCurso() === $this) {
                $matricula->setCurso(null);
            }
        }

        return $this;
    }
}
