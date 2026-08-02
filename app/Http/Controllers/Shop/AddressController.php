<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Carnet d'adresses de livraison de l'espace client.
 */
class AddressController extends Controller
{
    /** Villes / préfectures proposées, alignées sur le checkout. */
    public const CITIES = ['Conakry', 'Kindia', 'Boké', 'Labé', 'Mamou', 'Faranah', 'Kankan', "N'Zérékoré"];

    public function index(): View
    {
        $customer = $this->customer();

        return view('shop.account.addresses', [
            'customer'  => $customer,
            'addresses' => $customer ? $customer->addresses()->get() : collect(),
            'cities'    => self::CITIES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $customer = $this->customer();
        abort_unless($customer, 403);

        $data = $this->validated($request);

        // Première adresse du carnet : elle devient l'adresse par défaut.
        $isFirst = $customer->addresses()->count() === 0;

        $address = $customer->addresses()->create($data + ['is_default' => $isFirst || $data['is_default']]);

        if ($address->is_default) {
            $this->clearOtherDefaults($customer, $address->id);
        }

        return redirect()->route('shop.account.addresses')->with('success', 'Adresse ajoutée.');
    }

    public function update(Request $request, CustomerAddress $address): RedirectResponse
    {
        $this->authorizeAddress($address);

        $data = $this->validated($request);
        $address->update($data);

        if ($address->is_default) {
            $this->clearOtherDefaults($address->customer, $address->id);
        }

        return redirect()->route('shop.account.addresses')->with('success', 'Adresse mise à jour.');
    }

    public function setDefault(CustomerAddress $address): RedirectResponse
    {
        $this->authorizeAddress($address);

        $address->update(['is_default' => true]);
        $this->clearOtherDefaults($address->customer, $address->id);

        return redirect()->route('shop.account.addresses')->with('success', 'Adresse par défaut mise à jour.');
    }

    public function destroy(CustomerAddress $address): RedirectResponse
    {
        $this->authorizeAddress($address);

        $customer  = $address->customer;
        $wasDefault = $address->is_default;
        $address->delete();

        // On ne laisse jamais le carnet sans adresse par défaut.
        if ($wasDefault) {
            $customer->addresses()->first()?->update(['is_default' => true]);
        }

        return redirect()->route('shop.account.addresses')->with('success', 'Adresse supprimée.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'label'      => ['nullable', 'string', 'max:60'],
            'full_name'  => ['required', 'string', 'max:120'],
            'phone'      => ['required', 'string', 'max:30'],
            'city'       => ['required', 'string', 'max:100'],
            'address'    => ['required', 'string', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $data['is_default'] = (bool) ($data['is_default'] ?? false);

        return $data;
    }

    private function clearOtherDefaults(Customer $customer, int $keepId): void
    {
        $customer->addresses()->where('id', '!=', $keepId)->update(['is_default' => false]);
    }

    private function authorizeAddress(CustomerAddress $address): void
    {
        $customer = $this->customer();
        abort_unless($customer && $address->customer_id === $customer->id, 403);
    }

    private function customer(): ?Customer
    {
        return Auth::user()->customer;
    }
}
