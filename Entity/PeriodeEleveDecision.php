<?php

namespace FormaLibre\BulletinBundle\Entity;

use Claroline\CoreBundle\Entity\User;
use Claroline\CursusBundle\Entity\CourseSession;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use FormaLibre\BulletinBundle\Entity\Decision;
use FormaLibre\BulletinBundle\Entity\Periode;

/**
 * @ORM\Entity(repositoryClass="FormaLibre\BulletinBundle\Repository\PeriodeEleveDecisionRepository")
 * @ORM\Table(name="formalibre_bulletin_periode_eleve_decision")
 */
class PeriodeEleveDecision
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="FormaLibre\BulletinBundle\Entity\Periode"
     * )
     * @ORM\JoinColumn(name="periode_id", nullable=false, onDelete="CASCADE")
     */
    private $periode;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     * @ORM\JoinColumn(name="user_id", nullable=false, onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="FormaLibre\BulletinBundle\Entity\Decision"
     * )
     * @ORM\JoinColumn(name="decision_id", nullable=false, onDelete="CASCADE")
     */
    private $decision;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CursusBundle\Entity\CourseSession"
     * )
     * @ORM\JoinTable(name="formalibre_bulletin_periode_eleve_decision_matieres")
     */
    private $matieres;

    public function __construct()
    {
        $this->matieres = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getPeriode()
    {
        return $this->periode;
    }

    public function setPeriode(Periode $periode)
    {
        $this->periode = $periode;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getDecision()
    {
        return $this->decision;
    }

    public function setDecision(Decision $decision)
    {
        $this->decision = $decision;
    }

    public function getMatieres()
    {
        return $this->matieres->toArray();
    }

    public function addMatiere(CourseSession $matiere)
    {
        if (!$this->matieres->contains($matiere)) {
            $this->matieres->add($matiere);
        }

        return $this;
    }

    public function removeMatiere(CourseSession $matiere)
    {
        if ($this->matieres->contains($matiere)) {
            $this->matieres->removeElement($matiere);
        }

        return $this;
    }
}
