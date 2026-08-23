<?php

namespace App\Services\Admin;

use App\Models\Company;
use App\Repositories\Admin\CompanyRepository;
use App\Support\Media;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CompanyService
{
    public function __construct(
        protected CompanyRepository  $companyRepository,
        protected NotificationService $notificationService,
    ) {}

    public function getPaginatedCompanies(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->companyRepository->paginate($perPage, $filters);
    }

    public function pendingCount(): int
    {
        return $this->companyRepository->pendingCount();
    }

    public function importanceScore(Company $company): int
    {
        return $this->companyRepository->importanceScore($company);
    }

    public function getAllCompanies(): Collection
    {
        return $this->companyRepository->all();
    }

    public function getCompanyById(int $id): ?Company
    {
        return $this->companyRepository->findById($id);
    }

    // Création depuis l'admin (statut approved d'office)
    public function createCompany(array $data): Company
    {
        return DB::transaction(function () use ($data) {
            $data['status']    = Company::STATUS_APPROVED;
            $data['is_active'] = $data['is_active'] ?? true;
            return $this->companyRepository->create($data);
        });
    }

    // Création depuis le formulaire public /demarrer
    public function createRegistrationRequest(array $data, array $files = []): Company
    {
        return DB::transaction(function () use ($data, $files) {
            $data['status']    = Company::STATUS_PENDING;
            $data['is_active'] = false;

            $company = $this->companyRepository->create($data);

            // Stockage des fichiers
            // Chaque type de document a son dossier racine : rccm/, nif/, statuts/…
            $docs = [];
            foreach ($files as $field => $file) {
                $folder = $file ? Media::companyDoc($field, $company->id) : null;

                if ($folder) {
                    $docs[$field] = $file->store($folder, Media::diskName());
                }
            }
            if (!empty($docs)) {
                $this->companyRepository->update($company, $docs);
            }

            $this->notificationService->newCompanyRegistration(
                $company->raison_sociale,
                route('admin.companies.show', $company),
            );

            return $company->fresh();
        });
    }

    public function approveCompany(Company $company, float $creditLimit): Company
    {
        if (!$company->isPending()) {
            throw new \Exception('Cette demande n\'est pas en attente.');
        }

        $this->companyRepository->update($company, [
            'status'       => Company::STATUS_APPROVED,
            'is_active'    => true,
            'credit_limit' => $creditLimit,
        ]);

        return $company->fresh();
    }

    public function rejectCompany(Company $company): Company
    {
        if (!$company->isPending()) {
            throw new \Exception('Cette demande n\'est pas en attente.');
        }

        $this->companyRepository->update($company, [
            'status'    => Company::STATUS_REJECTED,
            'is_active' => false,
        ]);

        return $company->fresh();
    }

    public function updateCompany(Company $company, array $data): Company
    {
        return DB::transaction(function () use ($company, $data) {
            $this->companyRepository->update($company, $data);
            return $company->fresh();
        });
    }

    public function deleteCompany(Company $company): bool
    {
        if ($company->customers()->count() > 0) {
            throw new \Exception('Impossible de supprimer une entreprise avec des clients associés.');
        }

        // Supprimer les fichiers si présents
        foreach (array_keys(Media::COMPANY_DOCS) as $doc) {
            Media::delete($company->$doc);
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

    public function emailExists(string $email, ?int $excludeCompanyId = null): bool
    {
        $company = $this->companyRepository->findByEmail($email);
        if (!$company) return false;
        return $excludeCompanyId ? $company->id !== $excludeCompanyId : true;
    }

    public function registrationNumberExists(string $registrationNumber, ?int $excludeCompanyId = null): bool
    {
        $company = $this->companyRepository->findByRegistrationNumber($registrationNumber);
        if (!$company) return false;
        return $excludeCompanyId ? $company->id !== $excludeCompanyId : true;
    }
}
