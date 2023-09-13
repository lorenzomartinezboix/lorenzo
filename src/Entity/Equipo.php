<?php

namespace App\Entity;

use App\Repository\EquipoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EquipoRepository::class)]
#[ORM\Table(name: 'equipos')]
#[UniqueEntity(fields:["nombre"], message:"No puede haber un equipo duplicado")]
class Equipo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    #[Assert\NotBlank(
        message: 'El nombre del equipo es obligatorio',
    )]   
    // #[Assert\Type(
    //     type: ['alpha', 'digit'],
    //     message: 'El nombre del equipo ha de ser correcto',
    // )]     
    #[Assert\Length(
        max:50,
        maxMessage: 'Máximo 50 caracteres para el equipo'
    )]
    #[ORM\Column(length: 50)]
    private ?string $nombre = null;

    #[ORM\Column]
    private ?bool $activo = null;
    #[Assert\NotBlank(
        message: 'El entrenador es obligatorio',
    )]    
    // #[Assert\Type(
    //     type: ['alpha', 'digit'],
    //     message: 'El nombre del entrenador ha de ser correcto',
    // )] 
    #[Assert\Length(
        max:150,
        maxMessage: 'Máximo 150 caracteres para el equipo'
    )]
    #[ORM\Column(length: 150)]
    private ?string $entrenador = null;

    #[ORM\OneToMany(mappedBy: 'equipo', targetEntity: Participante::class)]
    private Collection $equiposparticipantes;

    #[ORM\OneToMany(mappedBy: 'local', targetEntity: Partido::class)]
    private Collection $equipolocal;

    #[ORM\OneToMany(mappedBy: 'visitante', targetEntity: Partido::class)]
    private Collection $equipovisitante;

    #[ORM\OneToMany(mappedBy: 'equipo', targetEntity: Jugador::class)]
    private Collection $jugadoresequipos;

    #[ORM\OneToMany(mappedBy: 'equipo', targetEntity: Gol::class)]
    private Collection $golesequipo;

    public function __construct()
    {
        $this->equiposparticipantes = new ArrayCollection();
        $this->equipolocal = new ArrayCollection();
        $this->equipovisitante = new ArrayCollection();
        $this->jugadoresequipos = new ArrayCollection();
        $this->golesequipo = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function isActivo(): ?bool
    {
        return $this->activo;
    }

    public function setActivo(bool $activo): static
    {
        $this->activo = $activo;

        return $this;
    }

    public function getEntrenador(): ?string
    {
        return $this->entrenador;
    }

    public function setEntrenador(string $entrenador): static
    {
        $this->entrenador = $entrenador;

        return $this;
    }

    /**
     * @return Collection<int, Participante>
     */
    public function getEquiposparticipantes(): Collection
    {
        return $this->equiposparticipantes;
    }

    public function addEquiposparticipante(Participante $equiposparticipante): static
    {
        if (!$this->equiposparticipantes->contains($equiposparticipante)) {
            $this->equiposparticipantes->add($equiposparticipante);
            $equiposparticipante->setEquipo($this);
        }

        return $this;
    }

    public function removeEquiposparticipante(Participante $equiposparticipante): static
    {
        if ($this->equiposparticipantes->removeElement($equiposparticipante)) {
            // set the owning side to null (unless already changed)
            if ($equiposparticipante->getEquipo() === $this) {
                $equiposparticipante->setEquipo(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Partido>
     */
    public function getEquipolocal(): Collection
    {
        return $this->equipolocal;
    }

    public function addEquipolocal(Partido $equipolocal): static
    {
        if (!$this->equipolocal->contains($equipolocal)) {
            $this->equipolocal->add($equipolocal);
            $equipolocal->setLocal($this);
        }

        return $this;
    }

    public function removeEquipolocal(Partido $equipolocal): static
    {
        if ($this->equipolocal->removeElement($equipolocal)) {
            // set the owning side to null (unless already changed)
            if ($equipolocal->getLocal() === $this) {
                $equipolocal->setLocal(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Partido>
     */
    public function getEquipovisitante(): Collection
    {
        return $this->equipovisitante;
    }

    public function addEquipovisitante(Partido $equipovisitante): static
    {
        if (!$this->equipovisitante->contains($equipovisitante)) {
            $this->equipovisitante->add($equipovisitante);
            $equipovisitante->setVisitante($this);
        }

        return $this;
    }

    public function removeEquipovisitante(Partido $equipovisitante): static
    {
        if ($this->equipovisitante->removeElement($equipovisitante)) {
            // set the owning side to null (unless already changed)
            if ($equipovisitante->getVisitante() === $this) {
                $equipovisitante->setVisitante(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Jugador>
     */
    public function getJugadoresequipos(): Collection
    {
        return $this->jugadoresequipos;
    }

    public function addJugadoresequipo(Jugador $jugadoresequipo): static
    {
        if (!$this->jugadoresequipos->contains($jugadoresequipo)) {
            $this->jugadoresequipos->add($jugadoresequipo);
            $jugadoresequipo->setEquipo($this);
        }

        return $this;
    }

    public function removeJugadoresequipo(Jugador $jugadoresequipo): static
    {
        if ($this->jugadoresequipos->removeElement($jugadoresequipo)) {
            // set the owning side to null (unless already changed)
            if ($jugadoresequipo->getEquipo() === $this) {
                $jugadoresequipo->setEquipo(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Gol>
     */
    public function getGolesequipo(): Collection
    {
        return $this->golesequipo;
    }

    public function addGolesequipo(Gol $golesequipo): static
    {
        if (!$this->golesequipo->contains($golesequipo)) {
            $this->golesequipo->add($golesequipo);
            $golesequipo->setEquipo($this);
        }

        return $this;
    }

    public function removeGolesequipo(Gol $golesequipo): static
    {
        if ($this->golesequipo->removeElement($golesequipo)) {
            // set the owning side to null (unless already changed)
            if ($golesequipo->getEquipo() === $this) {
                $golesequipo->setEquipo(null);
            }
        }

        return $this;
    }
}
