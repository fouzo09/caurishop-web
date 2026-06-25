<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformMargin;
use App\Models\Product;
use App\Services\Admin\MarginService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MarginController extends Controller
{
    public function __construct(
        protected MarginService $marginService
    ) {}

    public function index(): View
    {
        $globalMargin   = $this->marginService->getGlobalMargin();
        $productMargins = $this->marginService->getProductMargins();

        // Enrichir les marges produit avec le nom du produit
        $productIds = $productMargins->pluck('reference_id')->filter()->unique();
        $products   = Product::whereIn('id', $productIds)->pluck('name', 'id');

        return view('admin.margins.index', compact('globalMargin', 'productMargins', 'products'));
    }

    public function storeGlobal(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type'     => 'required|in:percentage,fixed',
            'value'    => 'required|numeric|min:0',
            'label'    => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $data['scope']      = PlatformMargin::SCOPE_GLOBAL;
        $data['is_active']  = $request->boolean('is_active', true);

        $existing = $this->marginService->getGlobalMargin();

        if ($existing) {
            $this->marginService->updateMargin($existing, $data);
        } else {
            $this->marginService->createMargin($data);
        }

        return redirect()->route('admin.margins.index')
            ->with('success', 'Marge globale enregistrée.');
    }

    public function storeProduct(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'reference_id' => 'required|exists:products,id',
            'type'         => 'required|in:percentage,fixed',
            'value'        => 'required|numeric|min:0',
            'label'        => 'nullable|string|max:255',
        ]);

        $data['scope']     = PlatformMargin::SCOPE_PRODUCT;
        $data['is_active'] = true;

        $this->marginService->createMargin($data);

        return redirect()->route('admin.margins.index')
            ->with('success', 'Marge produit ajoutée.');
    }

    public function update(Request $request, PlatformMargin $margin): RedirectResponse
    {
        $data = $request->validate([
            'type'     => 'required|in:percentage,fixed',
            'value'    => 'required|numeric|min:0',
            'label'    => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        $this->marginService->updateMargin($margin, $data);

        return redirect()->route('admin.margins.index')
            ->with('success', 'Marge mise à jour.');
    }

    public function destroy(PlatformMargin $margin): RedirectResponse
    {
        $this->marginService->deleteMargin($margin);

        return redirect()->route('admin.margins.index')
            ->with('success', 'Marge supprimée.');
    }

    public function toggle(PlatformMargin $margin): RedirectResponse
    {
        $this->marginService->toggleMargin($margin);

        $status = $margin->fresh()->is_active ? 'activée' : 'désactivée';

        return redirect()->route('admin.margins.index')
            ->with('success', "Marge {$status}.");
    }

    public function recalculate(): RedirectResponse
    {
        $counts = $this->marginService->recalculateAll();

        return redirect()->route('admin.margins.index')
            ->with('success', "Prix recalculés : {$counts['products']} produit(s), {$counts['variants']} variante(s).");
    }
}
