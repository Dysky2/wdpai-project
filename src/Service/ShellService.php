<?php

declare(strict_types=1);

namespace App\Service;

use App\Core\Translator;

/** Builds the layout shell (navigation + current user chip) from the logged-in user. */
final class ShellService
{
    public function __construct(
        private readonly AuthService $auth,
        private readonly Translator $translator,
    ) {
    }

    /** @return array{navItems: list<array{label: string, href: string, route: string}>, currentUser: ?array{roleLabel: string, name: string, initial: string}, activeRoute: string} */
    public function build(string $activeRoute): array
    {
        $user = $this->auth->currentUser();
        if ($user === null) {
            return ['navItems' => [], 'currentUser' => null, 'activeRoute' => $activeRoute];
        }

        $navItems = [
            ['label' => $this->translator->translate('nav.incidents'), 'href' => '/incidents', 'route' => '/incidents'],
            ['label' => $this->translator->translate('nav.standings'), 'href' => '/standings', 'route' => '/standings'],
        ];
        if ($user->isTeamPrincipal()) {
            $navItems[] = ['label' => $this->translator->translate('nav.new_protest'), 'href' => '/incidents/create', 'route' => '/incidents/create'];
        }

        return [
            'navItems' => $navItems,
            'currentUser' => [
                'roleLabel' => $this->translator->translate($user->isSteward() ? 'role.FIA_STEWARD' : 'role.TEAM_PRINCIPAL'),
                'name' => $user->getDisplayName(),
                'initial' => mb_strtoupper(mb_substr($user->getFirstName(), 0, 1)),
            ],
            'activeRoute' => $activeRoute,
        ];
    }
}
