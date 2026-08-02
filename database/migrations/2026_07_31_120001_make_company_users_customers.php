<?php

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

/**
 * Les salariés et administrateurs d'entreprise sont des clients comme les autres :
 * seule leur liaison à une entreprise (et donc l'accès au crédit) les distingue.
 * On leur ajoute le rôle « customer » et la fiche client correspondante,
 * sans toucher à leurs rôles existants.
 */
return new class extends Migration {
    public function up(): void
    {
        $users = User::whereHas('roles', fn ($q) => $q->whereIn('name', ['company_admin', 'company_employee']))->get();

        foreach ($users as $user) {
            if (! $user->hasRole('customer')) {
                $user->assignRole('customer');
            }

            if ($user->customer) {
                continue;
            }

            $parts = preg_split('/\s+/', trim((string) $user->name), 2);

            Customer::create([
                'user_id'    => $user->id,
                'type'       => Customer::TYPE_COMPANY,
                'company_id' => $user->company_id,
                'first_name' => $parts[0] ?? $user->name,
                'last_name'  => $parts[1] ?? '',
                'email'      => $user->email,
                'is_active'  => true,
            ]);
        }
    }

    public function down(): void
    {
        // On retire uniquement le rôle ajouté : les fiches clients créées sont
        // conservées, elles peuvent déjà porter des commandes.
        User::whereHas('roles', fn ($q) => $q->whereIn('name', ['company_admin', 'company_employee']))
            ->get()
            ->each(fn (User $user) => $user->removeRole('customer'));
    }
};
