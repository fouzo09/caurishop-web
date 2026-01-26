<?php

namespace App\Services;

use App\Repositories\UserRepository;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class UserService
{
    public function __construct(
        protected UserRepository $userRepository
    ) {}

    public function getPaginatedUsers(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->userRepository->paginate($perPage, $filters);
    }

    public function getAllUsers(): Collection
    {
        return $this->userRepository->all();
    }

    public function getUserById(int $id): ?User
    {
        return $this->userRepository->findById($id);
    }

    public function createUser(array $data): User
    {
        DB::beginTransaction();

        try {
            $data['password'] = Hash::make($data['password']);
            $data['is_active'] = $data['is_active'] ?? true;

            $roles = $data['roles'] ?? [];
            unset($data['roles']);

            $user = $this->userRepository->create($data);

            if (!empty($roles)) {
                $user->syncRoles($roles);
            }

            DB::commit();

            return $user->load('roles');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateUser(User $user, array $data): User
    {
        DB::beginTransaction();

        try {
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            $roles = $data['roles'] ?? [];
            unset($data['roles']);

            $this->userRepository->update($user, $data);

            if (isset($roles)) {
                $user->syncRoles($roles);
            }

            DB::commit();

            return $user->fresh()->load('roles');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteUser(User $user): bool
    {
        return $this->userRepository->delete($user);
    }

    public function activateUser(User $user): bool
    {
        if ($user->is_active) {
            throw new \Exception('L\'utilisateur est déjà actif.');
        }

        return $this->userRepository->activate($user);
    }

    public function suspendUser(User $user): bool
    {
        if (!$user->is_active) {
            throw new \Exception('L\'utilisateur est déjà suspendu.');
        }

        return $this->userRepository->suspend($user);
    }

    public function verifyUserEmail(User $user): bool
    {
        if ($user->hasVerifiedEmail()) {
            throw new \Exception('L\'email est déjà vérifié.');
        }

        return $this->userRepository->verifyEmail($user);
    }

    public function toggleUserStatus(User $user): bool
    {
        return $user->is_active
            ? $this->suspendUser($user)
            : $this->activateUser($user);
    }

    public function emailExists(string $email, ?int $excludeUserId = null): bool
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            return false;
        }

        return $excludeUserId ? $user->id !== $excludeUserId : true;
    }
}
