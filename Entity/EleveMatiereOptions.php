<?php

namespace FormaLibre\BulletinBundle\Entity;

use Claroline\CoreBundle\Entity\User;
use Claroline\CursusBundle\Entity\CourseSession;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Entity(repositoryClass="FormaLibre\BulletinBundle\Repository\EleveMatiereOptionsRepository")
 * @ORM\Table(
 *     name="formalibre_bulletin_eleve_matiere_options",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="bulletin_unique_eleve_matiere_option", columns={"matiere_id", "eleve_id"})
 *     }
 * )
 * @DoctrineAssert\UniqueEntity({"matiere", "eleve"})
 */
class EleveMatiereOptions
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="eleve_id", nullable=false, onDelete="CASCADE")
     */
    protected $eleve;

    /**
     * @ORM\ManyToOne(
     *      targetEntity="Claroline\CursusBundle\Entity\CourseSession"
     * )
     * @ORM\JoinColumn(name="matiere_id", nullable=false, onDelete="CASCADE")
     */
    protected $matiere;

    /**
     * @ORM\Column(name="deliberated", type="boolean")
     */
    protected $deliberated = false;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     */
    protected $options;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getEleve()
    {
        return $this->eleve;
    }

    public function setEleve(User $eleve)
    {
        $this->eleve = $eleve;
    }

    public function getMatiere()
    {
        return $this->matiere;
    }

    public function setMatiere(CourseSession $matiere)
    {
        $this->matiere = $matiere;
    }

    public function isDeliberated()
    {
        return $this->deliberated;
    }

    public function setDeliberated($deliberated)
    {
        $this->deliberated = $deliberated;
    }

    public function getOptions()
    {
        return $this->options;
    }
    public function setOptions($options)
    {
        $this->options = $options;
    }
}