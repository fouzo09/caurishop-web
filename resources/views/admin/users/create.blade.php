@extends('tpl.admin')

@section('content')
<div class="content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Créer un Utilisateur</h1>
            <div class="page-breadcrumb">
                <span>CAURISHOP</span>
                <i class="fas fa-chevron-right"></i>
                <a href="{{ route('admin.users.index') }}">Utilisateurs</a>
                <i class="fas fa-chevron-right"></i>
                <span>Nouveau</span>
            </div>
        </div>
    </div>

    @if($errors->any())
    <div class="alert danger">
        <i class="fas fa-exclamation-triangle"></i>
        <span>Veuillez corriger les erreurs dans le formulaire</span>
    </div>
    @endif

    <form action="{{ route('admin.users.store') }}" method="POST">
        @csrf

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Informations de l'Utilisateur</h3>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="form-group">
                        <label class="form-label">Nom complet</label>
                        <input type="text" name="name" class="form-input" placeholder="Prénom NOM" value="{{ old('name') }}" required>
                        @error('name')
                        <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-input" placeholder="email@exemple.com" value="{{ old('email') }}" required>
                        @error('email')
                        <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Mot de passe</label>
                        <input type="password" name="password" class="form-input" placeholder="••••••••" required>
                        @error('password')
                        <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Confirmation mot de passe</label>
                        <input type="password" name="password_confirmation" class="form-input" placeholder="••••••••" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Rôles</label>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 0.75rem;">
                        @foreach($roles as $role)
                        <label style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem; background: var(--light); border-radius: 8px; cursor: pointer;">
                            <input type="checkbox" name="roles[]" value="{{ $role->id }}" {{ in_array($role->id, old('roles', [])) ? 'checked' : '' }}>
                            <span style="font-weight: 600;">{{ ucfirst($role->name) }}</span>
                        </label>
                        @endforeach
                    </div>
                    @error('roles')
                    <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                        <span>Utilisateur actif</span>
                    </label>
                </div>
            </div>
            <div class="card-footer" style="display: flex; gap: 1rem; justify-content: flex-end;">
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline">
                    <i class="fas fa-times"></i>
                    Annuler
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Créer
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
