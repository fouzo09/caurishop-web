@extends('tpl.admin')

@section('content')
<div class="content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Nouveau fournisseur</h1>
            <div class="page-breadcrumb">
                <span>CAURISHOP</span>
                <i class="fas fa-chevron-right"></i>
                <a href="{{ route('admin.suppliers.index') }}">Fournisseurs</a>
                <i class="fas fa-chevron-right"></i>
                <span>Nouveau</span>
            </div>
        </div>
        <a href="{{ route('admin.suppliers.index') }}" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>

    <div class="card" style="max-width:680px;">
        <div class="card-body">
            @if($errors->any())
            <div class="alert danger" style="margin-bottom:1.25rem;">
                @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
            </div>
            @endif

            <form action="{{ route('admin.suppliers.store') }}" method="POST">
                @csrf

                <div style="margin-bottom:1rem;">
                    <label class="form-label">Nom <span style="color:#dc2626;">*</span></label>
                    <input type="text" name="name" class="form-input"
                           value="{{ old('name') }}" required autofocus>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
                    <div>
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-input" value="{{ old('email') }}">
                    </div>
                    <div>
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="phone" class="form-input" value="{{ old('phone') }}">
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
                    <div>
                        <label class="form-label">Pays</label>
                        <input type="text" name="country" class="form-input" value="{{ old('country') }}"
                               placeholder="Ex: Chine, Turquie, USA…">
                    </div>
                    <div>
                        <label class="form-label">Site web</label>
                        <input type="url" name="website" class="form-input" value="{{ old('website') }}"
                               placeholder="https://…">
                    </div>
                </div>

                <div style="margin-bottom:1.5rem;">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-input" rows="3"
                              placeholder="Conditions de paiement, délais de livraison…">{{ old('notes') }}</textarea>
                </div>

                <div style="display:flex;justify-content:flex-end;gap:.75rem;">
                    <a href="{{ route('admin.suppliers.index') }}" class="btn btn-outline">Annuler</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
