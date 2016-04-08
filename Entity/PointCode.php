<?php

namespace FormaLibre\BulletinBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="FormaLibre\BulletinBundle\Repository\PointCodeRepository")
 * @ORM\Table(name="formalibre_bulletin_point_code")
 */
class PointCode
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", unique=true)
     * @Assert\NotBlank()
     */
    private $code;

    /**
     * @ORM\Column(name="info")
     * @Assert\NotBlank()
     */
    private $info;

    /**
     * @ORM\Column(name="short_info")
     * @Assert\NotBlank()
     */
    private $shortInfo;

    /**
     * @ORM\Column(name="is_default_value", type="boolean")
     */
    private $isDefaultValue = false;

    /**
     * @ORM\Column(name="ignored", type="boolean")
     */
    private $ignored = true;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
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
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return mixed
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * @param mixed $info
     */
    public function setInfo($info)
    {
        $this->info = $info;
    }

    /**
     * @return mixed
     */
    public function getShortInfo()
    {
        return $this->shortInfo;
    }

    /**
     * @param mixed $shortInfo
     */
    public function setShortInfo($shortInfo)
    {
        $this->shortInfo = $shortInfo;
    }

    /**
     * @return mixed
     */
    public function getIsDefaultValue()
    {
        return $this->isDefaultValue;
    }

    /**
     * @param mixed $isDefaultValue
     */
    public function setIsDefaultValue($isDefaultValue)
    {
        $this->isDefaultValue = $isDefaultValue;
    }

    /**
     * @return mixed
     */
    public function getIgnored()
    {
        return $this->ignored;
    }

    /**
     * @param mixed $ignored
     */
    public function setIgnored($ignored)
    {
        $this->ignored = $ignored;
    }
}