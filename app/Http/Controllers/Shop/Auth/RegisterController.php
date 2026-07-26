<?php

namespace App\Http\Controllers\Shop\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use App\Services\Admin\ActivityLogService;
use App\Services\Shop\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('shop.auth.register');
    }

    public function store(Request $request, CartService $cart): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'phone'      => ['required', 'string', 'max:30', 'unique:customers,phone'],
            'email'      => ['nullable', 'email:rfc', 'unique:users,email'],
            'password'   => ['required', 'string', 'min:8'],
        ], [
            'phone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
        ]);

        // Un e-mail est requis par l'auth Laravel. Si le client n'en fournit pas,
        // on synthétise un identifiant technique à partir du téléphone.
        $email      = $data['email'] ?? null;
        $loginEmail = $email
            ?: preg_replace('/\D+/', '', $data['phone']) . '_' . Str::lower(Str::random(4)) . '@phone.caurishop.local';

        $user = User::create([
            'name'      => trim($data['first_name'] . ' ' . $data['last_name']),
            'email'     => $loginEmail,
            'password'  => Hash::make($data['password']),
            'is_active' => true,
        ]);

        // Garantit l'existence du rôle (évite l'échec si le seeder n'a pas tourné en prod).
        \Spatie\Permission\Models\Role::findOrCreate('customer', 'web');
        $user->assignRole('customer');

        Customer::create([
            'user_id'    => $user->id,
            'type'       => Customer::TYPE_INDIVIDUAL,
            'company_id' => null,
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            'email'      => $email,
            'phone'      => $data['phone'],
            'is_active'  => true,
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        $cart->mergeSessionIntoUser();

        app(ActivityLogService::class)->log('register', 'Inscription client — ' . $user->name, $user->id);

        return redirect()->intended(route('shop.account.index'))
            ->with('success', 'Bienvenue ' . $data['first_name'] . ' ! Votre compte a été créé.');
    }
}
