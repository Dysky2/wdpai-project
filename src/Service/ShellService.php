<?php

declare(strict_types=1);

namespace App\Service;

/** Builds the layout shell (navigation + current user chip) from the logged-in user. */
final class ShellService
{
    public function __construct(private readonly AuthService $auth)
    {
    }

    /** @return array{navItems: list<array{label: string, href: string, route: string}>, currentUser: ?array{roleLabel: string, name: string, initial: string}, activeRoute: string} */
    public function build(string $activeRoute): array
    {
        $user = $this->auth->currentUser();
        if ($user === null) {
            return ['navItems' => [], 'currentUser' => null, 'activeRoute' => $activeRoute];
        }

        $navItems = [
            ['label' => 'Incydenty', 'href' => '/incidents', 'route' => '/incidents'],
            ['label' => 'Klasyfikacja', 'href' => '/standings', 'route' => '/standings'],
        ];
        if ($user->isTeamPrincipal()) {
            $navItems[] = ['label' => 'Nowy protest', 'href' => '/incidents/create', 'route' => '/incidents/create'];
        }

        return [
            'navItems' => $navItems,
            'currentUser' => [
                'roleLabel' => $user->isSteward() ? 'Sędzia FIA' : 'Szef zespołu',
                'name' => $user->getDisplayName(),
                'initial' => mb_strtoupper(mb_substr($user->getFirstName(), 0, 1)),
            ],
            'activeRoute' => $activeRoute,
        ];
    }
}
