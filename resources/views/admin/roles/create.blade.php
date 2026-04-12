@extends('tpl.admin')

@section('content')
<div class="content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Créer un Rôle</h1>
            <div class="page-breadcrumb">
                <span>CAURISHOP</span>
                <i class="fas fa-chevron-right"></i>
                <a href="{{ route('admin.roles.index') }}">Rôles</a>
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

    <form action="{{ route('admin.roles.store') }}" method="POST">
        @csrf

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Informations du Rôle</h3>
            </div>
            <div class="card-body">
                <div class="form-group" style="max-width: 400px;">
                    <label class="form-label">Nom du rôle</label>
                    <input type="text" name="name" class="form-input" placeholder="ex: manager" value="{{ old('name') }}" required>
                    @error('name')
                    <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span>
                    @enderror
                    <div style="font-size: 0.8rem; color: var(--gray); margin-top: 0.35rem;">
                        Utilisez des minuscules sans espaces (ex: manager, accountant).
                    </div>
                </div>
            </div>
        </div>

        <div class="card" style="margin-top: 1.5rem;">
            <div class="card-header">
                <h3 class="card-title">Permissions</h3>
                <div style="display: flex; gap: 0.5rem;">
                    <button type="button" class="btn btn-outline btn-sm" onclick="selectAll()">Tout sélectionner</button>
                    <button type="button" class="btn btn-outline btn-sm" onclick="deselectAll()">Tout désélectionner</button>
                </div>
            </div>
            <div class="card-body">
                @foreach($permissionsGrouped as $module => $permissions)
                <div style="margin-bottom: 1.5rem;">
                    <div style="font-weight: 700; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--primary); margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-layer-group"></i>
                        {{ ucfirst($module) }}
                        <button type="button" class="btn btn-outline btn-sm" style="font-size: 0.7rem; padding: 0.2rem 0.5rem;" onclick="toggleModule('{{ $module }}')">
                            Basculer
                        </button>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 0.5rem;" id="module-{{ $module }}">
                        @foreach($permissions as $permission)
                        <label style="display: flex; align-items: center; gap: 0.5rem; padding: 0.6rem 0.75rem; background: var(--light); border-radius: 6px; cursor: pointer; border: 1px solid transparent;" class="perm-label">
                            <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                   class="perm-check module-{{ $module }}"
                                {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}>
                            <span style="font-size: 0.85rem;">
                                <strong>{{ explode('.', $permission->name)[1] ?? $permission->name }}</strong>
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
            <div class="card-footer" style="display: flex; gap: 1rem; justify-content: flex-end;">
                <a href="{{ route('admin.roles.index') }}" class="btn btn-outline">
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

<script>
    function selectAll() {
        document.querySelectorAll('.perm-check').forEach(cb => cb.checked = true);
    }

    function deselectAll() {
        document.querySelectorAll('.perm-check').forEach(cb => cb.checked = false);
    }

    function toggleModule(module) {
        const checks = document.querySelectorAll('.module-' + module);
        const allChecked = Array.from(checks).every(cb => cb.checked);
        checks.forEach(cb => cb.checked = !allChecked);
    }
</script>
@endsection
