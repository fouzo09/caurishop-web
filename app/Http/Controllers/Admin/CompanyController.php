<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateCompanyRequest;
use App\Http\Requests\Admin\UpdateCompanyRequest;
use App\Services\Admin\CompanyService;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function __construct(
        protected CompanyService $companyService
    ) {}

    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->get('search'),
            'is_active' => $request->get('is_active'),
            'city' => $request->get('city'),
            'country' => $request->get('country'),
        ];

        $companies = $this->companyService->getPaginatedCompanies(15, $filters);

        return view('admin.companies.index', compact('companies'));
    }

    public function create(): View
    {
        return view('admin.companies.create');
    }

    public function store(CreateCompanyRequest $request): RedirectResponse
    {
        try {
            $company = $this->companyService->createCompany($request->validated());

            return redirect()->route('admin.companies.index')
                ->with('success', 'Entreprise créée avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la création de l\'entreprise: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(Company $company): View
    {
        $company->load('customers');
        return view('admin.companies.show', compact('company'));
    }

    public function edit(Company $company): View
    {
        return view('admin.companies.edit', compact('company'));
    }

    public function update(UpdateCompanyRequest $request, Company $company): RedirectResponse
    {
        try {
            $this->companyService->updateCompany($company, $request->validated());

            return redirect()->route('admin.companies.index')
                ->with('success', 'Entreprise mise à jour avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(Company $company): RedirectResponse
    {
        try {
            $this->companyService->deleteCompany($company);

            return redirect()->route('admin.companies.index')
                ->with('success', 'Entreprise supprimée avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    public function activate(Company $company): RedirectResponse
    {
        try {
            $this->companyService->activateCompany($company);

            return redirect()->back()
                ->with('success', 'Entreprise activée avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    public function deactivate(Company $company): RedirectResponse
    {
        try {
            $this->companyService->deactivateCompany($company);

            return redirect()->back()
                ->with('success', 'Entreprise désactivée avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }
}
