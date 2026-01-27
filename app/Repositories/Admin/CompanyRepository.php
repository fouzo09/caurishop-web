<?php

namespace App\Repositories\Admin;

use App\Models\Company;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CompanyRepository
{
    public function __construct(
        protected Company $model
    ) {}

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->withCount('customers');

        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                    ->orWhere('email', 'like', "%{$filters['search']}%")
                    ->orWhere('registration_number', 'like', "%{$filters['search']}%");
            });
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (!empty($filters['city'])) {
            $query->where('city', 'like', "%{$filters['city']}%");
        }

        if (!empty($filters['country'])) {
            $query->where('country', 'like', "%{$filters['country']}%");
        }

        return $query->latest()->paginate($perPage);
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function findById(int $id): ?Company
    {
        return $this->model->with('customers')->find($id);
    }

    public function findByEmail(string $email): ?Company
    {
        return $this->model->where('email', $email)->first();
    }

    public function findByRegistrationNumber(string $registrationNumber): ?Company
    {
        return $this->model->where('registration_number', $registrationNumber)->first();
    }

    public function create(array $data): Company
    {
        return $this->model->create($data);
    }

    public function update(Company $company, array $data): bool
    {
        return $company->update($data);
    }

    public function delete(Company $company): bool
    {
        return $company->delete();
    }

    public function activate(Company $company): bool
    {
        return $company->update(['is_active' => true]);
    }

    public function deactivate(Company $company): bool
    {
        return $company->update(['is_active' => false]);
    }

    public function getActive(): Collection
    {
        return $this->model->where('is_active', true)->get();
    }

    public function getInactive(): Collection
    {
        return $this->model->where('is_active', false)->get();
    }
}
