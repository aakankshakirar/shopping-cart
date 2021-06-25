<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Security\Voter\OrderVoter;

/**
 * @Route("/admin/order")
 * @IsGranted("ROLE_ADMIN", message="You are not authorized to view this page Sorry")
 */
class OrderController extends AbstractController
{

    /**
     * @Route("/", name="order_index")
     */
    public function index(OrderRepository $order): Response
    {
        $product = false;
        if(!$product){
            throw $this->createNotFoundException("This order does not exist");
        }

        return $this->render('admin/order/index.html.twig', [
            'orders' => $order->findBy(array(), array('id' => 'DESC')),
        ]);
    }

    /**
     * @Route("/vieworder/{id}", name="view_order", methods={"GET","POST"})
     * @IsGranted("show", subject="order", message="You are not related to this order. You can not view this.")
     */
    public function viewOrder(Order $order): Response
    {
        return $this->render('admin/order/view_order.html.twig', [
            'order' => $order
        ]);
    }
}
