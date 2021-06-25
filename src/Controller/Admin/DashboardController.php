<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\Category;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DashboardController extends AbstractController
{
    /**
     * @Route("/admin/dashboard", name="admin_dashboard")
     */
    public function index(): Response
    {
        $widget = array();

        $entityManager = $this->getDoctrine()->getManager();

        $widget['user'] = count($entityManager->getRepository(User::class)->findAll());
        $widget['category'] = count($entityManager->getRepository(Category::class)->findAll());
        $widget['product'] = count($entityManager->getRepository(Product::class)->findAll());
        $widget['order'] = count($entityManager->getRepository(Order::class)->findAll());

        return $this->render('admin/dashboard/index.html.twig', [
            'widget' => $widget,
        ]);
    }   
}
