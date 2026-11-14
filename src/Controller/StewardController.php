<?php

declare(strict_types=1);

namespace App\Controller;

use App\Demo\DemoData;
use App\Exception\NotFoundException;
use App\Http\Request;
use App\Http\Response;

final class StewardController extends AbstractController
{
    public function __construct(private readonly DemoData $demo)
    {
        parent::__construct();
    }

    public function adjudicate(Request $request, int $id): Response
    {
        $form = $this->demo->adjudicationForm($id) ?? throw new NotFoundException('Incident not found');

        if ($request->isPost()) {
            return Response::redirect('/incidents/' . $id);
        }

        return $this->render('stewards/adjudicate', $this->demo->shell('/incidents') + [
            'pageTitle' => 'Orzeczenie ' . $form['incident']['code'] . ' — ApexSteward',
        ] + $form);
    }
}
