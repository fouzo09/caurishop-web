<?php

use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\InstallmentController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\SecurityController;
use App\Http\Controllers\Company\DashboardController as CompanyDashboard;
use App\Http\Controllers\Company\OrderController as CompanyOrderController;
use App\Http\Controllers\Company\EmployeeController as CompanyEmployeeController;
use App\Http\Controllers\Company\ProfileController as CompanyProfileController;
use App\Http\Controllers\Portal\DashboardController as PortalDashboard;
use App\Http\Controllers\Portal\ProductController as PortalProductController;
use App\Http\Controllers\Portal\OrderController as PortalOrderController;
use App\Http\Controllers\Portal\PaymentController as PortalPaymentController;
use App\Http\Controllers\Portal\ProfileController as PortalProfileController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => view('landing'))->name('home');

Route::get('/demarrer', fn() => view('get-started'))->name('get-started');
Route::post('/demarrer', function (\Illuminate\Http\Request $request, \App\Services\Admin\CompanyService $companyService) {
    $request->validate([
        'g_nom'           => 'required|string|max:100',
        'g_prenom'        => 'required|string|max:100',
        'g_tel'           => 'required|string|max:30',
        'g_piece'         => 'required|string|max:100',
        'g_adresse'       => 'required|string|max:255',
        'e_raison'        => 'required|string|max:200',
        'e_email'         => 'required|email|unique:companies,email',
        'e_tel'           => 'required|string|max:30',
        'e_registration'  => 'nullable|string|max:100',
        'e_date'          => 'required|date',
        'e_employes'      => 'required|string|max:50',
        'e_adresse'       => 'required|string|max:255',
        'e_ville'         => 'required|string|max:100',
        'e_pays'          => 'required|string|max:100',
        'doc_rccm'        => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        'doc_nif'         => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        'doc_statuts'     => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        'doc_cni'         => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        'doc_patente'     => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        'doc_domicile'    => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
    ]);

    $data = [
        'raison_sociale'      => $request->e_raison,
        'email'               => $request->e_email,
        'phone'               => $request->e_tel,
        'registration_number' => $request->e_registration,
        'address'             => $request->e_adresse,
        'city'                => $request->e_ville,
        'country'             => $request->e_pays,
        'date_creation'       => $request->e_date,
        'nombre_employes'     => $request->e_employes,
        'gerant_nom'          => $request->g_nom,
        'gerant_prenom'       => $request->g_prenom,
        'gerant_tel'          => $request->g_tel,
        'gerant_piece'        => $request->g_piece,
        'gerant_adresse'      => $request->g_adresse,
    ];

    $files = [
        'doc_rccm'     => $request->file('doc_rccm'),
        'doc_nif'      => $request->file('doc_nif'),
        'doc_statuts'  => $request->file('doc_statuts'),
        'doc_cni'      => $request->file('doc_cni'),
        'doc_patente'  => $request->file('doc_patente'),
        'doc_domicile' => $request->file('doc_domicile'),
    ];

    $companyService->createRegistrationRequest($data, $files);

    return redirect()->route('get-started')->with('success', true);
})->name('get-started.store');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->name('login.authenticate');
});

Route::middleware('auth')->group(function () {
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::prefix('admin')->name('admin.')->group(function () {

        Route::get('/', [HomeController::class, 'index'])->name('dashboard');

        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/', [ProfileController::class, 'show'])->name('show');
            Route::put('/', [ProfileController::class, 'update'])->name('update');
            Route::put('/password', [ProfileController::class, 'changePassword'])->name('password');
        });

        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/create', [UserController::class, 'create'])->name('create');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::get('/{user}', [UserController::class, 'show'])->name('show');
            Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
            Route::put('/{user}', [UserController::class, 'update'])->name('update');
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
            Route::post('/{user}/suspend', [UserController::class, 'suspend'])->name('suspend');
            Route::post('/{user}/activate', [UserController::class, 'activate'])->name('activate');
            Route::post('/{user}/verify', [UserController::class, 'verify'])->name('verify');
        });

        Route::prefix('companies')->name('companies.')->group(function () {
            Route::get('/', [CompanyController::class, 'index'])->name('index');
            Route::get('/create', [CompanyController::class, 'create'])->name('create');
            Route::post('/', [CompanyController::class, 'store'])->name('store');
            Route::get('/{company}', [CompanyController::class, 'show'])->name('show');
            Route::get('/{company}/edit', [CompanyController::class, 'edit'])->name('edit');
            Route::put('/{company}', [CompanyController::class, 'update'])->name('update');
            Route::delete('/{company}', [CompanyController::class, 'destroy'])->name('destroy');
            Route::post('/{company}/activate', [CompanyController::class, 'activate'])->name('activate');
            Route::post('/{company}/deactivate', [CompanyController::class, 'deactivate'])->name('deactivate');
            Route::post('/{company}/approve', [CompanyController::class, 'approve'])->name('approve');
            Route::post('/{company}/reject', [CompanyController::class, 'reject'])->name('reject');
        });

        Route::prefix('customers')->name('customers.')->group(function () {
            Route::get('/', [CustomerController::class, 'index'])->name('index');
            Route::get('/create', [CustomerController::class, 'create'])->name('create');
            Route::post('/', [CustomerController::class, 'store'])->name('store');
            Route::get('/{customer}', [CustomerController::class, 'show'])->name('show');
            Route::get('/{customer}/edit', [CustomerController::class, 'edit'])->name('edit');
            Route::put('/{customer}', [CustomerController::class, 'update'])->name('update');
            Route::delete('/{customer}', [CustomerController::class, 'destroy'])->name('destroy');
            Route::post('/{customer}/activate', [CustomerController::class, 'activate'])->name('activate');
            Route::post('/{customer}/deactivate', [CustomerController::class, 'deactivate'])->name('deactivate');
        });

        Route::prefix('products')->name('products.')->group(function () {
            Route::get('/', [ProductController::class, 'index'])->name('index');
            Route::get('/create', [ProductController::class, 'create'])->name('create');
            Route::post('/', [ProductController::class, 'store'])->name('store');
            Route::get('/{product}', [ProductController::class, 'show'])->name('show');
            Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
            Route::put('/{product}', [ProductController::class, 'update'])->name('update');
            Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
            Route::post('/{product}/activate', [ProductController::class, 'activate'])->name('activate');
            Route::post('/{product}/deactivate', [ProductController::class, 'deactivate'])->name('deactivate');
            Route::post('/{product}/publish', [ProductController::class, 'publish'])->name('publish');
            Route::post('/{product}/unpublish', [ProductController::class, 'unpublish'])->name('unpublish');

            // Images
            Route::post('/{product}/images', [ProductController::class, 'storeImages'])->name('images.store');
            Route::delete('/{product}/images/{image}', [ProductController::class, 'destroyImage'])->name('images.destroy');
            Route::post('/{product}/images/{image}/primary', [ProductController::class, 'setPrimaryImage'])->name('images.primary');

            // Variants
            Route::get('/{product}/variants/create', [ProductController::class, 'createVariant'])->name('variants.create');
            Route::post('/{product}/variants', [ProductController::class, 'storeVariant'])->name('variants.store');
            Route::get('/{product}/variants/{variant}/edit', [ProductController::class, 'editVariant'])->name('variants.edit');
            Route::put('/{product}/variants/{variant}', [ProductController::class, 'updateVariant'])->name('variants.update');
            Route::delete('/{product}/variants/{variant}', [ProductController::class, 'destroyVariant'])->name('variants.destroy');
        });

        Route::prefix('installments')->name('installments.')->group(function () {
            Route::get('/', [InstallmentController::class, 'index'])->name('index');
            Route::get('/{installment}/pay', [InstallmentController::class, 'pay'])->name('pay');
            Route::post('/{installment}/pay', [InstallmentController::class, 'recordPayment'])->name('record-payment');
        });

        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', [PaymentController::class, 'index'])->name('index');
        });

        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [OrderController::class, 'index'])->name('index');
            Route::get('/create', [OrderController::class, 'create'])->name('create');
            Route::post('/', [OrderController::class, 'store'])->name('store');
            Route::get('/{order}', [OrderController::class, 'show'])->name('show');
            Route::post('/{order}/confirm', [OrderController::class, 'confirm'])->name('confirm');
            Route::post('/{order}/deliver', [OrderController::class, 'deliver'])->name('deliver');
            Route::post('/{order}/cancel', [OrderController::class, 'cancel'])->name('cancel');
        });

        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/', [RoleController::class, 'index'])->name('index');
            Route::get('/create', [RoleController::class, 'create'])->name('create');
            Route::post('/', [RoleController::class, 'store'])->name('store');
            Route::get('/{role}', [RoleController::class, 'show'])->name('show');
            Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit');
            Route::put('/{role}', [RoleController::class, 'update'])->name('update');
            Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('permissions')->name('permissions.')->group(function () {
            Route::get('/', [PermissionController::class, 'index'])->name('index');
        });

        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SettingsController::class, 'index'])->name('index');
            Route::put('/{group}', [SettingsController::class, 'update'])->name('update');
        });

        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [NotificationController::class, 'index'])->name('index');
            Route::get('/unread', [NotificationController::class, 'unreadJson'])->name('unread');
            Route::post('/{id}/read', [NotificationController::class, 'markRead'])->name('read');
            Route::post('/read-all', [NotificationController::class, 'markAllRead'])->name('read-all');
            Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('security')->name('security.')->group(function () {
            Route::get('/', [SecurityController::class, 'index'])->name('index');
        });
    });

    // ── Portail Admin Entreprise ─────────────────────────────────────
    Route::prefix('company')->name('company.')->middleware('role:company_admin')->group(function () {
        Route::get('/', [CompanyDashboard::class, 'index'])->name('dashboard');

        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [CompanyOrderController::class, 'index'])->name('index');
            Route::get('/create', [CompanyOrderController::class, 'create'])->name('create');
            Route::post('/', [CompanyOrderController::class, 'store'])->name('store');
            Route::get('/{order}', [CompanyOrderController::class, 'show'])->name('show');
            Route::post('/{order}/approve', [CompanyOrderController::class, 'approve'])->name('approve');
            Route::post('/{order}/reject', [CompanyOrderController::class, 'reject'])->name('reject');
        });

        Route::prefix('employees')->name('employees.')->group(function () {
            Route::get('/', [CompanyEmployeeController::class, 'index'])->name('index');
        });

        Route::get('/profile', [CompanyProfileController::class, 'show'])->name('profile');
        Route::put('/profile', [CompanyProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [CompanyProfileController::class, 'changePassword'])->name('profile.password');
    });

    // ── Portail Employé ──────────────────────────────────────────────
    Route::prefix('portal')->name('portal.')->middleware('role:company_employee')->group(function () {
        Route::get('/', [PortalDashboard::class, 'index'])->name('dashboard');

        Route::prefix('products')->name('products.')->group(function () {
            Route::get('/', [PortalProductController::class, 'index'])->name('index');
            Route::get('/{product}', [PortalProductController::class, 'show'])->name('show');
        });

        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [PortalOrderController::class, 'index'])->name('index');
            Route::get('/create', [PortalOrderController::class, 'create'])->name('create');
            Route::post('/', [PortalOrderController::class, 'store'])->name('store');
            Route::get('/{order}', [PortalOrderController::class, 'show'])->name('show');
        });

        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', [PortalPaymentController::class, 'index'])->name('index');
        });

        Route::get('/profile', [PortalProfileController::class, 'show'])->name('profile');
        Route::put('/profile', [PortalProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [PortalProfileController::class, 'changePassword'])->name('profile.password');
    });
});


//Tahoma - 400
//
//Family
//Inter, "Segoe UI", Tahoma, Geneva, Verdana, sans-serif
//Style
//normal
//Weight
//400
//Color
//rgb(31, 41, 55)
//Size
//14px
//Line Height
//20px
//AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrS
