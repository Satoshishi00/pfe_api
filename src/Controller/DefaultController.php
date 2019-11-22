<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends AbstractController
{
    /**
     * @Route("/default", name="default", methods={"GET"})
     */
    public function index()
    {
        return new JsonResponse([
            "message" => "Welcome to your new controller!",
            "path" => "src/Controller/DefaultController.php"],
             200);
    }
}
