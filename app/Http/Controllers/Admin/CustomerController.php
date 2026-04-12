<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateCustomerRequest;
use App\Http\Requests\Admin\UpdateCustomerRequest;
use App\Services\Admin\CustomerService;
use App\Models\Customer;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class CustomerController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:customers.view', only: ['index', 'show']),
            new Middleware('permission:customers.create', only: ['create', 'store']),
            new Middleware('permission:customers.edit', only: ['edit', 'update']),
            new Middleware('permission:customers.delete', only: ['destroy']),
            new Middleware('permission:customers.activate', only: ['activate', 'deactivate']),
        ];
    }

    public function __construct(
        protected CustomerService $customerService
    ) {}

    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->get('search'),
            'is_active' => $request->get('is_active'),
            'type' => $request->get('type'),
            'company_id' => $request->get('company_id'),
        ];

        $customers = $this->customerService->getPaginatedCustomers(15, $filters);
        $companies = Company::where('is_active', true)->get();

        return view('admin.customers.index', compact('customers', 'companies'));
    }

    public function create(): View
    {
        $companies = Company::where('is_active', true)->get();
        return view('admin.customers.create', compact('companies'));
    }

    public function store(CreateCustomerRequest $request): RedirectResponse
    {
        try {
            $customer = $this->customerService->createCustomer($request->validated());

            return redirect()->route('admin.customers.index')
                ->with('success', 'Client créé avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la création du client: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(Customer $customer): View
    {
        $customer->load(['company', 'orders', 'payments']);
        return view('admin.customers.show', compact('customer'));
    }

    public function edit(Customer $customer): View
    {
        $companies = Company::where('is_active', true)->get();
        return view('admin.customers.edit', compact('customer', 'companies'));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        try {
            $this->customerService->updateCustomer($customer, $request->validated());

            return redirect()->route('admin.customers.index')
                ->with('success', 'Client mis à jour avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        try {
            $this->customerService->deleteCustomer($customer);

            return redirect()->route('admin.customers.index')
                ->with('success', 'Client supprimé avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    public function activate(Customer $customer): RedirectResponse
    {
        try {
            $this->customerService->activateCustomer($customer);

            return redirect()->back()
                ->with('success', 'Client activé avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    public function deactivate(Customer $customer): RedirectResponse
    {
        try {
            $this->customerService->deactivateCustomer($customer);

            return redirect()->back()
                ->with('success', 'Client désactivé avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }
}
