<?php

namespace FormaLibre\BulletinBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\User;
use FormaLibre\BulletinBundle\Entity\Periode;
use Claroline\CursusBundle\Entity\CourseSession;

class LockStatusRepository extends EntityRepository
{
    public function findLockStatus(CourseSession $matiere,Periode $periode)
    {
        $dql = '
            SELECT l
            FROM FormaLibre\BulletinBundle\Entity\LockStatus l
            WHERE l.periode = :periode
            AND l.matiere = :matiere 
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('periode', $periode);
        $query->setParameter('matiere', $matiere);
      

        $lockStatus= $query->getOneOrNullResult();
        
        if(is_null($lockStatus)){
            return $lockStatus=true;
        }
        else {
           return $lockStatus->getLockStatus();
        }
        
    }
}
