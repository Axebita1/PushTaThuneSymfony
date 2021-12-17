<?php

namespace App\Entity;

use App\Repository\MontantRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MontantRepository::class)
 */
class Montant
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="float")
     */
    private $montant;

    /**
     * @ORM\Column(type="integer")
     */
    private $idParticipant;

    /**
     * @ORM\Column(type="integer")
     */
    private $idSoiree;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): self
    {
        $this->montant = $montant;

        return $this;
    }

    public function getIdParticipant(): ?int
    {
        return $this->idParticipant;
    }

    public function setIdParticipant(int $idParticipant): self
    {
        $this->idParticipant = $idParticipant;

        return $this;
    }

    public function getIdSoiree(): ?int
    {
        return $this->idSoiree;
    }

    public function setIdSoiree(int $idSoiree): self
    {
        $this->idSoiree = $idSoiree;

        return $this;
    }
}
