@extends('tpl.admin')

@section('content')
<div class="content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Mon Profil</h1>
            <div class="page-breadcrumb">
                <span>CAURISHOP</span>
                <i class="fas fa-chevron-right"></i>
                <span>Profil</span>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert success" id="alert-success">
        <i class="fas fa-check-circle"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('password_success'))
    <div class="alert success" id="alert-password">
        <i class="fas fa-check-circle"></i>
        <span>{{ session('password_success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="alert danger">
        <i class="fas fa-times-circle"></i>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <div style="display: grid; grid-template-columns: 280px 1fr; gap: 1.5rem; align-items: start;">

        {{-- Carte identité --}}
        <div class="card">
            <div class="card-body" style="text-align: center; padding: 2rem 1.5rem;">
                <div style="width: 90px; height: 90px; background: linear-gradient(135deg, var(--primary), #6366F1); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 2rem; margin: 0 auto 1.25rem;">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <div style="font-weight: 700; font-size: 1.1rem; margin-bottom: 0.25rem;">{{ $user->name }}</div>
                <div style="font-size: 0.85rem; color: var(--gray); margin-bottom: 1rem;">{{ $user->email }}</div>
                <div style="display: flex; flex-wrap: wrap; gap: 0.4rem; justify-content: center; margin-bottom: 1.25rem;">
                    @forelse($user->roles as $role)
                    <span class="badge primary">{{ ucfirst($role->name) }}</span>
                    @empty
                    <span class="badge" style="background: var(--light); color: var(--gray);">Aucun rôle</span>
                    @endforelse
                </div>
                <div style="display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.85rem; text-align: left;">
                    <div style="display: flex; align-items: center; gap: 0.6rem; color: var(--gray);">
                        <i class="fas fa-calendar" style="width: 16px;"></i>
                        Membre depuis {{ $user->created_at->format('d/m/Y') }}
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.6rem;">
                        @if($user->is_active)
                        <i class="fas fa-circle" style="width: 16px; color: var(--success); font-size: 0.6rem;"></i>
                        <span style="color: var(--success);">Compte actif</span>
                        @else
                        <i class="fas fa-circle" style="width: 16px; color: var(--danger); font-size: 0.6rem;"></i>
                        <span style="color: var(--danger);">Compte suspendu</span>
                        @endif
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.6rem; color: var(--gray);">
                        @if($user->email_verified_at)
                        <i class="fas fa-check-circle" style="width: 16px; color: var(--success);"></i>
                        <span style="color: var(--success);">Email vérifié</span>
                        @else
                        <i class="fas fa-exclamation-circle" style="width: 16px; color: var(--warning);"></i>
                        <span style="color: var(--warning);">Email non vérifié</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Onglets --}}
        <div>
            <div class="profile-tabs">
                <button class="profile-tab active" onclick="switchTab('info')">
                    <i class="fas fa-user"></i>
                    Informations
                </button>
                <button class="profile-tab {{ $errors->has('current_password') || $errors->has('password') ? 'active' : '' }}"
                        onclick="switchTab('password')" id="tab-password">
                    <i class="fas fa-lock"></i>
                    Mot de passe
                </button>
            </div>

            {{-- Tab: Informations --}}
            <div id="panel-info" class="profile-panel {{ $errors->has('current_password') || $errors->has('password') ? 'hidden' : '' }}">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Informations personnelles</h3>
                    </div>
                    <form action="{{ route('admin.profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            @if($errors->hasAny(['name', 'email']))
                            <div class="alert danger" style="margin-bottom: 1rem;">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span>Veuillez corriger les erreurs ci-dessous.</span>
                            </div>
                            @endif

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                                <div class="form-group">
                                    <label class="form-label">Nom complet</label>
                                    <input type="text" name="name" class="form-input"
                                           value="{{ old('name', $user->name) }}" required>
                                    @error('name')
                                    <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Adresse email</label>
                                    <input type="email" name="email" class="form-input"
                                           value="{{ old('email', $user->email) }}" required>
                                    @error('email')
                                    <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span>
                                    @enderror
                                    @if($user->email !== old('email', $user->email))
                                    <div style="font-size: 0.8rem; color: var(--warning); margin-top: 0.3rem;">
                                        <i class="fas fa-info-circle"></i>
                                        Modifier l'email réinitialisera la vérification.
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="card-footer" style="display: flex; justify-content: flex-end;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Tab: Mot de passe --}}
            <div id="panel-password" class="profile-panel {{ $errors->has('current_password') || $errors->has('password') ? '' : 'hidden' }}">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Changer le mot de passe</h3>
                    </div>
                    <form action="{{ route('admin.profile.password') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            @if($errors->hasAny(['current_password', 'password']))
                            <div class="alert danger" style="margin-bottom: 1rem;">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span>Veuillez corriger les erreurs ci-dessous.</span>
                            </div>
                            @endif

                            <div style="max-width: 480px; display: flex; flex-direction: column; gap: 1.25rem;">
                                <div class="form-group" style="margin: 0;">
                                    <label class="form-label">Mot de passe actuel</label>
                                    <div class="password-field">
                                        <input type="password" name="current_password" id="current_password"
                                               class="form-input" placeholder="••••••••" autocomplete="current-password">
                                        <button type="button" class="toggle-pwd" onclick="togglePassword('current_password', this)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    @error('current_password')
                                    <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group" style="margin: 0;">
                                    <label class="form-label">Nouveau mot de passe</label>
                                    <div class="password-field">
                                        <input type="password" name="password" id="new_password"
                                               class="form-input" placeholder="••••••••" autocomplete="new-password"
                                               oninput="checkStrength(this.value)">
                                        <button type="button" class="toggle-pwd" onclick="togglePassword('new_password', this)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div id="strength-bar" style="margin-top: 0.5rem; height: 4px; border-radius: 2px; background: var(--border); overflow: hidden;">
                                        <div id="strength-fill" style="height: 100%; width: 0; transition: width 0.3s, background 0.3s;"></div>
                                    </div>
                                    <div id="strength-label" style="font-size: 0.78rem; margin-top: 0.3rem; color: var(--gray);"></div>
                                    @error('password')
                                    <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group" style="margin: 0;">
                                    <label class="form-label">Confirmer le nouveau mot de passe</label>
                                    <div class="password-field">
                                        <input type="password" name="password_confirmation" id="confirm_password"
                                               class="form-input" placeholder="••••••••" autocomplete="new-password">
                                        <button type="button" class="toggle-pwd" onclick="togglePassword('confirm_password', this)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div style="padding: 0.75rem 1rem; background: var(--light); border-radius: 6px; border-left: 3px solid var(--primary); font-size: 0.85rem; color: var(--gray);">
                                    <i class="fas fa-info-circle" style="color: var(--primary);"></i>
                                    Le mot de passe doit contenir au moins 8 caractères.
                                </div>
                            </div>
                        </div>
                        <div class="card-footer" style="display: flex; justify-content: flex-end;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-lock"></i>
                                Changer le mot de passe
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .profile-tabs {
        display: flex;
        gap: 0.25rem;
        margin-bottom: 1rem;
        background: var(--light);
        padding: 0.35rem;
        border-radius: 10px;
        border: 1px solid var(--border);
        width: fit-content;
    }

    .profile-tab {
        padding: 0.55rem 1.25rem;
        border: none;
        background: transparent;
        border-radius: 7px;
        font-size: 0.9rem;
        font-weight: 500;
        cursor: pointer;
        color: var(--gray);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s;
    }

    .profile-tab.active {
        background: var(--white);
        color: var(--primary);
        box-shadow: 0 1px 4px rgba(0,0,0,0.08);
        font-weight: 600;
    }

    .profile-tab:hover:not(.active) {
        color: var(--dark);
    }

    .profile-panel.hidden {
        display: none;
    }

    .password-field {
        position: relative;
    }

    .password-field .form-input {
        padding-right: 2.8rem;
    }

    .toggle-pwd {
        position: absolute;
        right: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        color: var(--gray);
        padding: 0;
        font-size: 0.9rem;
        line-height: 1;
    }

    .toggle-pwd:hover {
        color: var(--primary);
    }
</style>

<script>
    function switchTab(tab) {
        document.querySelectorAll('.profile-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.profile-panel').forEach(p => p.classList.add('hidden'));

        document.getElementById('panel-' + tab).classList.remove('hidden');

        const tabBtn = tab === 'info'
            ? document.querySelector('.profile-tab:first-child')
            : document.getElementById('tab-password');
        tabBtn.classList.add('active');
    }

    function togglePassword(id, btn) {
        const input = document.getElementById(id);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

    function checkStrength(value) {
        const fill = document.getElementById('strength-fill');
        const label = document.getElementById('strength-label');

        let score = 0;
        if (value.length >= 8) score++;
        if (/[A-Z]/.test(value)) score++;
        if (/[0-9]/.test(value)) score++;
        if (/[^A-Za-z0-9]/.test(value)) score++;

        const levels = [
            { pct: '0%',   color: '',                      text: '' },
            { pct: '25%',  color: 'var(--danger)',         text: 'Très faible' },
            { pct: '50%',  color: 'var(--warning)',        text: 'Faible' },
            { pct: '75%',  color: '#F59E0B',               text: 'Moyen' },
            { pct: '100%', color: 'var(--success)',        text: 'Fort' },
        ];

        const lvl = value.length === 0 ? levels[0] : levels[Math.max(1, score)];
        fill.style.width = lvl.pct;
        fill.style.background = lvl.color;
        label.textContent = lvl.text;
        label.style.color = lvl.color;
    }

    // Auto-fermeture des alertes après 4s
    ['alert-success', 'alert-password'].forEach(id => {
        const el = document.getElementById(id);
        if (el) setTimeout(() => { el.style.opacity = '0'; el.style.transition = 'opacity 0.4s'; setTimeout(() => el.remove(), 400); }, 4000);
    });

    // Ouvrir directement l'onglet mot de passe si demandé depuis le dropdown
    if (sessionStorage.getItem('profileTab') === 'password') {
        sessionStorage.removeItem('profileTab');
        switchTab('password');
    }
</script>
@endsection
