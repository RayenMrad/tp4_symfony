<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * @Route("/{name}", name="homepage")
     */
    public function home($name = 'rayen'): Response
    {
        return $this->render('index.html.twig', ['name' => $name]);
    }
}
