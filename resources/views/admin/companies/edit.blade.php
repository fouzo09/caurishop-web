@extends('tpl.admin')

@section('content')
<div class="content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Modifier l'Entreprise</h1>
            <div class="page-breadcrumb">
                <span>CAURISHOP</span>
                <i class="fas fa-chevron-right"></i>
                <a href="{{ route('admin.companies.index') }}">Entreprises</a>
                <i class="fas fa-chevron-right"></i>
                <span>Modifier</span>
            </div>
        </div>
    </div>

    @if($errors->any())
    <div class="alert danger"><i class="fas fa-exclamation-triangle"></i><span>Veuillez corriger les erreurs dans le formulaire</span></div>
    @endif

    <form action="{{ route('admin.companies.update', $company) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card">
            <div class="card-header"><h3 class="card-title">Informations de l'Entreprise</h3></div>
            <div class="card-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">

                    <div class="form-group" style="grid-column:span 2;">
                        <label class="form-label">Raison sociale</label>
                        <input type="text" name="raison_sociale" class="form-input" placeholder="Diallo & Frères SARL" value="{{ old('raison_sociale', $company->raison_sociale) }}" required>
                        @error('raison_sociale')<span style="color:var(--danger);font-size:.85rem;">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Numéro d'enregistrement (RCCM)</label>
                        <input type="text" name="registration_number" class="form-input" placeholder="RC-2024-001" value="{{ old('registration_number', $company->registration_number) }}" required>
                        @error('registration_number')<span style="color:var(--danger);font-size:.85rem;">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-input" placeholder="contact@entreprise.com" value="{{ old('email', $company->email) }}" required>
                        @error('email')<span style="color:var(--danger);font-size:.85rem;">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="phone" class="form-input" placeholder="+224 XXX XX XX XX" value="{{ old('phone', $company->phone) }}" required>
                        @error('phone')<span style="color:var(--danger);font-size:.85rem;">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Limite de crédit (GNF)</label>
                        <input type="number" name="credit_limit" class="form-input" placeholder="10000000" value="{{ old('credit_limit', $company->credit_limit) }}" step="0.01" min="0" required>
                        @error('credit_limit')<span style="color:var(--danger);font-size:.85rem;">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group" style="grid-column:span 2;">
                        <label class="form-label">Adresse</label>
                        <input type="text" name="address" class="form-input" placeholder="Quartier, Rue, Immeuble" value="{{ old('address', $company->address) }}" required>
                        @error('address')<span style="color:var(--danger);font-size:.85rem;">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Ville</label>
                        <input type="text" name="city" class="form-input" placeholder="Conakry" value="{{ old('city', $company->city) }}" required>
                        @error('city')<span style="color:var(--danger);font-size:.85rem;">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Pays</label>
                        <input type="text" name="country" class="form-input" placeholder="Guinée" value="{{ old('country', $company->country) }}" required>
                        @error('country')<span style="color:var(--danger);font-size:.85rem;">{{ $message }}</span>@enderror
                    </div>

                </div>

                <div class="form-group" style="margin-bottom:0;margin-top:1rem;">
                    <label style="display:flex;align-items:center;gap:.5rem;">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $company->is_active) ? 'checked' : '' }}>
                        <span>Entreprise active</span>
                    </label>
                </div>
            </div>
            <div class="card-footer" style="display:flex;gap:1rem;justify-content:flex-end;">
                <a href="{{ route('admin.companies.index') }}" class="btn btn-outline"><i class="fas fa-times"></i> Annuler</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Mettre à Jour</button>
            </div>
        </div>
    </form>
</div>
@endsection
