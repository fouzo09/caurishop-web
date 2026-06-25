<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Services\Admin\ScraperService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class ScraperController extends Controller
{
    public function __construct(
        protected ScraperService $scraperService
    ) {}

    public function index(): View
    {
        $files = $this->scraperService->listFiles();
        return view('admin.scraper.index', compact('files'));
    }

    public function runHtml(Request $request): RedirectResponse
    {
        $request->validate([
            'html' => 'required|string|min:100',
        ], [
            'html.required' => 'Le code HTML est obligatoire.',
            'html.min'      => 'Le HTML collé semble trop court.',
        ]);

        try {
            $filename = $this->scraperService->runFromHtml(
                $request->input('html'),
                '',
                ''
            );

            return redirect()->route('admin.scraper.show', $filename)
                ->with('success', 'Extraction terminée.');
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', 'Erreur : ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(string $filename): View|RedirectResponse
    {
        $data      = $this->scraperService->readFile($filename);
        $suppliers = Supplier::active()->orderBy('name')->get();

        if (!$data) {
            return redirect()->route('admin.scraper.index')
                ->with('error', 'Fichier introuvable.');
        }

        return view('admin.scraper.show', compact('data', 'filename', 'suppliers'));
    }

    public function review(string $filename, Request $request): View|RedirectResponse
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'product_ids' => 'required|array|min:1',
        ], [
            'supplier_id.required' => 'Sélectionnez un fournisseur.',
            'supplier_id.exists'   => 'Fournisseur invalide.',
            'product_ids.required' => 'Sélectionnez au moins un produit.',
            'product_ids.min'      => 'Sélectionnez au moins un produit.',
        ]);

        $data = $this->scraperService->readFile($filename);
        if (!$data) {
            return redirect()->route('admin.scraper.index')
                ->with('error', 'Fichier introuvable.');
        }

        $supplier    = Supplier::findOrFail($request->input('supplier_id'));
        $selectedIds = $request->input('product_ids');
        $selected    = array_filter(
            $data['products'],
            fn($p) => in_array($p['id'], $selectedIds)
        );

        $groups = $this->scraperService->groupProducts(array_values($selected));

        return view('admin.scraper.review', compact('groups', 'supplier', 'filename'));
    }

    public function createProducts(string $filename, Request $request): RedirectResponse
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'groups'      => 'required|array|min:1',
        ], [
            'supplier_id.required' => 'Le fournisseur est obligatoire.',
            'groups.required'      => 'Aucun groupe de produits reçu.',
        ]);

        try {
            $supplier = Supplier::findOrFail($request->input('supplier_id'));
            $count    = $this->scraperService->createProductGroups(
                $request->input('groups'),
                $supplier
            );

            return redirect()->route('admin.products.index')
                ->with('success', "{$count} produit(s) créé(s) avec succès.");
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    public function destroy(string $filename): RedirectResponse
    {
        $this->scraperService->deleteFile($filename);

        return redirect()->route('admin.scraper.index')
            ->with('success', 'Fichier supprimé.');
    }

    public function imageProxy(Request $request): Response
    {
        $url = $request->query('url', '');

        if (!$url || !preg_match('#^https?://#i', $url)) {
            abort(400);
        }

        // Fix malformed URLs like https://site.com//cdn.other.com/path → https://cdn.other.com/path
        // These are generated when a protocol-relative URL (//cdn.other.com/...) is incorrectly
        // prefixed with the page host instead of just "https:".
        if (preg_match('#^https?://[^/]+//([a-zA-Z0-9][^/]*/\S*)$#', $url, $m)) {
            $url = 'https://' . $m[1];
        }

        try {
            $response = Http::withOptions(['verify' => false, 'allow_redirects' => true])
                ->timeout(10)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/124.0.0.0 Safari/537.36'])
                ->get($url);

            if (!$response->successful()) {
                abort(404);
            }

            $contentType = $response->header('Content-Type') ?? 'image/jpeg';

            // Strip charset/params from content-type (e.g. "image/jpeg; charset=utf-8")
            $mimeType = trim(explode(';', $contentType)[0]);

            if (!str_starts_with($mimeType, 'image/')) {
                abort(400);
            }

            return response($response->body(), 200)
                ->header('Content-Type', $mimeType)
                ->header('Cache-Control', 'public, max-age=3600');
        } catch (\Throwable) {
            abort(404);
        }
    }
}
