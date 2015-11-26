<?php

namespace FormaLibre\BulletinBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\User;
use FormaLibre\BulletinBundle\Entity\Periode;
use Claroline\CursusBundle\Entity\CourseSession;

class LockStatusRepository extends EntityRepository
{
    public function findLockStatus(User $user,CourseSession $matiere,Periode $periode)
    {
        $dql = '
            SELECT l
            FROM FormaLibre\BulletinBundle\Entity\LockStatus l
            WHERE l.periode = :periode
            AND l.matiere = :matiere
            AND l.teacher = :teacher
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('periode', $periode);
        $query->setParameter('matiere', $matiere);
        $query->setParameter('teacher', $user);

        $lockStatus= $query->getOneOrNullResult();
        
        if(is_null($lockStatus)){
            return $lockStatus=false;
        }
        else {
           return $lockStatus->getLockStatus();
        }
        
    }
}
