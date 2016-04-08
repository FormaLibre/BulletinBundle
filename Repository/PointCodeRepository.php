<?php

namespace FormaLibre\BulletinBundle\Repository;

use Doctrine\ORM\EntityRepository;

class PointCodeRepository extends EntityRepository
{
    public function findDefaultPointCodes()
    {
        $dql = '
            SELECT c
            FROM FormaLibre\BulletinBundle\Entity\PointCode c
            WHERE c.isDefaultValue = true
        ';

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function findIgnoredPointCodes()
    {
        $dql = '
            SELECT c
            FROM FormaLibre\BulletinBundle\Entity\PointCode c
            WHERE c.ignored = true
        ';

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }
}