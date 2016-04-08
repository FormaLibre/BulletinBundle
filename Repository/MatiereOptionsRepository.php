<?php

namespace FormaLibre\BulletinBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Claroline\CursusBundle\Entity\CourseSessionUser;
use Doctrine\ORM\EntityRepository;

class MatiereOptionsRepository extends EntityRepository
{
    public function findAllSessionsUsersFromSessions(User $user, array $sessionsList)
    {
        $dql = '
            SELECT su
            FROM Claroline\CursusBundle\Entity\CourseSessionUser su
            JOIN su.session s
            WHERE su.user = :user
            AND su.userType = :userType
            AND s IN (:sessionsList)
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);
        $query->setParameter('userType', CourseSessionUser::LEARNER);
        $query->setParameter('sessionsList', $sessionsList);

        return $query->getResult();
    }

    public function findMatiereOptionsByMatieres(array $matieres)
    {
        $dql = '
            SELECT mo
            FROM FormaLibre\BulletinBundle\Entity\MatiereOptions mo
            WHERE mo.matiere IN (:matieres)
            ORDER BY mo.position ASC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('matieres', $matieres);

        return $query->getResult();
    }

    public function findMatiereOptionsByMatiereId($matiereId)
    {
        $dql = '
            SELECT mo
            FROM FormaLibre\BulletinBundle\Entity\MatiereOptions mo
            JOIN mo.matiere m
            WHERE m.id = :matiereId
            ORDER BY mo.position ASC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('matiereId', $matiereId);

        return $query->getOneOrNullResult();
    }
}