<?php

namespace FormaLibre\BulletinBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="formalibre_bulletin_pointDivers")
 */
class PointDivers{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column()
     */
    private $name;

    /**
     * @ORM\Column()
     */
    private $officialName;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $withTotal;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $total;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $position;

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
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $officialName
     */
    public function setOfficialName($officialName)
    {
        $this->officialName = $officialName;
    }

    /**
     * @return mixed
     */
    public function getOfficialName()
    {
        return $this->officialName;
    }

    /**
     * @param mixed $withTotal
     */
    public function setWithTotal($withTotal)
    {
        $this->withTotal = $withTotal;
    }

    /**
     * @return mixed
     */
    public function getWithTotal()
    {
        return $this->withTotal;
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

    public function __toString()
    {
        return (string) $this->getOfficialName();
    }
}