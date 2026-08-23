@extends('tpl.admin')

@section('content')
@php
    $order     = $installment->creditPlan->order;
    $customer  = $order->customer;
    $remaining = max(0, (float)$installment->amount_due - (float)$installment->amount_paid);
@endphp
<div class="content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Enregistrer un paiement</h1>
            <div class="page-breadcrumb">
                <span>CAURISHOP</span>
                <i class="fas fa-chevron-right"></i>
                <a href="{{ route('admin.installments.index') }}">Échéanciers</a>
                <i class="fas fa-chevron-right"></i>
                <span>Paiement</span>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">

        {{-- Formulaire --}}
        <div class="card">
            <div class="card-header"><h3 class="card-title">Détails du paiement</h3></div>
            <div class="card-body">
                <form action="{{ route('admin.installments.record-payment', $installment) }}" method="POST">
                    @csrf
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                        <div class="form-group">
                            <label class="form-label">Montant (GNF) <span style="color: var(--danger);">*</span></label>
                            <input type="number" name="amount" class="form-input"
                                value="{{ old('amount', number_format($remaining, 2, '.', '')) }}"
                                min="0.01" max="{{ $remaining }}" step="0.01" required>
                            <div style="font-size: 0.8rem; color: var(--gray); margin-top: 0.25rem;">
                                Reste dû : {{ number_format($remaining, 0, ',', ' ') }} GNF
                            </div>
                            @error('amount')
                            <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Date de paiement <span style="color: var(--danger);">*</span></label>
                            <input type="date" name="payment_date" class="form-input"
                                value="{{ old('payment_date', date('Y-m-d')) }}" required>
                            @error('payment_date')
                            <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Méthode <span style="color: var(--danger);">*</span></label>
                            <select name="method" class="form-input" required>
                                <option value="cash"         {{ old('method', 'cash') === 'cash'         ? 'selected' : '' }}>Espèces</option>
                                <option value="transfer"     {{ old('method') === 'transfer'     ? 'selected' : '' }}>Virement</option>
                                <option value="mobile_money" {{ old('method') === 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                                <option value="card"         {{ old('method') === 'card'         ? 'selected' : '' }}>Carte</option>
                                <option value="other"        {{ old('method') === 'other'        ? 'selected' : '' }}>Autre</option>
                            </select>
                            @error('method')
                            <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Référence <span style="font-weight: 400; color: var(--gray);">(optionnel)</span></label>
                            <input type="text" name="reference" class="form-input"
                                placeholder="N° de transaction, reçu..." value="{{ old('reference') }}">
                            @error('reference')
                            <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div style="display: flex; gap: 0.75rem; margin-top: 0.5rem;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check"></i> Confirmer le paiement
                        </button>
                        <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-outline">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Récapitulatif --}}
        <div>
            <div class="card">
                <div class="card-header"><h3 class="card-title">Récapitulatif</h3></div>
                <div class="card-body">
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <div>
                            <div style="font-size: 0.8rem; color: var(--gray);">Client</div>
                            <div style="font-weight: 600;">{{ $customer->displayName() }}</div>
                            @if($customer->company)
                            <div style="font-size: 0.85rem; color: var(--gray);">{{ $customer->company->name }}</div>
                            @endif
                        </div>
                        <div style="border-top: 1px solid var(--border); padding-top: 0.75rem;">
                            <div style="font-size: 0.8rem; color: var(--gray);">Commande</div>
                            <div style="font-weight: 600; color: var(--primary);">{{ $order->order_number }}</div>
                        </div>
                        <div style="border-top: 1px solid var(--border); padding-top: 0.75rem; display: flex; flex-direction: column; gap: 0.5rem;">
                            <div style="display: flex; justify-content: space-between;">
                                <span style="font-size: 0.9rem; color: var(--gray);">Échéance n°</span>
                                <span style="font-weight: 600;">{{ $installment->installment_number }}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="font-size: 0.9rem; color: var(--gray);">Date d'échéance</span>
                                <span style="font-weight: 600; {{ $installment->status === 'late' ? 'color: var(--danger);' : '' }}">
                                    {{ $installment->due_date->format('d/m/Y') }}
                                </span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="font-size: 0.9rem; color: var(--gray);">Montant dû</span>
                                <span style="font-weight: 600;">{{ number_format($installment->amount_due, 0, ',', ' ') }} GNF</span>
                            </div>
                            @if($installment->amount_paid > 0)
                            <div style="display: flex; justify-content: space-between;">
                                <span style="font-size: 0.9rem; color: var(--gray);">Déjà payé</span>
                                <span style="font-weight: 600; color: var(--success);">{{ number_format($installment->amount_paid, 0, ',', ' ') }} GNF</span>
                            </div>
                            @endif
                            <div style="display: flex; justify-content: space-between; padding-top: 0.5rem; border-top: 1px solid var(--border);">
                                <span style="font-size: 0.9rem; color: var(--gray);">Reste à payer</span>
                                <span style="font-weight: 700; font-size: 1.1rem; color: var(--primary);">
                                    {{ number_format($remaining, 0, ',', ' ') }} GNF
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
