<?php

namespace FormaLibre\BulletinBundle\Entity;

use Claroline\CursusBundle\Entity\CourseSession;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="FormaLibre\BulletinBundle\Repository\PeriodeRepository")
 * @ORM\Table(name="formalibre_bulletin_periode")
 */
class Periode
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

    /**
     * @ORM\Column(name="start_date", type="datetime", nullable=true)
     */
    private $start;

    /**
     * @ORM\Column(name="end_date", type="datetime", nullable=true)
     */
    private $end;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $degre;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $annee;

    /**
     * @ORM\Column()
     */
    private $ReunionParent;

    /**
     * @ORM\Column()
     */
    private $template;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $onlyPoint;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CursusBundle\Entity\CourseSession"
     * )
     * @ORM\JoinTable(name="formalibre_bulletin_periode_matieres")
     */
    protected $matieres;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="FormaLibre\BulletinBundle\Entity\PointDivers"
     * )
     * @ORM\JoinTable(name="formalibre_bulletin_periode_point_divers")
     */
    protected $pointDivers;

    /**
     * @ORM\Column(type="float", nullable=false)
     */
    private $coefficient = 1;
    
    /**
     * @ORM\ManyToOne(
     *     targetEntity="FormaLibre\BulletinBundle\Entity\PeriodesGroup"
     * )
     * @ORM\JoinTable(name="formalibre_bulletin_periodes_group")
     */
    private $periodesGroup = 0;
    
      /**
     * @ORM\ManyToOne(
     *     targetEntity="FormaLibre\BulletinBundle\Entity\Periode"
     * )
     * @ORM\JoinTable(name="oldPeriode1")
     */
    protected $oldPeriode1;
    
     /**
     * @ORM\ManyToOne(
     *     targetEntity="FormaLibre\BulletinBundle\Entity\Periode"
     * )
     * @ORM\JoinTable(name="oldPeriode2")
     */
    protected $oldPeriode2;
    
     /**
     * @ORM\ManyToOne(
     *     targetEntity="FormaLibre\BulletinBundle\Entity\Periode"
     * )
     * @ORM\JoinTable(name="oldPeriode3")
     */
    protected $oldPeriode3;
    
     /**
     * @ORM\ManyToOne(
     *     targetEntity="FormaLibre\BulletinBundle\Entity\Periode"
     * )
     * @ORM\JoinTable(name="oldPeriode4")
     */
    protected $oldPeriode4;
    
     /**
     * @ORM\ManyToOne(
     *     targetEntity="FormaLibre\BulletinBundle\Entity\Periode"
     * )
     * @ORM\JoinTable(name="oldPeriode5")
     */
    protected $oldPeriode5;

    /**
     * @ORM\Column(name="locked", type="boolean", options={"default" = 0})
     */
    private $locked = false;

    /**
     * @ORM\Column(name="published", type="boolean", options={"default" = 1})
     */
    private $published = true;

    public function __construct()
    {
        $this->matieres = new ArrayCollection();
        $this->pointDivers = new ArrayCollection();
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
     * @param mixed $annee
     */
    public function setAnnee($annee)
    {
        $this->annee = $annee;
    }

    /**
     * @return mixed
     */
    public function getAnnee()
    {
        return $this->annee;
    }

    /**
     * @param mixed $degre
     */
    public function setDegre($degre)
    {
        $this->degre = $degre;
    }

    /**
     * @return mixed
     */
    public function getDegre()
    {
        return $this->degre;
    }

    /**
     * @param mixed $end
     */
    public function setEnd($end)
    {
        $this->end = $end;
    }

    /**
     * @return mixed
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @param mixed $start
     */
    public function setStart($start)
    {
        $this->start = $start;
    }

    /**
     * @return mixed
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @param mixed $ReunionParent
     */
    public function setReunionParent($ReunionParent)
    {
        $this->ReunionParent = $ReunionParent;
    }

    /**
     * @return mixed
     */
    public function getReunionParent()
    {
        return $this->ReunionParent;
    }

    /**
     * @param mixed $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * @return mixed
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param mixed $onlyPoint
     */
    public function setOnlyPoint($onlyPoint)
    {
        $this->onlyPoint = $onlyPoint;
    }

    /**
     * @return mixed
     */
    public function getOnlyPoint()
    {
        return $this->onlyPoint;
    }

    public function getMatieres()
    {
        return $this->matieres->toArray();
    }

    //Alias because it's actually some course sessions and it makes everything way too confusing for me.
    public function getCourseSessions()
    {
        return $this->getMatieres();
    }

    public function addMatiere(CourseSession $matiere)
    {
        if (!$this->matieres->contains($matiere)) {
            $this->matieres->add($matiere);
        }

        return $this;
    }

    public function setCourseSessions(array $sessions) 
    {
        $this->matieres = new ArrayCollection($sessions);
    }

    public function removeMatiere(CourseSession $matiere)
    {
        if ($this->matieres->contains($matiere)) {
            $this->matieres->removeElement($matiere);
        }

        return $this;
    }

    public function invertSession(CourseSession $matiere)
    {
        if ($this->matieres->contains($matiere)) {
            $this->matieres->removeElement($matiere);
        } else {
            $this->matieres->add($matiere);
        }

        return $this;
    }

    public function getPointDivers()
    {
        return $this->pointDivers->toArray();
    }

    public function setPointDivers(array $pointDivers)
    {
        $this->pointDivers = $pointDivers;
    }

    public function addPointDiver(PointDivers $pointDivers)
    {
        if (!$this->pointDivers->contains($pointDivers)) {
            $this->pointDivers->add($pointDivers);
        }

        return $this;
    }

    public function removePointDiver(PointDivers $pointDivers)
    {
        if ($this->pointDivers->contains($pointDivers)) {
            $this->pointDivers->removeElement($pointDivers);
        }

        return $this;
    }

    public function getCoefficient()
    {
        return $this->coefficient;
    }

    public function setCoefficient($coefficient)
    {
        $this->coefficient = $coefficient;
    }
    function getPeriodesGroup() {
        return $this->periodesGroup;
    }

    function setPeriodesGroup($periodesGroup) {
        $this->periodesGroup = $periodesGroup;
    }

    /**
     * @return Periode
     */
    function getOldPeriode1() {
        return $this->oldPeriode1;
    }
    /**
     * @return Periode
     */
    function getOldPeriode2() {
        return $this->oldPeriode2;
    }
    /**
     * @return Periode
     */
    function getOldPeriode3() {
        return $this->oldPeriode3;
    }
    /**
     * @return Periode
     */
    function getOldPeriode4() {
        return $this->oldPeriode4;
    }
    /**
     * @return Periode
     */
    function getOldPeriode5() {
        return $this->oldPeriode5;
    }
    
    /**
     * 
     * @param \FormaLibre\BulletinBundle\Entity\Periode $oldPeriode1
     */
    function setOldPeriode1(Periode $oldPeriode1) {
        $this->oldPeriode1 = $oldPeriode1;
    }

    function setOldPeriode2($oldPeriode2) {
        $this->oldPeriode2 = $oldPeriode2;
    }

    function setOldPeriode3($oldPeriode3) {
        $this->oldPeriode3 = $oldPeriode3;
    }

    function setOldPeriode4($oldPeriode4) {
        $this->oldPeriode4 = $oldPeriode4;
    }

    function setOldPeriode5($oldPeriode5) {
        $this->oldPeriode5 = $oldPeriode5;
    }

    /**
     * @return mixed
     */
    public function isLocked()
    {
        return $this->locked;
    }

    /**
     * @param mixed $locked
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;
    }

    /**
     * @return mixed
     */
    public function isPublished()
    {
        return $this->published;
    }

    /**
     * @param mixed $published
     */
    public function setPublished($published)
    {
        $this->published = $published;
    }
}