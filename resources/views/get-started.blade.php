<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Démarrer — CauriShop</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"/>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{font-family:'Inter',sans-serif;background:#fff;color:#111;-webkit-font-smoothing:antialiased;overflow-x:hidden}
a{text-decoration:none;color:inherit}
:root{--bl:#2663EB;--t:#111111;--s:#6b7280;--b:#e8e8e8;--f:#f7f7f7}

/* ══ NAV ════════════════════════════════ */
nav{position:fixed;inset:0 0 auto;z-index:99;height:64px;padding:0 48px;display:flex;align-items:center;justify-content:space-between;background:rgba(255,255,255,.94);backdrop-filter:blur(20px);border-bottom:1px solid var(--b)}
.logo-name{font-size:17px;font-weight:800;letter-spacing:.5px;text-transform:uppercase}
.nav-menu{display:flex;gap:32px}
.nav-menu a{font-size:14px;font-weight:500;color:var(--s);transition:color .15s}
.nav-menu a:hover{color:var(--t)}
.nav-r{display:flex;align-items:center;gap:12px}
.nav-r .in{font-size:14px;font-weight:500;color:var(--s);transition:color .15s}
.nav-r .in:hover{color:var(--t)}
.nav-r .cta{font-size:14px;font-weight:700;padding:9px 22px;border-radius:8px;background:var(--bl);color:#fff;transition:opacity .15s}
.nav-r .cta:hover{opacity:.8}

/* ══ HERO ════════════════════════════════ */
.hero-sm{padding:140px 48px 64px;border-bottom:1px solid var(--b)}
.hero-sm-inner{max-width:1120px;margin:0 auto}
.hero-tag{display:inline-flex;align-items:center;gap:7px;font-size:12px;font-weight:600;color:var(--s);text-transform:uppercase;letter-spacing:1px;margin-bottom:18px}
.hero-tag i{width:6px;height:6px;border-radius:50%;background:var(--bl);display:block}
.hero-sm h1{font-size:52px;font-weight:900;letter-spacing:-2px;line-height:1.06;color:var(--t);margin-bottom:12px}
.hero-sm p{font-size:17px;color:var(--s);line-height:1.7;max-width:520px}

/* ══ STEPPER BAR ═════════════════════════ */
.stepper-wrap{padding:0 48px;border-bottom:1px solid var(--b);background:#fff;position:sticky;top:64px;z-index:9;overflow-x:auto;margin-top:64px}
.stepper{max-width:1120px;margin:0 auto;display:flex;align-items:stretch;min-width:560px}
.step-tab{flex:1;display:flex;align-items:center;gap:12px;padding:20px 0;border-bottom:2px solid transparent;opacity:.38;transition:opacity .2s,border-color .2s}
.step-tab.active{border-bottom-color:var(--bl);opacity:1}
.step-tab.done{opacity:.65}
.step-tab + .step-tab{margin-left:32px}
.sn{width:28px;height:28px;border-radius:50%;flex-shrink:0;border:1.5px solid var(--b);background:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:var(--s);transition:all .2s}
.step-tab.active .sn{background:var(--bl);border-color:var(--bl);color:#fff}
.step-tab.done .sn{background:var(--t);border-color:var(--t);color:#fff}
.step-info p{font-size:10px;font-weight:600;color:var(--s);text-transform:uppercase;letter-spacing:.8px;margin-bottom:2px}
.step-info strong{font-size:13px;font-weight:700;color:var(--t)}

/* ══ SECTIONS ════════════════════════════ */
.s{padding:72px 48px}
.si{max-width:1120px;margin:0 auto}
.step-section{display:none}
.step-section.active{display:block;animation:fadeup .4s ease both}
@keyframes fadeup{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}

.s-head{margin-bottom:44px}
.eyebrow{font-size:12px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:var(--s);display:block;margin-bottom:10px}
.s-head h2{font-size:24px;font-weight:800;letter-spacing:-.5px;line-height:1.2;color:var(--t);margin-bottom:10px}
.s-head p{font-size:16px;color:var(--s);line-height:1.7;max-width:500px}

/* ══ FIELDS ══════════════════════════════ */
.fields{display:grid;grid-template-columns:1fr 1fr;gap:22px;max-width:760px}
.field{display:flex;flex-direction:column;gap:6px}
.field.full{grid-column:1/-1}
.field.third{grid-column:span 1}
label.fl{font-size:13px;font-weight:600;color:var(--t)}
.field input,.field select,.field textarea{
  font-family:'Inter',sans-serif;font-size:14px;
  padding:12px 14px;border-radius:9px;
  border:1.5px solid var(--b);background:#fff;color:var(--t);
  transition:border-color .15s,box-shadow .15s;outline:none;width:100%;
}
.field input:focus,.field select:focus,.field textarea:focus{border-color:var(--bl);box-shadow:0 0 0 3px rgba(38,99,235,.1)}
.field input.err,.field select.err{border-color:#ef4444}
.ferr{font-size:11px;color:#ef4444;margin-top:-2px}
.field select{appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 14px center;padding-right:36px}
.field textarea{resize:vertical;min-height:90px}
.field-hint{font-size:11px;color:var(--s);margin-top:-2px}

/* ══ FILE UPLOAD ═════════════════════════ */
.doc-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;max-width:760px}
.doc-item{
  border:1.5px solid var(--b);border-radius:12px;padding:18px 20px;
  background:#fff;transition:border-color .15s;
}
.doc-item:focus-within{border-color:var(--bl)}
.doc-item-head{display:flex;align-items:flex-start;gap:10px;margin-bottom:12px}
.doc-ico{width:36px;height:36px;border-radius:8px;background:var(--f);border:1px solid var(--b);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.doc-ico svg{width:16px;height:16px;stroke:var(--s);fill:none;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
.doc-name{font-size:13px;font-weight:700;color:var(--t);margin-bottom:2px}
.doc-desc{font-size:11px;color:var(--s);line-height:1.4}
.doc-badge{font-size:10px;font-weight:700;padding:2px 7px;border-radius:5px;display:inline-block;margin-left:6px}
.doc-badge.req{background:#fef3c7;color:#92400e}
.doc-badge.opt{background:var(--f);color:var(--s)}
.doc-input{
  display:flex;align-items:center;gap:10px;
  padding:8px 12px;border-radius:7px;border:1px dashed var(--b);
  background:var(--f);cursor:pointer;transition:border-color .15s;
}
.doc-input:hover{border-color:#aaa}
.doc-input svg{width:14px;height:14px;stroke:var(--s);fill:none;stroke-width:2;flex-shrink:0}
.doc-input span{font-size:12px;color:var(--s);font-weight:500}
.doc-input input[type=file]{display:none}
.doc-filename{font-size:11px;color:var(--bl);font-weight:600;margin-top:4px;display:none}

/* ══ RECAP ═══════════════════════════════ */
.recap-grid{display:grid;grid-template-columns:1fr 1fr;gap:24px;max-width:760px}
.recap-block{background:var(--f);border:1px solid var(--b);border-radius:14px;padding:24px}
.recap-block h4{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--s);margin-bottom:14px}
.recap-row{display:flex;justify-content:space-between;align-items:flex-start;padding:9px 0;border-bottom:1px solid var(--b);gap:16px}
.recap-row:last-child{border:none;padding-bottom:0}
.recap-row span:first-child{font-size:12px;color:var(--s);font-weight:500;flex-shrink:0}
.recap-row span:last-child{font-size:13px;font-weight:700;color:var(--t);text-align:right}
.recap-docs{display:flex;flex-direction:column;gap:6px}
.recap-doc{display:flex;align-items:center;gap:8px;font-size:13px;color:var(--t);font-weight:600}
.recap-doc svg{width:14px;height:14px;stroke:#1a9e50;fill:none;stroke-width:2.5;flex-shrink:0}

/* ══ ACTIONS ═════════════════════════════ */
.actions{display:flex;align-items:center;gap:12px;margin-top:40px;max-width:760px}
.btn-next{font-family:'Inter',sans-serif;font-size:15px;font-weight:700;padding:13px 32px;border-radius:10px;background:var(--bl);color:#fff;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:8px;transition:opacity .15s,transform .15s}
.btn-next:hover{opacity:.85;transform:translateY(-1px)}
.btn-next svg{width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round}
.btn-prev{font-family:'Inter',sans-serif;font-size:14px;font-weight:600;padding:13px 24px;border-radius:10px;border:1.5px solid var(--b);color:var(--t);background:#fff;cursor:pointer;transition:border-color .15s}
.btn-prev:hover{border-color:#aaa}

/* ══ SUCCÈS ══════════════════════════════ */
.success-s{padding:100px 48px;display:none}
.success-s.show{display:block;animation:fadeup .5s ease both}
.success-inner{max-width:1120px;margin:0 auto}
.success-ico{width:56px;height:56px;border-radius:50%;background:#eef9f1;border:1px solid #c3e6cb;display:flex;align-items:center;justify-content:center;margin-bottom:28px}
.success-ico svg{width:24px;height:24px;stroke:#1a9e50;fill:none;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round}
.success-s h2{font-size:44px;font-weight:900;letter-spacing:-1.6px;line-height:1.08;color:var(--t);margin-bottom:12px}
.success-s p{font-size:17px;color:var(--s);line-height:1.7;max-width:480px;margin-bottom:36px}
.btn-home{display:inline-flex;align-items:center;gap:8px;font-size:15px;font-weight:700;padding:13px 28px;border-radius:10px;border:1.5px solid var(--b);color:var(--t);background:#fff;transition:border-color .15s,transform .15s}
.btn-home:hover{border-color:#aaa;transform:translateY(-1px)}

/* ══ FOOTER ══════════════════════════════ */
footer{background:#fff;border-top:1px solid var(--b);padding:64px 48px 40px}
.ft{max-width:1120px;margin:0 auto}
.ft-top{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:48px;margin-bottom:48px}
.ft-about p{font-size:14px;color:var(--s);line-height:1.7;margin-top:12px;max-width:220px}
.ft-col h4{font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#ccc;margin-bottom:16px}
.ft-col ul{list-style:none;display:flex;flex-direction:column;gap:10px}
.ft-col ul a{font-size:14px;color:var(--s);transition:color .15s}
.ft-col ul a:hover{color:var(--t)}
.ft-bot{display:flex;justify-content:space-between;align-items:center;padding-top:24px;border-top:1px solid var(--b)}
.ft-bot p,.ft-bot a{font-size:13px;color:#bbb}
.ft-bot a:hover{color:var(--s)}
.ft-links{display:flex;gap:20px}

/* ══ RESPONSIVE ══════════════════════════ */
@media(max-width:960px){
  nav,.stepper-wrap,.s,.success-s,footer{padding-left:20px;padding-right:20px}
  .nav-menu{display:none}
  .fields,.doc-grid,.recap-grid,.ft-top{grid-template-columns:1fr}
  .field.full{grid-column:1}
}
</style>
</head>
<body>

<!-- NAV -->
<nav>
  <a href="{{ route('home') }}" class="logo"><span class="logo-name">CauriShop</span></a>
  <div class="nav-menu">
    <a href="{{ route('home') }}#solutions">Pour qui ?</a>
    <a href="{{ route('home') }}#comment">Comment ça marche</a>
    <a href="{{ route('home') }}#entreprise">Compte entreprise</a>
  </div>
  <div class="nav-r">
    <a href="{{ route('login') }}" class="in">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:15px;height:15px;margin-right:6px;vertical-align:middle"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
      Connexion
    </a>
    <a href="{{ route('get-started') }}" class="cta">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:15px;height:15px;margin-right:6px;vertical-align:middle"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
      Démarrer
    </a>
  </div>
</nav>

<!-- STEPPER BAR -->
<div class="stepper-wrap" id="stepper-bar">
  <div class="stepper">
    <div class="step-tab active" id="tab-1">
      <div class="sn">1</div>
      <div class="step-info"><p>Étape 1</p><strong>Gérant / DG</strong></div>
    </div>
    <div class="step-tab" id="tab-2">
      <div class="sn">2</div>
      <div class="step-info"><p>Étape 2</p><strong>Entreprise</strong></div>
    </div>
    <div class="step-tab" id="tab-3">
      <div class="sn">3</div>
      <div class="step-info"><p>Étape 3</p><strong>Documents</strong></div>
    </div>
    <div class="step-tab" id="tab-4">
      <div class="sn">4</div>
      <div class="step-info"><p>Étape 4</p><strong>Confirmation</strong></div>
    </div>
  </div>
</div>

<!-- ─── ÉTAPE 1 — Gérant ─────────────────────────────────────────── -->
<section class="s step-section active" id="section-1">
  <div class="si">
    <div class="s-head">
      <span class="eyebrow">Étape 1 sur 4</span>
      <h2>Informations du Gérant / DG / Fondateur</h2>
      <p>Renseignez les coordonnées du responsable légal de l'entreprise.</p>
    </div>
    <div class="fields">
      <div class="field">
        <label class="fl" for="g-nom">Nom</label>
        <input type="text" id="g-nom" placeholder="Diallo"/>
      </div>
      <div class="field">
        <label class="fl" for="g-prenom">Prénom</label>
        <input type="text" id="g-prenom" placeholder="Mamadou"/>
      </div>
      <div class="field">
        <label class="fl" for="g-tel">Téléphone</label>
        <input type="tel" id="g-tel" placeholder="+224 6XX XXX XXX"/>
      </div>
      <div class="field">
        <label class="fl" for="g-piece">Pièce d'identité</label>
        <select id="g-piece">
          <option value="">— Sélectionner —</option>
          <option>Carte Nationale d'Identité (CNI)</option>
          <option>Passeport</option>
          <option>Permis de conduire</option>
          <option>Carte de séjour</option>
        </select>
      </div>
      <div class="field full">
        <label class="fl" for="g-adresse">Adresse personnelle</label>
        <input type="text" id="g-adresse" placeholder="Quartier, commune, ville — ex : Kipé, Ratoma, Conakry"/>
      </div>
    </div>
    <div class="actions">
      <button class="btn-next" onclick="goTo(2)">
        Suivant
        <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
      </button>
    </div>
  </div>
</section>

<!-- ─── ÉTAPE 2 — Entreprise ─────────────────────────────────────── -->
<section class="s step-section" id="section-2">
  <div class="si">
    <div class="s-head">
      <span class="eyebrow">Étape 2 sur 4</span>
      <h2>Informations de l'entreprise</h2>
      <p>Renseignez les informations officielles de votre structure.</p>
    </div>
    <div class="fields">
      <div class="field full">
        <label class="fl" for="e-raison">Raison sociale</label>
        <input type="text" id="e-raison" placeholder="Ex : Diallo & Frères SARL"/>
      </div>
      <div class="field">
        <label class="fl" for="e-tel">Téléphone de l'entreprise</label>
        <input type="tel" id="e-tel" placeholder="+224 6XX XXX XXX"/>
      </div>
      <div class="field">
        <label class="fl" for="e-date">Date de création</label>
        <input type="date" id="e-date"/>
      </div>
      <div class="field">
        <label class="fl" for="e-employes">Nombre d'employés</label>
        <select id="e-employes">
          <option value="">— Sélectionner —</option>
          <option>1 – 5</option>
          <option>6 – 20</option>
          <option>21 – 50</option>
          <option>51 – 100</option>
          <option>Plus de 100</option>
        </select>
      </div>
      <div class="field full">
        <label class="fl" for="e-adresse">Adresse de l'entreprise</label>
        <input type="text" id="e-adresse" placeholder="Quartier, commune, ville — ex : Almamya, Kaloum, Conakry"/>
      </div>
    </div>
    <div class="actions">
      <button class="btn-prev" onclick="goTo(1)">← Retour</button>
      <button class="btn-next" onclick="goTo(3)">
        Suivant
        <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
      </button>
    </div>
  </div>
</section>

<!-- ─── ÉTAPE 3 — Documents ──────────────────────────────────────── -->
<section class="s step-section" id="section-3">
  <div class="si">
    <div class="s-head">
      <span class="eyebrow">Étape 3 sur 4</span>
      <h2>Documents juridiques</h2>
      <p>Joignez les documents disponibles. Les documents obligatoires sont requis pour valider votre dossier.</p>
    </div>
    <div class="doc-grid">

      <div class="doc-item">
        <div class="doc-item-head">
          <div class="doc-ico"><svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>
          <div>
            <div class="doc-name">RCCM <span class="doc-badge req">Obligatoire</span></div>
            <div class="doc-desc">Registre du Commerce et du Crédit Mobilier</div>
          </div>
        </div>
        <label class="doc-input" for="f-rccm">
          <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
          <span>Choisir un fichier</span>
          <input type="file" id="f-rccm" accept=".pdf,.jpg,.jpeg,.png" onchange="showFile(this,'fn-rccm')"/>
        </label>
        <div class="doc-filename" id="fn-rccm"></div>
      </div>

      <div class="doc-item">
        <div class="doc-item-head">
          <div class="doc-ico"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg></div>
          <div>
            <div class="doc-name">NIF <span class="doc-badge req">Obligatoire</span></div>
            <div class="doc-desc">Numéro d'Identification Fiscale</div>
          </div>
        </div>
        <label class="doc-input" for="f-nif">
          <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
          <span>Choisir un fichier</span>
          <input type="file" id="f-nif" accept=".pdf,.jpg,.jpeg,.png" onchange="showFile(this,'fn-nif')"/>
        </label>
        <div class="doc-filename" id="fn-nif"></div>
      </div>

      <div class="doc-item">
        <div class="doc-item-head">
          <div class="doc-ico"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg></div>
          <div>
            <div class="doc-name">Statuts de la société <span class="doc-badge req">Obligatoire</span></div>
            <div class="doc-desc">Acte constitutif signé et enregistré</div>
          </div>
        </div>
        <label class="doc-input" for="f-statuts">
          <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
          <span>Choisir un fichier</span>
          <input type="file" id="f-statuts" accept=".pdf,.jpg,.jpeg,.png" onchange="showFile(this,'fn-statuts')"/>
        </label>
        <div class="doc-filename" id="fn-statuts"></div>
      </div>

      <div class="doc-item">
        <div class="doc-item-head">
          <div class="doc-ico"><svg viewBox="0 0 24 24"><path d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/><path d="M16 3H8v4h8V3z"/></svg></div>
          <div>
            <div class="doc-name">Patente commerciale <span class="doc-badge opt">Facultatif</span></div>
            <div class="doc-desc">Licence d'exercice d'activité commerciale</div>
          </div>
        </div>
        <label class="doc-input" for="f-patente">
          <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
          <span>Choisir un fichier</span>
          <input type="file" id="f-patente" accept=".pdf,.jpg,.jpeg,.png" onchange="showFile(this,'fn-patente')"/>
        </label>
        <div class="doc-filename" id="fn-patente"></div>
      </div>

      <div class="doc-item">
        <div class="doc-item-head">
          <div class="doc-ico"><svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></div>
          <div>
            <div class="doc-name">Attestation de domiciliation <span class="doc-badge opt">Facultatif</span></div>
            <div class="doc-desc">Justificatif du siège social de l'entreprise</div>
          </div>
        </div>
        <label class="doc-input" for="f-domicile">
          <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
          <span>Choisir un fichier</span>
          <input type="file" id="f-domicile" accept=".pdf,.jpg,.jpeg,.png" onchange="showFile(this,'fn-domicile')"/>
        </label>
        <div class="doc-filename" id="fn-domicile"></div>
      </div>

      <div class="doc-item">
        <div class="doc-item-head">
          <div class="doc-ico"><svg viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg></div>
          <div>
            <div class="doc-name">Pièce d'identité du gérant <span class="doc-badge req">Obligatoire</span></div>
            <div class="doc-desc">CNI, passeport ou titre de séjour en cours de validité</div>
          </div>
        </div>
        <label class="doc-input" for="f-cni">
          <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
          <span>Choisir un fichier</span>
          <input type="file" id="f-cni" accept=".pdf,.jpg,.jpeg,.png" onchange="showFile(this,'fn-cni')"/>
        </label>
        <div class="doc-filename" id="fn-cni"></div>
      </div>

    </div>
    <div class="actions">
      <button class="btn-prev" onclick="goTo(2)">← Retour</button>
      <button class="btn-next" onclick="goTo(4)">
        Vérifier ma demande
        <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
      </button>
    </div>
  </div>
</section>

<!-- ─── ÉTAPE 4 — Confirmation ───────────────────────────────────── -->
<section class="s step-section" id="section-4">
  <div class="si">
    <div class="s-head">
      <span class="eyebrow">Étape 4 sur 4</span>
      <h2>Confirmation</h2>
      <p>Vérifiez les informations avant d'envoyer votre dossier.</p>
    </div>
    <div class="recap-grid">
      <div class="recap-block">
        <h4>Gérant / DG</h4>
        <div class="recap-row"><span>Nom complet</span><span id="r-gerant">—</span></div>
        <div class="recap-row"><span>Téléphone</span><span id="r-gtel">—</span></div>
        <div class="recap-row"><span>Pièce d'identité</span><span id="r-gpiece">—</span></div>
        <div class="recap-row"><span>Adresse</span><span id="r-gadresse">—</span></div>
      </div>
      <div class="recap-block">
        <h4>Entreprise</h4>
        <div class="recap-row"><span>Raison sociale</span><span id="r-raison">—</span></div>
        <div class="recap-row"><span>Téléphone</span><span id="r-etel">—</span></div>
        <div class="recap-row"><span>Date de création</span><span id="r-edate">—</span></div>
        <div class="recap-row"><span>Employés</span><span id="r-employes">—</span></div>
        <div class="recap-row"><span>Adresse</span><span id="r-eadresse">—</span></div>
      </div>
      <div class="recap-block" style="grid-column:1/-1">
        <h4>Documents joints</h4>
        <div class="recap-docs" id="r-docs"></div>
      </div>
    </div>

    <div class="actions">
      <button class="btn-prev" onclick="goTo(3)">← Retour</button>
      <form method="POST" action="{{ route('get-started.store') }}" id="finalForm" enctype="multipart/form-data" style="display:contents">
        @csrf
        <input type="hidden" name="g_nom"       id="f-gnom"/>
        <input type="hidden" name="g_prenom"     id="f-gprenom"/>
        <input type="hidden" name="g_tel"        id="f-gtel"/>
        <input type="hidden" name="g_piece"      id="f-gpiece"/>
        <input type="hidden" name="g_adresse"    id="f-gadresse"/>
        <input type="hidden" name="e_raison"     id="f-eraison"/>
        <input type="hidden" name="e_tel"        id="f-etel"/>
        <input type="hidden" name="e_date"       id="f-edate"/>
        <input type="hidden" name="e_employes"   id="f-eemployes"/>
        <input type="hidden" name="e_adresse"    id="f-eadresse"/>
        <button type="submit" class="btn-next">
          <svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
          Envoyer mon dossier
        </button>
      </form>
    </div>
  </div>
</section>

<!-- SUCCÈS -->
<div class="success-s {{ session('success') ? 'show' : '' }}" id="success-section">
  <div class="success-inner">
    <div class="success-ico">
      <svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
    </div>
    <h2>Dossier envoyé !</h2>
    <p>Merci. Notre équipe examine votre demande et vous contactera sous 24 h pour finaliser votre inscription sur CauriShop.</p>
    <a href="{{ route('home') }}" class="btn-home">← Retour à l'accueil</a>
  </div>
</div>

<!-- FOOTER -->
<footer>
  <div class="ft">
    <div class="ft-top">
      <div class="ft-about">
        <a href="{{ route('home') }}" class="logo"><span class="logo-name">CauriShop</span></a>
        <p>La marketplace guinéenne pour acheter, vendre et réserver des services.</p>
      </div>
      <div class="ft-col">
        <h4>Navigation</h4>
        <ul>
          <li><a href="{{ route('home') }}#solutions">Pour qui ?</a></li>
          <li><a href="{{ route('home') }}#comment">Comment ça marche</a></li>
          <li><a href="{{ route('home') }}#entreprise">Compte entreprise</a></li>
        </ul>
      </div>
      <div class="ft-col">
        <h4>Rejoindre</h4>
        <ul>
          <li><a href="{{ route('get-started') }}">Explorer la boutique</a></li>
          <li><a href="{{ route('get-started') }}">Nous contacter</a></li>
          <li><a href="{{ route('get-started') }}">Démarrer</a></li>
        </ul>
      </div>
      <div class="ft-col">
        <h4>Contact</h4>
        <ul>
          <li><a href="#">Conakry, Guinée</a></li>
          <li><a href="#">contact@caurishop.gn</a></li>
          <li><a href="#">+224 XXX XXX XXX</a></li>
        </ul>
      </div>
    </div>
    <div class="ft-bot">
      <p>© {{ date('Y') }} CauriShop. Tous droits réservés.</p>
      <div class="ft-links"><a href="#">Conditions</a><a href="#">Confidentialité</a></div>
    </div>
  </div>
</footer>

<script>
@if(session('success'))
  document.getElementById('stepper-bar').style.display  = 'none';
  document.getElementById('success-section').classList.add('show');
@endif

function v(id){ return document.getElementById(id).value.trim(); }

function goTo(step) {
  if (step === 2) {
    if (!v('g-nom') || !v('g-prenom') || !v('g-tel') || !v('g-piece') || !v('g-adresse')) {
      alert('Veuillez remplir tous les champs du gérant.'); return;
    }
  }
  if (step === 3) {
    if (!v('e-raison') || !v('e-tel') || !v('e-date') || !v('e-employes') || !v('e-adresse')) {
      alert('Veuillez remplir tous les champs de l\'entreprise.'); return;
    }
  }
  if (step === 4) {
    const req = ['f-rccm','f-nif','f-statuts','f-cni'];
    const missing = req.filter(id => !document.getElementById(id).files.length);
    if (missing.length) {
      alert('Veuillez joindre les documents obligatoires (RCCM, NIF, Statuts, Pièce d\'identité).'); return;
    }
    // remplir le récap
    document.getElementById('r-gerant').textContent   = v('g-prenom') + ' ' + v('g-nom');
    document.getElementById('r-gtel').textContent     = v('g-tel');
    document.getElementById('r-gpiece').textContent   = v('g-piece');
    document.getElementById('r-gadresse').textContent = v('g-adresse');
    document.getElementById('r-raison').textContent   = v('e-raison');
    document.getElementById('r-etel').textContent     = v('e-tel');
    document.getElementById('r-edate').textContent    = document.getElementById('e-date').value;
    document.getElementById('r-employes').textContent = v('e-employes');
    document.getElementById('r-eadresse').textContent = v('e-adresse');
    // docs
    const docs = [
      {id:'f-rccm',label:'RCCM'},{id:'f-nif',label:'NIF'},{id:'f-statuts',label:'Statuts'},
      {id:'f-patente',label:'Patente commerciale'},{id:'f-domicile',label:'Attestation de domiciliation'},
      {id:'f-cni',label:'Pièce d\'identité'}
    ];
    const container = document.getElementById('r-docs');
    container.innerHTML = '';
    docs.forEach(d => {
      const el = document.getElementById(d.id);
      if (el.files.length) {
        container.innerHTML += `<div class="recap-doc"><svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>${d.label} — ${el.files[0].name}</div>`;
      }
    });
    // hidden inputs
    document.getElementById('f-gnom').value      = v('g-nom');
    document.getElementById('f-gprenom').value   = v('g-prenom');
    document.getElementById('f-gtel').value      = v('g-tel');
    document.getElementById('f-gpiece').value    = v('g-piece');
    document.getElementById('f-gadresse').value  = v('g-adresse');
    document.getElementById('f-eraison').value   = v('e-raison');
    document.getElementById('f-etel').value      = v('e-tel');
    document.getElementById('f-edate').value     = document.getElementById('e-date').value;
    document.getElementById('f-eemployes').value = v('e-employes');
    document.getElementById('f-eadresse').value  = v('e-adresse');
  }

  document.querySelectorAll('.step-section').forEach(s => s.classList.remove('active'));
  document.querySelectorAll('.step-tab').forEach(t => { t.classList.remove('active'); });
  for (let i = 1; i < step; i++) document.getElementById('tab-'+i).classList.add('done');
  document.getElementById('section-'+step).classList.add('active');
  document.getElementById('tab-'+step).classList.add('active');
  window.scrollTo({top:0,behavior:'smooth'});
}

function showFile(input, targetId) {
  const el = document.getElementById(targetId);
  if (input.files.length) {
    el.textContent = '✓ ' + input.files[0].name;
    el.style.display = 'block';
    input.closest('.doc-item').querySelector('.doc-input span').textContent = 'Changer le fichier';
  }
}
</script>
</body>
</html>
