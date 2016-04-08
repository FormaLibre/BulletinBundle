<?php

namespace FormaLibre\BulletinBundle\Repository;

use Doctrine\ORM\EntityRepository;

class PeriodeRepository extends EntityRepository
{
    public function findNonOnlyPointPeriodes()
    {
        $dql = '
            SELECT p
            FROM FormaLibre\BulletinBundle\Entity\Periode p
            WHERE p.onlyPoint IS NULL
            OR p.onlyPoint = false
        ';
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function findSearchedGroupsUsers(array $groups, array $searches = array())
    {
        $userProperties = array('firstName', 'lastName');
        $groupProperties = array('classe');

        $dql = '
            SELECT u.id as id,
                   u.username as username,
                   u.firstName as first_name,
                   u.lastName as last_name,
                   g.id as group_id,
                   g.name as group_name
            FROM Claroline\CoreBundle\Entity\User u
            JOIN u.groups g
            WHERE g IN (:groups)
        ';

        foreach ($searches as $key => $search) {
            foreach ($search as $id => $el) {
                if (in_array($key, $userProperties)) {
                    $dql .= " AND UPPER(u.{$key}) LIKE :{$key}{$id}";
                } elseif (in_array($key, $groupProperties)) {
                    $dql .= " AND UPPER(g.name) LIKE :{$key}{$id}";
                }
            }
        }
        $query = $this->_em->createQuery($dql);
        $query->setParameter('groups', $groups);

        foreach ($searches as $key => $search) {
            foreach ($search as $id => $el) {
                if (in_array($key, $userProperties) || in_array($key, $groupProperties)) {
                    $query->setParameter($key . $id, '%' . strtoupper($el) . '%');
                }
            }
        }

        return $query->getResult();
    }
}
