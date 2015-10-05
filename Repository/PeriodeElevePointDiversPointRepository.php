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
        $qb->select('pemp')
            ->from('FormaLibre\BulletinBundle\Entity\PeriodeElevePointDiversPoint', 'pemp')
            ->where('pemp.periode = :periode')
            ->andWhere('pemp.eleve = :user')
            ->orderBy('pemp.position')
            ->setParameter('periode', $periode)
            ->setParameter('user', $user);
        $query = $qb->getQuery();
        return $results = $query->getResult();
    }

    public function findPEPDPByUserAndNonOnlyPointPeriode(User $user)
    {
        $dql = '
            SELECT pepdp
            FROM FormaLibre\BulletinBundle\Entity\PeriodeElevePointDiversPoint pepdp
            JOIN pepdp.periode p
            WHERE pepdp.eleve = :eleve
            AND (
                p.onlyPoint IS NULL
                OR p.onlyPoint = false
            )
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('eleve', $user);

        return $query->getResult();
    }
}