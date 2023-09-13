<?php

namespace App\Entity;

use App\Repository\GolRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GolRepository::class)]
//añadimos Table
#[ORM\Table(name: 'goles')]
class Gol
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $minuto = null;

    #[ORM\ManyToOne(inversedBy: 'golesjugador')]
    //comentamos esta y añadimos la siguientes cuando es id el referencedColum, creo que no es necesario ponerlo, quedaría #[ORM\JoinColumn(name:"jugador",nullable: false)]
    //ejemplo si no tiene id: #[ORM\JoinColumn(name:"codigo_postal",referencedColumnName:"codigo",nullable: false)]
    //#[ORM\JoinColumn(nullable: false)]
    #[ORM\JoinColumn(name:"jugador",referencedColumnName:"id",nullable: false)]
    private ?Jugador $jugador = null;

    #[ORM\ManyToOne(inversedBy: 'golesequipo')]
    //#[ORM\JoinColumn(nullable: false)]
    #[ORM\JoinColumn(name:"equipo",referencedColumnName:"id",nullable: false)]
    private ?Equipo $equipo = null;

    #[ORM\ManyToOne(inversedBy: 'golespartido')]
    //#[ORM\JoinColumn(nullable: false)]
    //#[ORM\JoinColumn(name:"partido",nullable: false)]   
    #[ORM\JoinColumn(name:"partido",referencedColumnName:"id",nullable: false)]
    private ?Partido $partido = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMinuto(): ?int
    {
        return $this->minuto;
    }

    public function setMinuto(int $minuto): static
    {
        $this->minuto = $minuto;

        return $this;
    }

    public function getJugador(): ?Jugador
    {
        return $this->jugador;
    }

    public function setJugador(?Jugador $jugador): static
    {
        $this->jugador = $jugador;

        return $this;
    }

    public function getEquipo(): ?Equipo
    {
        return $this->equipo;
    }

    public function setEquipo(?Equipo $equipo): static
    {
        $this->equipo = $equipo;

        return $this;
    }

    public function getPartido(): ?Partido
    {
        return $this->partido;
    }

    public function setPartido(?Partido $partido): static
    {
        $this->partido = $partido;

        return $this;
    }
}
