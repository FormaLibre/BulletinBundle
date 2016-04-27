<?php

namespace FormaLibre\BulletinBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Claroline\CursusBundle\Entity\CourseSession;
use Doctrine\ORM\EntityRepository;

class EleveMatiereOptionsRepository extends EntityRepository
{
    public function findEleveMatiereOptionsByEleveAndMatiere(User $eleve, CourseSession $matiere)
    {
        $dql = '
            SELECT emo
            FROM FormaLibre\BulletinBundle\Entity\EleveMatiereOptions emo
            WHERE emo.eleve = :eleve
            AND emo.matiere = :matiere
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('eleve', $eleve);
        $query->setParameter('matiere', $matiere);

        return $query->getOneOrNullResult();
    }

    public function findEleveMatiereOptionsByUserAndIds(User $eleve, array $ids)
    {
        $dql = '
            SELECT emo
            FROM FormaLibre\BulletinBundle\Entity\EleveMatiereOptions emo
            WHERE emo.eleve = :eleve
            AND emo.id IN (:ids)
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('eleve', $eleve);
        $query->setParameter('ids', $ids);

        return $query->getResult();
    }
}