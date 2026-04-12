@extends('tpl.admin')

@section('content')
<div class="content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Permissions Système</h1>
            <div class="page-breadcrumb">
                <span>CAURISHOP</span>
                <i class="fas fa-chevron-right"></i>
                <span>Permissions</span>
            </div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue">
                <i class="fas fa-key"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Total Permissions</div>
                <div class="stat-value">{{ $total }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange">
                <i class="fas fa-layer-group"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Modules</div>
                <div class="stat-value">{{ count($permissionsGrouped) }}</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Permissions par module</h3>
            <div style="font-size: 0.85rem; color: var(--gray);">
                Les permissions sont gérées via le seeder. Pour modifier, éditez les rôles.
            </div>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
                @foreach($permissionsGrouped as $module => $permissions)
                <div style="background: var(--light); border-radius: 10px; padding: 1.25rem; border: 1px solid var(--border);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                        <div style="font-weight: 700; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--primary); display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-layer-group"></i>
                            {{ ucfirst($module) }}
                        </div>
                        <span class="badge primary" style="font-size: 0.75rem;">{{ count($permissions) }}</span>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 0.35rem;">
                        @foreach($permissions as $permission)
                        <div style="display: flex; align-items: center; gap: 0.5rem; padding: 0.4rem 0.6rem; background: var(--white); border-radius: 5px; border: 1px solid var(--border);">
                            <i class="fas fa-key" style="color: var(--primary); font-size: 0.75rem; flex-shrink: 0;"></i>
                            <span style="font-size: 0.85rem; font-family: monospace;">{{ $permission->name }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-header">
            <h3 class="card-title">Actions rapides</h3>
        </div>
        <div class="card-body">
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <a href="{{ route('admin.roles.index') }}" class="btn btn-primary">
                    <i class="fas fa-user-tag"></i>
                    Gérer les Rôles
                </a>
                <a href="{{ route('admin.roles.create') }}" class="btn btn-outline">
                    <i class="fas fa-plus"></i>
                    Créer un Rôle
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
