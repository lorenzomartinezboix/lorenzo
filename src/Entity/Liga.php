<?php

namespace App\Entity;

use App\Repository\LigaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LigaRepository::class)]
#[ORM\Table(name: 'ligas')]
#[UniqueEntity(fields:["temporada"], message:"Liga duplicada")]
class Liga
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    #[Assert\NotBlank(
        message: 'La temporada es obligatoria',
    )]
    #[Assert\Length(
        max:50,
        maxMessage: 'Máximo 50 caracteres para la temporada'
    )]
    #[ORM\Column(length: 50)]
    private ?string $temporada = null;

    #[ORM\OneToMany(mappedBy: 'liga', targetEntity: Participante::class)]
    private Collection $ligasparticipantes;

    #[ORM\OneToMany(mappedBy: 'liga', targetEntity: Partido::class)]
    private Collection $ligaspartidos;

    public function __construct()
    {
        $this->ligasparticipantes = new ArrayCollection();
        $this->ligaspartidos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTemporada(): ?string
    {
        return $this->temporada;
    }

    public function setTemporada(string $temporada): static
    {
        $this->temporada = $temporada;

        return $this;
    }

    /**
     * @return Collection<int, Participante>
     */
    public function getLigasparticipantes(): Collection
    {
        return $this->ligasparticipantes;
    }

    public function addLigasparticipante(Participante $ligasparticipante): static
    {
        if (!$this->ligasparticipantes->contains($ligasparticipante)) {
            $this->ligasparticipantes->add($ligasparticipante);
            $ligasparticipante->setLiga($this);
        }

        return $this;
    }

    public function removeLigasparticipante(Participante $ligasparticipante): static
    {
        if ($this->ligasparticipantes->removeElement($ligasparticipante)) {
            // set the owning side to null (unless already changed)
            if ($ligasparticipante->getLiga() === $this) {
                $ligasparticipante->setLiga(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Partido>
     */
    public function getLigaspartidos(): Collection
    {
        return $this->ligaspartidos;
    }

    public function addLigaspartido(Partido $ligaspartido): static
    {
        if (!$this->ligaspartidos->contains($ligaspartido)) {
            $this->ligaspartidos->add($ligaspartido);
            $ligaspartido->setLiga($this);
        }

        return $this;
    }

    public function removeLigaspartido(Partido $ligaspartido): static
    {
        if ($this->ligaspartidos->removeElement($ligaspartido)) {
            // set the owning side to null (unless already changed)
            if ($ligaspartido->getLiga() === $this) {
                $ligaspartido->setLiga(null);
            }
        }

        return $this;
    }
}
