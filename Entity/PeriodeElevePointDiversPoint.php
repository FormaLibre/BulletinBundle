<?php

namespace FormaLibre\BulletinBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="FormaLibre\BulletinBundle\Repository\PeriodeElevePointDiversPointRepository")
 * @ORM\Table(name="formalibre_bulletin_periode_eleve_pointdivers_point")
 */
class PeriodeElevePointDiversPoint
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(
     *      targetEntity="FormaLibre\BulletinBundle\Entity\Periode",
     * )
     */
    private $periode;

    /**
     * @ORM\ManyToOne(
     *      targetEntity="FormaLibre\BulletinBundle\Entity\PointDivers",
     * )
     */
    private $divers;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User",
     *     cascade={"persist"}
     * )
     */
    private $eleve;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $total;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $point;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $position;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $comment;

    /**
     * @param mixed $divers
     */
    public function setDivers($divers)
    {
        $this->divers = $divers;
    }

    /**
     * @return mixed
     */
    public function getDivers()
    {
        return $this->divers;
    }

    /**
     * @param mixed $eleve
     */
    public function setEleve($eleve)
    {
        $this->eleve = $eleve;
    }

    /**
     * @return mixed
     */
    public function getEleve()
    {
        return $this->eleve;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $periode
     */
    public function setPeriode($periode)
    {
        $this->periode = $periode;
    }

    /**
     * @return mixed
     */
    public function getPeriode()
    {
        return $this->periode;
    }

    /**
     * @param mixed $point
     */
    public function setPoint($point)
    {
        $this->point = $point;
    }

    /**
     * @return mixed
     */
    public function getPoint()
    {
        return $this->point;
    }

    /**
     * @param mixed $total
     */
    public function setTotal($total)
    {
        $this->total = $total;
    }

    /**
     * @return mixed
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param mixed $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return mixed
     */
    public function getPosition()
    {
        return $this->position;
    }

    public function getComment()
    {
        return $this->comment;
    }

    public function setComment($comment)
    {
        $this->comment = $comment;
    }
}