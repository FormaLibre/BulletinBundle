<?php

namespace FormaLibre\BulletinBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity(repositoryClass="FormaLibre\BulletinBundle\Repository\PeriodeEleveMatierePointRepository")
 * @ORM\Table(name="formalibre_bulletin_periode_eleve_matiere_point")
 */
class PeriodeEleveMatierePoint
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

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
     * @ORM\Column(type="float", nullable=true)
     */
    private $comportement;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $presence;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $position;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $comment;
    
    protected $isLocked = true;
   

    /**
     * @param mixed $comportement
     */
    public function setComportement($comportement)
    {
        $this->comportement = $comportement;
    }

    /**
     * @return mixed
     */
    public function getComportement()
    {
        return $this->comportement;
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
     * @param mixed $matiere
     */
    public function setMatiere($matiere)
    {
        $this->matiere = $matiere;
    }

    /**
     * @return mixed
     */
    public function getMatiere()
    {
        return $this->matiere;
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
     * @param mixed $presence
     */
    public function setPresence($presence)
    {
        $this->presence = $presence;
    }

    /**
     * @return mixed
     */
    public function getPresence()
    {
        return $this->presence;
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

    public function getPourcentage()
    {
        if ($this->point < 850){
            $pourcentage = empty($this->total) ? 0 : ($this->point / $this->total) * 100;
        }
        else {
            $pourcentage = 999;
        }

        return $pourcentage;
    }

    public function getDisplayPourcentage(){
        if ($this->point == 999){
            $pourcentage = 'NE';
        }
        elseif ($this->point == 888){
            $pourcentage = 'CM';
        }
        elseif ($this->point == 900){
            $pourcentage = 'PNM';
        }
        else {
            $pourcentage = empty($this->total) ? 0 : round(($this->point / $this->total) * 100, 1).' %';
        }
        return $pourcentage;
    }

    public function getComment()
    {
        return $this->comment;
    }

    public function setComment($comment)
    {
        $this->comment = $comment;
    }
    
    public function setLocked($bool)
     {       
        $this->isLocked = $bool;
     }
    public function isLocked() 
      {      
     
        return $this->isLocked;
      }
}