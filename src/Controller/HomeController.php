<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\Response;
use App\Service\AuthService;
use App\View\ViewHelper;

/**
 * Entry point: sends logged-in users to the incidents log and guests to the login page.
 */
final class HomeController extends AbstractController
{
    public function __construct(ViewHelper $view, private readonly AuthService $auth)
    {
        parent::__construct($view);
    }

    public function index(): Response
    {
        return Response::redirect($this->auth->currentUser() !== null ? '/incidents' : '/login');
    }
}
