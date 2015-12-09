<?php

namespace FormaLibre\BulletinBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="FormaLibre\BulletinBundle\Repository\LockStatusRepository")
 * @ORM\Table(name="formalibre_bulletin_lock_status")
 */
class LockStatus
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User",
     *     cascade={"persist"}
     * )
     */
    private $teacher;

    /**
     * @ORM\ManyToOne(
     *      targetEntity="FormaLibre\BulletinBundle\Entity\Periode"
     * )
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $periode;

    /**
     * @ORM\ManyToOne(
     *      targetEntity="Claroline\CursusBundle\Entity\CourseSession"
     * )
     */
    private $matiere;
    
     /**
     * @ORM\Column(type="boolean")
     */
    protected $lockStatus = false;
    
    function getId() {
        return $this->id;
    }

    function getPeriode() {
        return $this->periode;
    }

    function getMatiere() {
        return $this->matiere;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setPeriode($periode) {
        $this->periode = $periode;
    }

    function setMatiere($matiere) {
        $this->matiere = $matiere;
    }

    function getTeacher() {
        return $this->teacher;
    }

    function setTeacher($teacher) {
        $this->teacher = $teacher;
    }

    function getLockStatus() {
        return $this->lockStatus;
    }

    function setLockStatus($lockStatus) {
        $this->lockStatus = $lockStatus;
    }




}
