<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\User;

/**
 * Contract for user data persistence operations.
 */
interface UserRepositoryInterface
{
    /**
     * Find a user by their primary key.
     */
    public function findById(int $id): ?User;

    /**
     * Find a user by their e-mail address.
     */
    public function findByEmail(string $email): ?User;

    /**
     * Persist a new user and return the entity with the generated id.
     */
    public function save(User $user): User;

    /**
     * Update an existing user record.
     */
    public function update(User $user): void;
}
