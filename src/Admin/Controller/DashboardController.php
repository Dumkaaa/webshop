<?php

namespace App\Admin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for the main admin dashboard.
 */
class DashboardController extends AbstractController
{
    /**
     * @Route("/", name="admin_dashboard")
     */
    public function index(): Response
    {
        return $this->render('admin/dashboard/index.html.twig');
    }
}
