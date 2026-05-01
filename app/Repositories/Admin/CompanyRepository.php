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
            $query->where(function ($q) use ($filters) {
                $q->where('raison_sociale', 'like', "%{$filters['search']}%")
                  ->orWhere('email', 'like', "%{$filters['search']}%")
                  ->orWhere('registration_number', 'like', "%{$filters['search']}%");
            });
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['city'])) {
            $query->where('city', 'like', "%{$filters['city']}%");
        }

        if (!empty($filters['country'])) {
            $query->where('country', 'like', "%{$filters['country']}%");
        }

        // Tri par importance pour les demandes en attente
        if (($filters['status'] ?? null) === Company::STATUS_PENDING) {
            $query->orderByRaw($this->importanceScoreExpr() . ' DESC')
                  ->orderBy('date_creation', 'asc');
        } else {
            $query->latest();
        }

        return $query->paginate($perPage);
    }

    /**
     * Score 0-10 : employés (0-5 pts) + ancienneté (0-5 pts).
     * Utilisé uniquement pour ORDER BY, jamais sélectionné.
     */
    private function importanceScoreExpr(): string
    {
        return "
            CASE
                WHEN nombre_employes ILIKE 'Plus%'  THEN 5
                WHEN nombre_employes ILIKE '51%'    THEN 4
                WHEN nombre_employes ILIKE '21%'    THEN 3
                WHEN nombre_employes ILIKE '6%'     THEN 2
                WHEN nombre_employes ILIKE '1%'     THEN 1
                ELSE 0
            END
            +
            CASE
                WHEN date_creation IS NULL                                         THEN 0
                WHEN date_creation <= CURRENT_DATE - INTERVAL '10 years'          THEN 5
                WHEN date_creation <= CURRENT_DATE - INTERVAL '5 years'           THEN 4
                WHEN date_creation <= CURRENT_DATE - INTERVAL '2 years'           THEN 3
                WHEN date_creation <= CURRENT_DATE - INTERVAL '1 year'            THEN 2
                ELSE 1
            END
        ";
    }

    public function importanceScore(Company $company): int
    {
        $emp = match(true) {
            str_starts_with((string) $company->nombre_employes, 'Plus') => 5,
            str_starts_with((string) $company->nombre_employes, '51')   => 4,
            str_starts_with((string) $company->nombre_employes, '21')   => 3,
            str_starts_with((string) $company->nombre_employes, '6')    => 2,
            str_starts_with((string) $company->nombre_employes, '1')    => 1,
            default => 0,
        };

        $age = 0;
        if ($company->date_creation) {
            $years = $company->date_creation->diffInYears(now());
            $age = match(true) {
                $years >= 10 => 5,
                $years >= 5  => 4,
                $years >= 2  => 3,
                $years >= 1  => 2,
                default      => 1,
            };
        }

        return $emp + $age;
    }

    public function pendingCount(): int
    {
        return $this->model->where('status', Company::STATUS_PENDING)->count();
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
}
