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
use App\Http\Controllers\Admin\MarginController;
use App\Http\Controllers\Admin\ScraperController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Company\DashboardController as CompanyDashboard;
use App\Http\Controllers\Company\OrderController as CompanyOrderController;
use App\Http\Controllers\Company\EmployeeController as CompanyEmployeeController;
use App\Http\Controllers\Company\ProfileController as CompanyProfileController;
use App\Http\Controllers\Portal\DashboardController as PortalDashboard;
use App\Http\Controllers\Portal\ProductController as PortalProductController;
use App\Http\Controllers\Portal\OrderController as PortalOrderController;
use App\Http\Controllers\Portal\PaymentController as PortalPaymentController;
use App\Http\Controllers\Portal\ProfileController as PortalProfileController;
use App\Http\Controllers\Portal\DjomyController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => view('landing'))->name('home');

Route::get('/demarrer', fn() => view('get-started'))->name('get-started');
Route::post('/demarrer', function (\Illuminate\Http\Request $request, \App\Services\Admin\CompanyService $companyService) {
    $nameRegex  = ['regex:/^[a-zA-ZÀ-ÿ\s\'\-]{2,}$/'];
    $phoneRegex = ['regex:/^[\+\d][\d\s\-\(\)]{6,19}$/'];
    $villeRegex = ['regex:/^[a-zA-ZÀ-ÿ\s\-]{2,}$/'];

    $request->validate([
        'g_nom'           => array_merge(['required', 'string', 'min:2', 'max:100'], $nameRegex),
        'g_prenom'        => array_merge(['required', 'string', 'min:2', 'max:100'], $nameRegex),
        'g_tel'           => array_merge(['required', 'string', 'min:8', 'max:30'], $phoneRegex),
        'g_piece'         => ['required', 'string', 'in:Carte Nationale d\'Identité (CNI),Passeport,Permis de conduire,Carte de séjour'],
        'g_adresse'       => ['required', 'string', 'min:5', 'max:255'],
        'e_raison'        => ['required', 'string', 'min:3', 'max:200'],
        'e_email'         => ['required', 'email:rfc', 'unique:companies,email'],
        'e_tel'           => array_merge(['required', 'string', 'min:8', 'max:30'], $phoneRegex),
        'e_registration'  => ['nullable', 'string', 'max:100'],
        'e_date'          => ['required', 'date', 'before:today'],
        'e_employes'      => ['required', 'string', 'in:1 – 5,6 – 20,21 – 50,51 – 100,Plus de 100'],
        'e_adresse'       => ['required', 'string', 'min:5', 'max:255'],
        'e_ville'         => array_merge(['required', 'string', 'min:2', 'max:100'], $villeRegex),
        'e_pays'          => array_merge(['required', 'string', 'min:2', 'max:100'], $villeRegex),
        'doc_rccm'        => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        'doc_nif'         => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        'doc_statuts'     => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        'doc_cni'         => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        'doc_patente'     => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        'doc_domicile'    => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
    ], [
        'g_nom.regex'        => 'Le nom ne doit contenir que des lettres.',
        'g_prenom.regex'     => 'Le prénom ne doit contenir que des lettres.',
        'g_tel.regex'        => 'Numéro de téléphone invalide.',
        'g_tel.min'          => 'Le numéro est trop court.',
        'g_piece.in'         => 'Pièce d\'identité invalide.',
        'g_adresse.min'      => 'L\'adresse est trop courte.',
        'e_raison.min'       => 'La raison sociale est trop courte.',
        'e_email.email'      => 'Adresse email invalide.',
        'e_email.unique'     => 'Cette adresse email est déjà utilisée.',
        'e_tel.regex'        => 'Numéro de téléphone invalide.',
        'e_tel.min'          => 'Le numéro est trop court.',
        'e_date.before'      => 'La date de création ne peut pas être dans le futur.',
        'e_employes.in'      => 'Veuillez sélectionner une valeur valide.',
        'e_adresse.min'      => 'L\'adresse est trop courte.',
        'e_ville.regex'      => 'La ville ne doit contenir que des lettres.',
        'e_pays.regex'       => 'Le pays ne doit contenir que des lettres.',
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

        Route::prefix('scraper')->name('scraper.')->group(function () {
            Route::get('/', [ScraperController::class, 'index'])->name('index');
            Route::get('/image-proxy', [ScraperController::class, 'imageProxy'])->name('image-proxy');
            Route::post('/run-html', [ScraperController::class, 'runHtml'])->name('run-html');
            Route::get('/{filename}', [ScraperController::class, 'show'])->name('show');
            Route::post('/{filename}/review', [ScraperController::class, 'review'])->name('review');
            Route::post('/{filename}/create-products', [ScraperController::class, 'createProducts'])->name('create-products');
            Route::delete('/{filename}', [ScraperController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('suppliers')->name('suppliers.')->group(function () {
            Route::get('/', [SupplierController::class, 'index'])->name('index');
            Route::get('/create', [SupplierController::class, 'create'])->name('create');
            Route::post('/', [SupplierController::class, 'store'])->name('store');
            Route::get('/{supplier}/edit', [SupplierController::class, 'edit'])->name('edit');
            Route::put('/{supplier}', [SupplierController::class, 'update'])->name('update');
            Route::delete('/{supplier}', [SupplierController::class, 'destroy'])->name('destroy');
            Route::post('/{supplier}/toggle', [SupplierController::class, 'toggle'])->name('toggle');
        });

        Route::prefix('margins')->name('margins.')->group(function () {
            Route::get('/', [MarginController::class, 'index'])->name('index');
            Route::post('/global', [MarginController::class, 'storeGlobal'])->name('global.store');
            Route::post('/product', [MarginController::class, 'storeProduct'])->name('product.store');
            Route::put('/{margin}', [MarginController::class, 'update'])->name('update');
            Route::delete('/{margin}', [MarginController::class, 'destroy'])->name('destroy');
            Route::post('/{margin}/toggle', [MarginController::class, 'toggle'])->name('toggle');
            Route::post('/recalculate', [MarginController::class, 'recalculate'])->name('recalculate');
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

        Route::prefix('djomy')->name('djomy.')->group(function () {
            // Paiement d'une échéance (commandes crédit)
            Route::get('/checkout/{installment}',  [DjomyController::class, 'checkout'])->name('checkout');
            Route::post('/checkout/{installment}', [DjomyController::class, 'initiateCheckout'])->name('checkout.initiate');
            // Paiement d'une commande comptant
            Route::get('/order-checkout/{order}',  [DjomyController::class, 'checkoutOrder'])->name('order.checkout');
            Route::post('/order-checkout/{order}', [DjomyController::class, 'initiateOrderCheckout'])->name('order.checkout.initiate');
            // Retour après paiement
            Route::get('/return',        [DjomyController::class, 'return'])->name('return');
            Route::get('/cancel',        [DjomyController::class, 'cancel'])->name('cancel');
            Route::get('/check-status',  [DjomyController::class, 'checkStatus'])->name('check-status');
        });

        Route::get('/profile', [PortalProfileController::class, 'show'])->name('profile');
        Route::put('/profile', [PortalProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [PortalProfileController::class, 'changePassword'])->name('profile.password');
    });
});


// Djomy webhook — public, no auth required, CSRF excluded in bootstrap/app.php
Route::post('/djomy/webhook', [DjomyController::class, 'webhook'])->name('djomy.webhook');

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
