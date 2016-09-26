<?php

namespace FormaLibre\BulletinBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\User;
use FormaLibre\BulletinBundle\Entity\Periode;
use Claroline\CursusBundle\Entity\CourseSession;

class PeriodeEleveMatierePointRepository extends EntityRepository
{
    public function findPeriodeEleveMatiere(User $user, Periode $periode)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('pemp')
            ->from('FormaLibre\BulletinBundle\Entity\PeriodeEleveMatierePoint', 'pemp')
            ->join('pemp.matiere', 'matiere' )
	        ->join('matiere.course', 'course' )
            ->where('pemp.periode = :periode')
            ->andWhere('pemp.eleve = :user')
            ->addOrderBy('matiere.displayOrder')
            ->setParameter('periode', $periode)
            ->setParameter('user', $user);
        $query = $qb->getQuery();
        return $results = $query->getResult();
    }

    public function findPeriodeMatiereEleve(Periode $periode, User $user, CourseSession $matiere)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('pemp')
            ->from('FormaLibre\BulletinBundle\Entity\PeriodeEleveMatierePoint', 'pemp')
            ->where('pemp.periode = :periode')
            ->andWhere('pemp.eleve = :user')
            ->andWhere('pemp.matiere = :matiere')
            ->setParameter('periode', $periode)
            ->setParameter('user', $user)
            ->setParameter('matiere', $matiere);
        $query = $qb->getQuery();

        return $results = $query->getOneOrNullResult();
    }

    public function findPEMPByUserAndNonOnlyPointPeriode(User $user)
    {
        $dql = '
            SELECT pemp
            FROM FormaLibre\BulletinBundle\Entity\PeriodeEleveMatierePoint pemp
            JOIN pemp.periode p
            WHERE pemp.eleve = :eleve
            AND (
                p.onlyPoint IS NULL
                OR p.onlyPoint = false
            )
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('eleve', $user);

        return $query->getResult();
    }

    public function findPempsByUserAndIds(User $user, array $ids)
    {
        $dql = '
            SELECT pemp
            FROM FormaLibre\BulletinBundle\Entity\PeriodeEleveMatierePoint pemp
            WHERE pemp.eleve = :eleve
            AND pemp.id IN (:ids)
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('eleve', $user);
        $query->setParameter('ids', $ids);

        return $query->getResult();
    }
}