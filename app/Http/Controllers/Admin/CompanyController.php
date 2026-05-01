<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateCompanyRequest;
use App\Http\Requests\Admin\UpdateCompanyRequest;
use App\Models\Company;
use App\Services\Admin\CompanyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class CompanyController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:companies.view',     only: ['index', 'show']),
            new Middleware('permission:companies.create',   only: ['create', 'store']),
            new Middleware('permission:companies.edit',     only: ['edit', 'update', 'approve', 'reject']),
            new Middleware('permission:companies.delete',   only: ['destroy']),
            new Middleware('permission:companies.activate', only: ['activate', 'deactivate']),
        ];
    }

    public function __construct(
        protected CompanyService $companyService
    ) {}

    public function index(Request $request): View
    {
        $filters = [
            'search'    => $request->get('search'),
            'is_active' => $request->get('is_active'),
            'status'    => $request->get('status'),
            'city'      => $request->get('city'),
            'country'   => $request->get('country'),
        ];

        $companies    = $this->companyService->getPaginatedCompanies(15, $filters);
        $pendingCount = $this->companyService->pendingCount();
        $showImportance = ($filters['status'] === Company::STATUS_PENDING);
        $scores = $showImportance
            ? $companies->getCollection()
                ->mapWithKeys(fn ($c) => [$c->id => $this->companyService->importanceScore($c)])
            : collect();

        return view('admin.companies.index', compact('companies', 'pendingCount', 'showImportance', 'scores'));
    }

    public function create(): View
    {
        return view('admin.companies.create');
    }

    public function store(CreateCompanyRequest $request): RedirectResponse
    {
        try {
            $this->companyService->createCompany($request->validated());
            return redirect()->route('admin.companies.index')
                ->with('success', 'Entreprise créée avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur : ' . $e->getMessage())
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
                ->with('error', 'Erreur : ' . $e->getMessage())
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
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function activate(Company $company): RedirectResponse
    {
        try {
            $this->companyService->activateCompany($company);
            return redirect()->back()->with('success', 'Entreprise activée avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function deactivate(Company $company): RedirectResponse
    {
        try {
            $this->companyService->deactivateCompany($company);
            return redirect()->back()->with('success', 'Entreprise désactivée avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function approve(Request $request, Company $company): RedirectResponse
    {
        $request->validate([
            'credit_limit' => 'required|numeric|min:0',
        ], [
            'credit_limit.required' => 'La limite de crédit est obligatoire.',
            'credit_limit.numeric'  => 'La limite de crédit doit être un nombre.',
            'credit_limit.min'      => 'La limite de crédit doit être ≥ 0.',
        ]);

        try {
            $this->companyService->approveCompany($company, (float) $request->credit_limit);
            return redirect()->route('admin.companies.show', $company)
                ->with('success', 'Demande approuvée — l\'entreprise est maintenant active.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function reject(Company $company): RedirectResponse
    {
        try {
            $this->companyService->rejectCompany($company);
            return redirect()->route('admin.companies.show', $company)
                ->with('success', 'Demande rejetée.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
