<?php

namespace FormaLibre\BulletinBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="formalibre_bulletin_periodes_group")
 */
class PeriodesGroup
{
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
    
    function getId() {
        return $this->id;
    }

    function getName() {
        return $this->name;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setName($name) {
        $this->name = $name;
    }


}
