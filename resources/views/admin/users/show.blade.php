@extends('tpl.admin')

@section('content')
<div class="content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Détails de l'Utilisateur</h1>
            <div class="page-breadcrumb">
                <span>CAURISHOP</span>
                <i class="fas fa-chevron-right"></i>
                <a href="{{ route('admin.users.index') }}">Utilisateurs</a>
                <i class="fas fa-chevron-right"></i>
                <span>Détails</span>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div style="display: flex; align-items: start; gap: 1.5rem; margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 1px solid var(--border);">
                <div style="width: 80px; height: 80px; background: var(--primary); color: var(--white); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 2rem;">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <div style="flex: 1;">
                    <h2 style="margin-bottom: 0.5rem;">{{ $user->name }}</h2>
                    <p style="color: var(--gray); margin-bottom: 0.75rem;">
                        <i class="fas fa-envelope" style="margin-right: 0.5rem;"></i>
                        {{ $user->email }}
                    </p>
                    <div style="display: flex; gap: 0.5rem;">
                        @if($user->is_active)
                        <span class="badge success">Actif</span>
                        @else
                        <span class="badge danger">Suspendu</span>
                        @endif

                        @if($user->email_verified_at)
                        <span class="badge success">Email Vérifié</span>
                        @else
                        <span class="badge warning">Email Non Vérifié</span>
                        @endif

                        @forelse($user->roles as $role)
                        <span class="badge primary">{{ ucfirst($role->name) }}</span>
                        @empty
                        <span class="badge" style="background: var(--light); color: var(--gray);">Aucun rôle</span>
                        @endforelse
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; margin-bottom: 2rem;">
                <div>
                    <div style="font-size: 0.85rem; color: var(--gray); margin-bottom: 0.5rem;">Date de création</div>
                    <div style="font-weight: 600; font-size: 1.1rem;">{{ $user->created_at->format('d/m/Y') }}</div>
                    <div style="font-size: 0.85rem; color: var(--gray);">{{ $user->created_at->format('H:i') }}</div>
                </div>
                <div>
                    <div style="font-size: 0.85rem; color: var(--gray); margin-bottom: 0.5rem;">Dernière modification</div>
                    <div style="font-weight: 600; font-size: 1.1rem;">{{ $user->updated_at->format('d/m/Y') }}</div>
                    <div style="font-size: 0.85rem; color: var(--gray);">{{ $user->updated_at->format('H:i') }}</div>
                </div>
                @if($user->email_verified_at)
                <div>
                    <div style="font-size: 0.85rem; color: var(--gray); margin-bottom: 0.5rem;">Email vérifié le</div>
                    <div style="font-weight: 600; font-size: 1.1rem;">{{ $user->email_verified_at->format('d/m/Y') }}</div>
                    <div style="font-size: 0.85rem; color: var(--gray);">{{ $user->email_verified_at->format('H:i') }}</div>
                </div>
                @endif
            </div>

            @if($user->getAllPermissions()->count() > 0)
            <div style="padding-top: 2rem; border-top: 1px solid var(--border);">
                <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">Permissions</h3>
                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                    @foreach($user->getAllPermissions() as $permission)
                    <span class="badge" style="background: var(--light); color: var(--primary);">
                        <i class="fas fa-key"></i>
                        {{ $permission->name }}
                    </span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <div class="card-footer" style="display: flex; gap: 1rem; justify-content: flex-end;">
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i>
                Retour
            </a>

            @if(!$user->email_verified_at)
            <a href="{{ route('admin.users.verify', $user) }}" class="btn btn-outline" style="color: var(--success);" onclick="event.preventDefault(); document.getElementById('verify-form').submit();">
                <i class="fas fa-check-circle"></i>
                Vérifier l'Email
            </a>
            <form id="verify-form" action="{{ route('admin.users.verify', $user) }}" method="POST" style="display: none;">
                @csrf
            </form>
            @endif

            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i>
                Modifier
            </a>

            @if($user->id !== auth()->id())
            @if($user->is_active)
            <a href="{{ route('admin.users.suspend', $user) }}" class="btn btn-outline" style="color: var(--warning);" onclick="event.preventDefault(); if(confirm('Suspendre cet utilisateur ?')) document.getElementById('suspend-form').submit();">
                <i class="fas fa-ban"></i>
                Suspendre
            </a>
            <form id="suspend-form" action="{{ route('admin.users.suspend', $user) }}" method="POST" style="display: none;">
                @csrf
            </form>
            @else
            <a href="{{ route('admin.users.activate', $user) }}" class="btn btn-outline" style="color: var(--success);" onclick="event.preventDefault(); document.getElementById('activate-form').submit();">
                <i class="fas fa-check"></i>
                Activer
            </a>
            <form id="activate-form" action="{{ route('admin.users.activate', $user) }}" method="POST" style="display: none;">
                @csrf
            </form>
            @endif

            <a href="{{ route('admin.users.destroy', $user) }}" class="btn btn-outline" style="color: var(--danger);" onclick="event.preventDefault(); if(confirm('Supprimer définitivement cet utilisateur ?')) document.getElementById('delete-form').submit();">
                <i class="fas fa-trash"></i>
                Supprimer
            </a>
            <form id="delete-form" action="{{ route('admin.users.destroy', $user) }}" method="POST" style="display: none;">
                @csrf
                @method('DELETE')
            </form>
            @endif
        </div>
    </div>
</div>
@endsection
