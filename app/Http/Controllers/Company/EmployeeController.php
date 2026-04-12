<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(): View
    {
        $company = auth()->user()->company;

        // Employés = users avec rôle company_employee liés à cette company
        $userEmployees = User::role('company_employee')
            ->where('company_id', $company->id)
            ->with('customer')
            ->get();

        // Customers (profils acheteurs) liés à cette company
        $customers = Customer::where('company_id', $company->id)
            ->with(['orders' => fn ($q) => $q->latest()->limit(1)])
            ->get();

        return view('company.employees.index', compact('company', 'userEmployees', 'customers'));
    }
}
