<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Customer;
use App\Models\CustomerAddress;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Carnet d'adresses de livraison de l'espace client.
 * Une adresse = ville (référentiel `cities`) + quartier + précision.
 */
class AddressController extends Controller
{
    /** Villes proposées au client, partagées avec le checkout. */
    public static function cities(): Collection
    {
        return City::active()->ordered()->get();
    }

    public function index(): View
    {
        $customer = $this->customer();

        return view('shop.account.addresses', [
            'customer'  => $customer,
            'addresses' => $customer ? $customer->addresses()->with('city')->get() : collect(),
            'cities'    => self::cities(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $customer = $this->customer();
        abort_unless($customer, 403);

        $data = $this->validated($request);

        // Première adresse du carnet : elle devient l'adresse par défaut.
        $isFirst = $customer->addresses()->count() === 0;

        // array_merge et non `+` : `$data` porte déjà la clé is_default, l'union
        // l'aurait gardée et la première adresse ne serait jamais par défaut.
        $address = $customer->addresses()->create(array_merge($data, [
            'is_default' => $isFirst || $data['is_default'],
        ]));

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
            'city_id'    => ['required', 'integer', 'exists:cities,id'],
            'quartier'   => ['required', 'string', 'max:120'],
            'precision'  => ['nullable', 'string', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
        ], [], [
            'city_id'   => 'ville',
            'quartier'  => 'quartier',
            'precision' => 'précision',
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
