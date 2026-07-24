<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(): View
    {
        return view('shop.contact');
    }

    public function send(Request $request): RedirectResponse
    {
        $request->validate([
            'name'    => ['required', 'string', 'max:120'],
            'email'   => ['required', 'email:rfc', 'max:150'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        // Phase 1 : pas d'envoi réel (mailing à brancher plus tard).
        return redirect()->route('shop.contact')
            ->with('success', 'Merci ! Votre message a bien été envoyé, nous vous répondrons rapidement.');
    }
}
