<?php

namespace App\Entity\Factory;

use App\Entity\Order;

class OrderFactory
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct($em)
    {
        $this->em = $em;
    }

    public function make()
    {
        $order = new Order();
    }
}