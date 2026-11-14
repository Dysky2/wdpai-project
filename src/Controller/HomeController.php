<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\Response;

/**
 * Entry point: sends visitors to the right start page.
 * Until sessions exist (login card) every visitor is a guest, so this redirects to /login.
 */
final class HomeController extends AbstractController
{
    public function index(): Response
    {
        return Response::redirect('/login');
    }
}
