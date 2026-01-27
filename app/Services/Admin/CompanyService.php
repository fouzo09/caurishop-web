<?php

namespace App\Services\Admin;

use App\Repositories\Admin\CompanyRepository;
use App\Models\Company;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CompanyService
{
    public function __construct(
        protected CompanyRepository $companyRepository
    ) {}

    public function getPaginatedCompanies(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->companyRepository->paginate($perPage, $filters);
    }

    public function getAllCompanies(): Collection
    {
        return $this->companyRepository->all();
    }

    public function getCompanyById(int $id): ?Company
    {
        return $this->companyRepository->findById($id);
    }

    public function createCompany(array $data): Company
    {
        DB::beginTransaction();

        try {
            $data['is_active'] = $data['is_active'] ?? true;

            $company = $this->companyRepository->create($data);

            DB::commit();

            return $company;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateCompany(Company $company, array $data): Company
    {
        DB::beginTransaction();

        try {
            $this->companyRepository->update($company, $data);

            DB::commit();

            return $company->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteCompany(Company $company): bool
    {
        if ($company->customers()->count() > 0) {
            throw new \Exception('Impossible de supprimer une entreprise avec des clients associés.');
        }

        return $this->companyRepository->delete($company);
    }

    public function activateCompany(Company $company): bool
    {
        if ($company->is_active) {
            throw new \Exception('L\'entreprise est déjà active.');
        }

        return $this->companyRepository->activate($company);
    }

    public function deactivateCompany(Company $company): bool
    {
        if (!$company->is_active) {
            throw new \Exception('L\'entreprise est déjà désactivée.');
        }

        return $this->companyRepository->deactivate($company);
    }

    public function toggleCompanyStatus(Company $company): bool
    {
        return $company->is_active
            ? $this->deactivateCompany($company)
            : $this->activateCompany($company);
    }

    public function emailExists(string $email, ?int $excludeCompanyId = null): bool
    {
        $company = $this->companyRepository->findByEmail($email);

        if (!$company) {
            return false;
        }

        return $excludeCompanyId ? $company->id !== $excludeCompanyId : true;
    }

    public function registrationNumberExists(string $registrationNumber, ?int $excludeCompanyId = null): bool
    {
        $company = $this->companyRepository->findByRegistrationNumber($registrationNumber);

        if (!$company) {
            return false;
        }

        return $excludeCompanyId ? $company->id !== $excludeCompanyId : true;
    }
}
