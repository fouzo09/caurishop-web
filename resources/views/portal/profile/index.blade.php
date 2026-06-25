@extends('tpl.portal')

@section('content')
<div class="page-content">
    <div class="page-header">
        <h1 class="page-title">Mon Profil</h1>
    </div>

    @if(session('error'))
    <div style="margin-bottom:1.5rem;padding:0.85rem 1.1rem;background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:8px;color:#991b1b;display:flex;align-items:center;gap:0.6rem;">
        <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
    </div>
    @endif
    @if(session('success'))
    <div id="alert-success" style="margin-bottom:1.5rem;padding:0.85rem 1.1rem;background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.3);border-radius:8px;color:#065f46;display:flex;align-items:center;gap:0.6rem;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif
    @if(session('password_success'))
    <div id="alert-password" style="margin-bottom:1.5rem;padding:0.85rem 1.1rem;background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.3);border-radius:8px;color:#065f46;display:flex;align-items:center;gap:0.6rem;">
        <i class="fas fa-check-circle"></i> {{ session('password_success') }}
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
                <div style="font-size: 0.85rem; color: var(--gray); margin-bottom: 0.75rem;">{{ $user->email }}</div>
                <div style="font-size: 0.85rem; color: var(--gray); margin-bottom: 1rem;">
                    {{ $user->company?->name ?? '' }}
                </div>
                <div style="display: flex; flex-wrap: wrap; gap: 0.4rem; justify-content: center; margin-bottom: 1.25rem;">
                    @forelse($user->roles as $role)
                    <span class="badge badge-primary">{{ ucfirst($role->name) }}</span>
                    @empty
                    <span class="badge badge-secondary">Aucun rôle</span>
                    @endforelse
                </div>
                <div style="display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.85rem; text-align: left;">
                    <div style="display: flex; align-items: center; gap: 0.6rem; color: var(--gray);">
                        <i class="fas fa-calendar" style="width: 16px;"></i>
                        Membre depuis {{ $user->created_at->format('d/m/Y') }}
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.6rem;">
                        @if($user->is_active)
                        <i class="fas fa-circle" style="width: 16px; color: #10b981; font-size: 0.6rem;"></i>
                        <span style="color: #10b981;">Compte actif</span>
                        @else
                        <i class="fas fa-circle" style="width: 16px; color: #ef4444; font-size: 0.6rem;"></i>
                        <span style="color: #ef4444;">Compte suspendu</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Onglets --}}
        <div>
            <div style="display:flex;gap:0.25rem;margin-bottom:1rem;background:var(--light);padding:0.35rem;border-radius:10px;border:1px solid var(--border);width:fit-content;">
                <button onclick="switchTab('info')" id="tab-info"
                        style="padding:0.55rem 1.25rem;border:none;background:var(--white);border-radius:7px;font-size:0.9rem;font-weight:600;cursor:pointer;color:var(--primary);display:flex;align-items:center;gap:0.5rem;box-shadow:0 1px 4px rgba(0,0,0,0.08);">
                    <i class="fas fa-user"></i> Informations
                </button>
                <button onclick="switchTab('password')" id="tab-password"
                        style="padding:0.55rem 1.25rem;border:none;background:transparent;border-radius:7px;font-size:0.9rem;font-weight:500;cursor:pointer;color:var(--gray);display:flex;align-items:center;gap:0.5rem;">
                    <i class="fas fa-lock"></i> Mot de passe
                </button>
            </div>

            {{-- Tab: Informations --}}
            <div id="panel-info" class="{{ $errors->has('current_password') || $errors->has('password') ? 'hidden' : '' }}">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Informations personnelles</h3>
                    </div>
                    <form action="{{ route('portal.profile.update') }}" method="POST">
                        @csrf @method('PUT')
                        <div class="card-body">
                            @if($errors->hasAny(['name', 'email']))
                            <div style="margin-bottom:1rem;padding:0.75rem 1rem;background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:8px;color:#991b1b;">
                                <i class="fas fa-exclamation-triangle"></i> Veuillez corriger les erreurs ci-dessous.
                            </div>
                            @endif
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                                <div class="form-group">
                                    <label class="form-label">Nom complet</label>
                                    <input type="text" name="name" class="form-control"
                                           value="{{ old('name', $user->name) }}" required>
                                    @error('name')<span style="color:#ef4444;font-size:0.85rem;">{{ $message }}</span>@enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Adresse email</label>
                                    <input type="email" name="email" class="form-control"
                                           value="{{ old('email', $user->email) }}" required>
                                    @error('email')<span style="color:#ef4444;font-size:0.85rem;">{{ $message }}</span>@enderror
                                </div>
                                <div class="form-group" style="grid-column:1/-1;">
                                    <label class="form-label">Numéro de téléphone <span style="font-size:.8rem;color:var(--gray);">(requis pour les paiements Djomy)</span></label>
                                    <input type="tel" name="phone" class="form-control"
                                           value="{{ old('phone', $customer?->phone) }}"
                                           placeholder="Ex : 00224623707722">
                                    @error('phone')<span style="color:#ef4444;font-size:0.85rem;">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
                        <div class="card-footer" style="display:flex;justify-content:flex-end;padding:1rem 1.25rem;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Tab: Mot de passe --}}
            <div id="panel-password" class="{{ $errors->has('current_password') || $errors->has('password') ? '' : 'hidden' }}">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Changer le mot de passe</h3>
                    </div>
                    <form action="{{ route('portal.profile.password') }}" method="POST">
                        @csrf @method('PUT')
                        <div class="card-body">
                            <div style="max-width: 480px; display: flex; flex-direction: column; gap: 1.25rem;">
                                <div class="form-group" style="margin:0;">
                                    <label class="form-label">Mot de passe actuel</label>
                                    <div style="position:relative;">
                                        <input type="password" name="current_password" id="current_password"
                                               class="form-control" placeholder="••••••••" style="padding-right:2.8rem;">
                                        <button type="button" onclick="togglePwd('current_password',this)"
                                                style="position:absolute;right:0.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--gray);">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    @error('current_password')<span style="color:#ef4444;font-size:0.85rem;">{{ $message }}</span>@enderror
                                </div>
                                <div class="form-group" style="margin:0;">
                                    <label class="form-label">Nouveau mot de passe</label>
                                    <div style="position:relative;">
                                        <input type="password" name="password" id="new_password"
                                               class="form-control" placeholder="••••••••" style="padding-right:2.8rem;"
                                               oninput="checkStrength(this.value)">
                                        <button type="button" onclick="togglePwd('new_password',this)"
                                                style="position:absolute;right:0.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--gray);">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div style="margin-top:0.5rem;height:4px;border-radius:2px;background:var(--border);overflow:hidden;">
                                        <div id="strength-fill" style="height:100%;width:0;transition:width 0.3s,background 0.3s;"></div>
                                    </div>
                                    <div id="strength-label" style="font-size:0.78rem;margin-top:0.3rem;color:var(--gray);"></div>
                                    @error('password')<span style="color:#ef4444;font-size:0.85rem;">{{ $message }}</span>@enderror
                                </div>
                                <div class="form-group" style="margin:0;">
                                    <label class="form-label">Confirmer le nouveau mot de passe</label>
                                    <div style="position:relative;">
                                        <input type="password" name="password_confirmation" id="confirm_password"
                                               class="form-control" placeholder="••••••••" style="padding-right:2.8rem;">
                                        <button type="button" onclick="togglePwd('confirm_password',this)"
                                                style="position:absolute;right:0.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--gray);">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer" style="display:flex;justify-content:flex-end;padding:1rem 1.25rem;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-lock"></i> Changer le mot de passe
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .hidden { display: none; }
</style>

<script>
function switchTab(tab) {
    ['info','password'].forEach(t => {
        document.getElementById('panel-' + t).classList.toggle('hidden', t !== tab);
        const btn = document.getElementById('tab-' + t);
        if (t === tab) {
            btn.style.background    = 'var(--white)';
            btn.style.color         = 'var(--primary)';
            btn.style.fontWeight    = '600';
            btn.style.boxShadow     = '0 1px 4px rgba(0,0,0,0.08)';
        } else {
            btn.style.background    = 'transparent';
            btn.style.color         = 'var(--gray)';
            btn.style.fontWeight    = '500';
            btn.style.boxShadow     = 'none';
        }
    });
}
function togglePwd(id, btn) {
    const input = document.getElementById(id);
    const icon  = btn.querySelector('i');
    input.type  = input.type === 'password' ? 'text' : 'password';
    icon.classList.toggle('fa-eye', input.type === 'password');
    icon.classList.toggle('fa-eye-slash', input.type === 'text');
}
function checkStrength(value) {
    let score = 0;
    if (value.length >= 8) score++;
    if (/[A-Z]/.test(value)) score++;
    if (/[0-9]/.test(value)) score++;
    if (/[^A-Za-z0-9]/.test(value)) score++;
    const levels = [
        { pct:'0%',   color:'',          text:'' },
        { pct:'25%',  color:'#ef4444',   text:'Très faible' },
        { pct:'50%',  color:'#f59e0b',   text:'Faible' },
        { pct:'75%',  color:'#f59e0b',   text:'Moyen' },
        { pct:'100%', color:'#10b981',   text:'Fort' },
    ];
    const lvl = value.length === 0 ? levels[0] : levels[Math.max(1, score)];
    document.getElementById('strength-fill').style.width      = lvl.pct;
    document.getElementById('strength-fill').style.background = lvl.color;
    document.getElementById('strength-label').textContent     = lvl.text;
    document.getElementById('strength-label').style.color     = lvl.color;
}
['alert-success','alert-password'].forEach(id => {
    const el = document.getElementById(id);
    if (el) setTimeout(() => { el.style.transition='opacity 0.4s'; el.style.opacity='0'; setTimeout(()=>el.remove(),400); }, 4000);
});
if (sessionStorage.getItem('profileTab') === 'password') {
    sessionStorage.removeItem('profileTab');
    switchTab('password');
}
</script>
@endsection
