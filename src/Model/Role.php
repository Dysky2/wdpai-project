<?php

declare(strict_types=1);

namespace App\Model;

/**
 * Application role (RBAC); values match the CHECK constraint on users.role.
 */
enum Role: string
{
    case TEAM_PRINCIPAL = 'TEAM_PRINCIPAL';
    case FIA_STEWARD = 'FIA_STEWARD';
}
