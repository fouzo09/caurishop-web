@extends('tpl.admin')

@section('content')
<div class="content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Modifier le Client</h1>
            <div class="page-breadcrumb">
                <span>CAURISHOP</span>
                <i class="fas fa-chevron-right"></i>
                <a href="{{ route('admin.customers.index') }}">Clients</a>
                <i class="fas fa-chevron-right"></i>
                <span>Modifier</span>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.customers.update', $customer) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Informations du Client</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Type de client</label>
                    <div style="display: flex; gap: 1rem;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; background: var(--light); border-radius: 8px; cursor: pointer; border: 2px solid var(--border);" onclick="toggleCustomerType('individual')">
                            <input type="radio" name="type" value="individual" {{ old('type', $customer->type) === 'individual' ? 'checked' : '' }} required>
                            <div>
                                <div style="font-weight: 600;"><i class="fas fa-user"></i> Particulier</div>
                                <div style="font-size: 0.8rem; color: var(--gray);">Client individuel</div>
                            </div>
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; background: var(--light); border-radius: 8px; cursor: pointer; border: 2px solid var(--border);" onclick="toggleCustomerType('company')">
                            <input type="radio" name="type" value="company" {{ old('type', $customer->type) === 'company' ? 'checked' : '' }} required>
                            <div>
                                <div style="font-weight: 600;"><i class="fas fa-building"></i> Entreprise</div>
                                <div style="font-size: 0.8rem; color: var(--gray);">Client professionnel</div>
                            </div>
                        </label>
                    </div>
                    @error('type')
                    <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span>
                    @enderror
                </div>

                <div id="individual-fields" style="display: {{ old('type', $customer->type) === 'individual' ? 'block' : 'none' }};">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                        <div class="form-group">
                            <label class="form-label">Prénom</label>
                            <input type="text" name="first_name" class="form-input" placeholder="Mamadou" value="{{ old('first_name', $customer->first_name) }}">
                            @error('first_name')
                            <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Nom</label>
                            <input type="text" name="last_name" class="form-input" placeholder="Diallo" value="{{ old('last_name', $customer->last_name) }}">
                            @error('last_name')
                            <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div id="company-fields" style="display: {{ old('type', $customer->type) === 'company' ? 'block' : 'none' }};">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                        <div class="form-group">
                            <label class="form-label">Entreprise</label>
                            <select name="company_id" class="form-select">
                                <option value="">Sélectionner une entreprise</option>
                                @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ old('company_id', $customer->company_id) == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('company_id')
                            <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Nom du contact</label>
                            <input type="text" name="company_contact_name" class="form-input" placeholder="Mamadou Diallo" value="{{ old('company_contact_name', $customer->company_contact_name) }}">
                            @error('company_contact_name')
                            <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 1.5rem;">
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-input" placeholder="email@exemple.com" value="{{ old('email', $customer->email) }}" required>
                        @error('email')
                        <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="phone" class="form-input" placeholder="+224 XXX XX XX XX" value="{{ old('phone', $customer->phone) }}" required>
                        @error('phone')
                        <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group" style="grid-column: span 2;">
                        <label class="form-label">Adresse</label>
                        <input type="text" name="address" class="form-input" placeholder="Quartier, Rue, Immeuble" value="{{ old('address', $customer->address) }}" required>
                        @error('address')
                        <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $customer->is_active) ? 'checked' : '' }}>
                        <span>Client actif</span>
                    </label>
                </div>
            </div>
            <div class="card-footer" style="display: flex; gap: 1rem; justify-content: flex-end;">
                <a href="{{ route('admin.customers.index') }}" class="btn btn-outline">
                    <i class="fas fa-times"></i>
                    Annuler
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Mettre à Jour
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    function toggleCustomerType(type) {
        const individualFields = document.getElementById('individual-fields');
        const companyFields = document.getElementById('company-fields');

        if (type === 'individual') {
            individualFields.style.display = 'block';
            companyFields.style.display = 'none';
        } else {
            individualFields.style.display = 'none';
            companyFields.style.display = 'block';
        }
    }
</script>
@endsection
