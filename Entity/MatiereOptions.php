<?php

namespace FormaLibre\BulletinBundle\Entity;

use Claroline\CursusBundle\Entity\CourseSession;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;

/**
 * @ORM\Entity(repositoryClass="FormaLibre\BulletinBundle\Repository\MatiereOptionsRepository")
 * @ORM\Table(name="formalibre_bulletin_matiere_options")
 */
class MatiereOptions
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api_bulletin"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(
     *      targetEntity="Claroline\CursusBundle\Entity\CourseSession"
     * )
     * @Groups({"api_bulletin"})
     * @SerializedName("course_session")
     */
    private $matiere;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"api_bulletin"})
     */
    private $total;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"api_bulletin"})
     */
    private $position;

    /**
     * @ORM\Column(name="color", nullable=true)
     */
    protected $color;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getMatiere()
    {
        return $this->getCourseSession();
    }

    public function getCourseSession()
    {
        return $this->matiere;
    }

    public function setMatiere(CourseSession $matiere)
    {
        $this->setCourseSession($matiere);
    }

    public function setCourseSession(CourseSession $matiere)
    {
        $this->matiere = $matiere;
    }

    public function getTotal()
    {
        return $this->total;
    }

    public function setTotal($total)
    {
        $this->total = $total;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function setPosition($position)
    {
        $this->position = $position;
    }

    public function getColor()
    {
        return $this->color;
    }

    public function setColor($color)
    {
        $this->color = $color;
    }
}
