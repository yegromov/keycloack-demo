<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Twig\Attribute\Template;

class AuthController extends AbstractController
{
    #[Route('/auth', name: 'app_auth')]
    #[Template('auth/index.html.twig')]
    public function index()
    {
        return [
            'authLink' => 'http://localhost:8085/',
        ];
    }

    #[Route('/authcallback', name: 'app_auth_callback')]
    #[Template('auth/callback.html.twig')]
    public function callback()
    {
        return [
            'userName' => 'User Name (default)',
        ];
    }
}
