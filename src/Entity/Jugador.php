<?php

namespace App\Entity;

use App\Repository\JugadorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: JugadorRepository::class)]
#[ORM\Table(name: 'jugadores')]
#[UniqueEntity(fields:["nombre","equipo"], message:"No puede haber un mismo jugador, en el mismo equipo")]
class Jugador
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    #[Assert\NotBlank(
        message: 'El nombre es obligatorio',
    )]
    #[Assert\Length(
        max:50,
        maxMessage: 'Máximo 50 caracteres para el nombre'
    )]
    #[ORM\Column(length: 50)]
    private ?string $nombre = null;
    #[Assert\NotBlank(
        message: 'El dorsal es obligatorio',
    )]
    #[Assert\Length(
        max:4,
        maxMessage: 'Máximo 4 dígitos para el dorsal'
    )]
    //OJO!! NO ME FUNCIONA CONTROLAR EL TIPO
    // #[Assert\Type(
    //     type: 'integer',
    //     message: 'The value {{ value }} is not a valid {{ type }}.',
    // )]     
    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $dorsal = null;

    #[ORM\ManyToOne(inversedBy: 'jugadoresequipos')]
    //#[ORM\JoinColumn(nullable: false)]
    #[ORM\JoinColumn(name:"equipo",referencedColumnName:"id",nullable: false)]
    private ?Equipo $equipo = null;

    #[ORM\OneToMany(mappedBy: 'jugador', targetEntity: Gol::class)]
    private Collection $golesjugador;

    public function __construct()
    {
        $this->golesjugador = new ArrayCollection();
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

    public function getDorsal(): ?int
    {
        return $this->dorsal;
    }

    public function setDorsal(int $dorsal): static
    {
        $this->dorsal = $dorsal;

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

    /**
     * @return Collection<int, Gol>
     */
    public function getGolesjugador(): Collection
    {
        return $this->golesjugador;
    }

    public function addGolesjugador(Gol $golesjugador): static
    {
        if (!$this->golesjugador->contains($golesjugador)) {
            $this->golesjugador->add($golesjugador);
            $golesjugador->setJugador($this);
        }

        return $this;
    }

    public function removeGolesjugador(Gol $golesjugador): static
    {
        if ($this->golesjugador->removeElement($golesjugador)) {
            // set the owning side to null (unless already changed)
            if ($golesjugador->getJugador() === $this) {
                $golesjugador->setJugador(null);
            }
        }

        return $this;
    }
    //add esta función se añade luego en el controller en ver.
    //la función toObject convierte un array en un objeto. stdClass es una clase estándar, que genera objeto con los datos que indiquemos
    public function toObject(): \stdClass{
        $obj = new \stdClass();
        $obj->id = $this->id;
        $obj->nombre = $this->nombre;
        $obj->dorsal =  $this->dorsal;

        return $obj;
    }    

}
