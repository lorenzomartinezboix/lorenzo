<?php

namespace App\Entity;

use App\Repository\PartidoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: PartidoRepository::class)]
#[ORM\Table(name: 'partidos')]
#[UniqueEntity(fields:["fecha","liga","local","visitante"], message:"No puede haber un mismo partido, con misma fecha, liga, local, visitante")]
class Partido
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $fecha = null;

    #[ORM\ManyToOne(inversedBy: 'ligaspartidos')]
    //#[ORM\JoinColumn(nullable: false)]
    #[ORM\JoinColumn(name:"liga",referencedColumnName:"id",nullable: false)]
    private ?Liga $liga = null;

    #[ORM\ManyToOne(inversedBy: 'equipolocal')]
    //#[ORM\JoinColumn(nullable: false)]
    #[ORM\JoinColumn(name:"local",referencedColumnName:"id",nullable: false)]
    private ?Equipo $local = null;

    #[ORM\ManyToOne(inversedBy: 'equipovisitante')]
    //#[ORM\JoinColumn(nullable: false)]
    #[ORM\JoinColumn(name:"visitante",referencedColumnName:"id",nullable: false)]
    private ?Equipo $visitante = null;

    #[ORM\OneToMany(mappedBy: 'partido', targetEntity: Gol::class)]
    private Collection $golespartido;

    public function __construct()
    {
        $this->golespartido = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFecha(): ?\DateTimeInterface
    {
        return $this->fecha;
    }

    public function setFecha(\DateTimeInterface $fecha): static
    {
        $this->fecha = $fecha;

        return $this;
    }

    public function getLiga(): ?Liga
    {
        return $this->liga;
    }

    public function setLiga(?Liga $liga): static
    {
        $this->liga = $liga;

        return $this;
    }

    public function getLocal(): ?Equipo
    {
        return $this->local;
    }

    public function setLocal(?Equipo $local): static
    {
        $this->local = $local;

        return $this;
    }

    public function getVisitante(): ?Equipo
    {
        return $this->visitante;
    }

    public function setVisitante(?Equipo $visitante): static
    {
        $this->visitante = $visitante;

        return $this;
    }

    /**
     * @return Collection<int, Gol>
     */
    public function getGolespartido(): Collection
    {
        return $this->golespartido;
    }

    public function addGolespartido(Gol $golespartido): static
    {
        if (!$this->golespartido->contains($golespartido)) {
            $this->golespartido->add($golespartido);
            $golespartido->setPartido($this);
        }

        return $this;
    }

    public function removeGolespartido(Gol $golespartido): static
    {
        if ($this->golespartido->removeElement($golespartido)) {
            // set the owning side to null (unless already changed)
            if ($golespartido->getPartido() === $this) {
                $golespartido->setPartido(null);
            }
        }

        return $this;
    }
}
