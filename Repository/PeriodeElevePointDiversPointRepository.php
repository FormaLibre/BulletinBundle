<?php

namespace FormaLibre\BulletinBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\User;
use FormaLibre\BulletinBundle\Entity\Periode;

class PeriodeElevePointDiversPointRepository extends EntityRepository
{
    public function findPeriodeElevePointDivers(User $user, Periode $periode)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('pepdp')
            ->from('FormaLibre\BulletinBundle\Entity\PeriodeElevePointDiversPoint', 'pepdp')
            ->where('pepdp.periode = :periode')
            ->andWhere('pepdp.eleve = :user')
            ->orderBy('pepdp.position')
            ->setParameter('periode', $periode)
            ->setParameter('user', $user);
        $query = $qb->getQuery();
        return $results = $query->getResult();
    }

    public function findPEPDPByUserAndNonOnlyPointPeriode(User $user, array $periodesIds)
    {
        $dql = '
            SELECT pepdp
            FROM FormaLibre\BulletinBundle\Entity\PeriodeElevePointDiversPoint pepdp
            JOIN pepdp.periode p
            WHERE pepdp.eleve = :eleve
            AND p.id IN (:periodesIds)
            AND (
                p.onlyPoint IS NULL
                OR p.onlyPoint = false
            )
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('eleve', $user);
        $query->setParameter('periodesIds', $periodesIds);

        return $query->getResult();
    }

    public function findPepdpsByUserAndIds(User $user, array $ids)
    {
        $dql = '
            SELECT pepdp
            FROM FormaLibre\BulletinBundle\Entity\PeriodeElevePointDiversPoint pepdp
            WHERE pepdp.eleve = :eleve
            AND pepdp.id IN (:ids)
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('eleve', $user);
        $query->setParameter('ids', $ids);

        return $query->getResult();
    }
}