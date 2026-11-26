<?php

declare(strict_types=1);

namespace App\Service;

use App\Http\Session;
use App\Model\User;
use App\Repository\UserRepositoryInterface;

final class AuthService implements CurrentUserProviderInterface
{
    private ?User $currentUser = null;
    private bool $currentUserLoaded = false;

    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly Session $session,
    ) {
    }

    /**
     * Checks credentials; returns the user or null without telling which part was wrong.
     * A hash is always verified (a throwaway one for unknown e-mails) to equalise timing.
     */
    public function attempt(string $email, string $password): ?User
    {
        $user = $this->users->findByEmail(trim($email));
        $hash = $user?->getPasswordHash() ?? password_hash(bin2hex(random_bytes(16)), PASSWORD_ARGON2ID);
        $valid = password_verify($password, $hash);

        if ($user === null || !$valid) {
            return null;
        }

        if (password_needs_rehash($hash, PASSWORD_ARGON2ID)) {
            $this->users->updatePasswordHash($user->getId(), password_hash($password, PASSWORD_ARGON2ID));
        }

        return $user;
    }

    public function login(User $user): void
    {
        $this->session->login($user->getId());
        $this->currentUser = $user;
        $this->currentUserLoaded = true;
    }

    public function logout(): void
    {
        $this->session->destroy();
        $this->currentUser = null;
        $this->currentUserLoaded = true;
    }

    /** Identity is rebuilt from the user_id in the session and the database, never from client data. */
    public function currentUser(): ?User
    {
        if (!$this->currentUserLoaded) {
            $id = $this->session->getUserId();
            $this->currentUser = $id === null ? null : $this->users->findById($id);
            $this->currentUserLoaded = true;
        }

        return $this->currentUser;
    }
}
