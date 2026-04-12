@extends('tpl.admin')

@section('content')
<div class="content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Détails du Rôle</h1>
            <div class="page-breadcrumb">
                <span>CAURISHOP</span>
                <i class="fas fa-chevron-right"></i>
                <a href="{{ route('admin.roles.index') }}">Rôles</a>
                <i class="fas fa-chevron-right"></i>
                <span>{{ ucfirst($role->name) }}</span>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div style="display: flex; align-items: center; gap: 1.5rem; margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 1px solid var(--border);">
                <div style="width: 70px; height: 70px; background: var(--primary); color: var(--white); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem;">
                    <i class="fas fa-user-tag"></i>
                </div>
                <div>
                    <h2 style="margin-bottom: 0.35rem;">{{ ucfirst($role->name) }}</h2>
                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                        @if(in_array($role->name, ['admin', 'employee']))
                        <span class="badge warning">Rôle Système</span>
                        @endif
                        <span class="badge primary">{{ $role->permissions->count() }} permission(s)</span>
                        <span class="badge" style="background: var(--light); color: var(--dark);">
                            {{ $role->users->count() }} utilisateur(s)
                        </span>
                    </div>
                </div>
            </div>

            {{-- Permissions groupées --}}
            @if($role->permissions->count() > 0)
            @php
                $grouped = [];
                foreach ($role->permissions as $perm) {
                    $parts = explode('.', $perm->name);
                    $module = $parts[0] ?? 'other';
                    $grouped[$module][] = $perm;
                }
                ksort($grouped);
            @endphp
            <div style="margin-bottom: 2rem;">
                <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1.25rem;">Permissions attribuées</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 1.25rem;">
                    @foreach($grouped as $module => $perms)
                    <div style="background: var(--light); border-radius: 8px; padding: 1rem;">
                        <div style="font-weight: 700; font-size: 0.85rem; text-transform: uppercase; color: var(--primary); margin-bottom: 0.75rem;">
                            <i class="fas fa-layer-group"></i> {{ ucfirst($module) }}
                        </div>
                        <div style="display: flex; flex-wrap: wrap; gap: 0.4rem;">
                            @foreach($perms as $perm)
                            <span class="badge" style="background: var(--white); color: var(--dark); font-size: 0.8rem; border: 1px solid var(--border);">
                                <i class="fas fa-check" style="color: var(--success); margin-right: 0.25rem;"></i>
                                {{ explode('.', $perm->name)[1] ?? $perm->name }}
                            </span>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @else
            <div style="color: var(--gray); font-style: italic; margin-bottom: 2rem;">Aucune permission attribuée.</div>
            @endif

            {{-- Utilisateurs --}}
            @if($role->users->count() > 0)
            <div style="padding-top: 2rem; border-top: 1px solid var(--border);">
                <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">Utilisateurs avec ce rôle</h3>
                <div style="display: flex; flex-wrap: wrap; gap: 0.75rem;">
                    @foreach($role->users as $user)
                    <a href="{{ route('admin.users.show', $user) }}" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0.75rem; background: var(--light); border-radius: 6px; text-decoration: none; color: var(--dark);">
                        <div style="width: 30px; height: 30px; background: var(--primary); color: var(--white); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700;">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                        <span style="font-size: 0.9rem;">{{ $user->name }}</span>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <div class="card-footer" style="display: flex; gap: 1rem; justify-content: flex-end;">
            <a href="{{ route('admin.roles.index') }}" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i>
                Retour
            </a>
            <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i>
                Modifier
            </a>
        </div>
    </div>
</div>
@endsection
