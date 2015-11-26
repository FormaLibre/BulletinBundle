<?php

namespace FormaLibre\BulletinBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

class Pemps
{
    private $pemps;

    private $pemds;

    public function __construct()
    {
        $this->pemps = new ArrayCollection();
        $this->pemds = new ArrayCollection();
    }

    /**
     * @param \FormaLibre\BulletinBundle\Entity\ArrayCollection $pemps
     */
    public function setPemps($pemps)
    {
        $this->pemps = $pemps;
    }

    /**
     * @return \FormaLibre\BulletinBundle\Entity\ArrayCollection
     */
    public function getPemps()
    {
        return $this->pemps;
    }

    /**
     * @param \FormaLibre\BulletinBundle\Entity\ArrayCollection $pemds
     */
    public function setPemds($pemds)
    {
        $this->pemps = $pemds;
    }

    /**
     * @return \FormaLibre\BulletinBundle\Entity\ArrayCollection
     */
    public function getPemds()
    {
        return $this->pemds;
    }
          
}