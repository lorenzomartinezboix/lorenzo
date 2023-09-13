<?php

namespace App\Entity;

use App\Repository\ParticipanteRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: ParticipanteRepository::class)]
#[ORM\Table(name: 'participantes')]
#[UniqueEntity(fields:["equipo","liga"], message:"No puede haber un mismo equipo, en la misma liga duplicado")]
class Participante
{
   //en participantes no hay id, son los campos equipo y liga
   // #[ORM\Id]
   // #[ORM\GeneratedValue]
   // #[ORM\Column]
   // private ?int $id = null;
    //Ahora equipo es clave
    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'equiposparticipantes')]
    //#[ORM\JoinColumn(nullable: false)]
    #[ORM\JoinColumn(name:"equipo",referencedColumnName:"id",nullable: false)]
    private ?Equipo $equipo = null;
    //Ahora liga también es clave
    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'ligasparticipantes')]
    //#[ORM\JoinColumn(nullable: false)]
    #[ORM\JoinColumn(name:"liga",referencedColumnName:"id",nullable: false)]
    private ?Liga $liga = null;

    //public function getId(): ?int
    //{
    //    return $this->id;
    //}

    public function getEquipo(): ?Equipo
    {
        return $this->equipo;
    }

    public function setEquipo(?Equipo $equipo): static
    {
        $this->equipo = $equipo;

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
}
