@extends('tpl.admin')

@section('content')
<div class="content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Scraper de Produits</h1>
            <div class="page-breadcrumb">
                <span>CAURISHOP</span>
                <i class="fas fa-chevron-right"></i>
                <span>Scraper</span>
            </div>
        </div>
    </div>

    {{-- Carte principale --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-spider" style="color:var(--primary);"></i> Nouveau scraping</h3>
        </div>

        <div class="card-body">

            <form action="{{ route('admin.scraper.run-html') }}" method="POST" id="formHtml">
                @csrf

                {{-- Zone de collage --}}
                <div id="dropZone" style="position:relative;border:2px dashed var(--border);border-radius:10px;transition:border-color .2s,background .2s;margin-bottom:1rem;">

                    {{-- État vide --}}
                    <div id="emptyState" style="padding:3rem 2rem;text-align:center;color:var(--gray);">
                        <i class="fas fa-code" style="font-size:2.5rem;opacity:.25;display:block;margin-bottom:1rem;"></i>
                        <p style="font-size:.95rem;font-weight:600;margin:0 0 .35rem;color:var(--text);">Collez votre code HTML ici</p>
                        <p style="font-size:.82rem;margin:0 0 1.25rem;">
                            Faites <kbd>Ctrl+U</kbd> sur le site cible → <kbd>Ctrl+A</kbd> → <kbd>Ctrl+C</kbd>, puis <kbd>Ctrl+V</kbd> ici.<br>
                            Ou glissez-déposez un fichier <code>.html</code>, ou utilisez le bouton ci-dessous.
                        </p>
                        <label for="fileInput" class="btn btn-outline btn-sm" style="cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;">
                            <i class="fas fa-file-code"></i> Choisir un fichier HTML
                        </label>
                        <input type="file" id="fileInput" accept=".html,.htm,text/html" style="display:none;">
                    </div>

                    {{-- État rempli --}}
                    <div id="filledState" style="display:none;">
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:.6rem 1rem;border-bottom:1px solid var(--border);background:var(--bg-light);border-radius:8px 8px 0 0;">
                            <span style="font-size:.8rem;font-weight:600;color:var(--text);display:flex;align-items:center;gap:.4rem;">
                                <i class="fas fa-code" style="color:var(--primary);"></i> Code HTML chargé
                            </span>
                            <div style="display:flex;align-items:center;gap:.75rem;">
                                <span id="htmlSize" style="font-size:.78rem;color:var(--gray);"></span>
                                <button type="button" onclick="clearHtml()" style="background:none;border:none;cursor:pointer;color:var(--gray);font-size:.8rem;padding:.2rem .4rem;border-radius:4px;display:flex;align-items:center;gap:.3rem;" title="Vider">
                                    <i class="fas fa-times"></i> Vider
                                </button>
                            </div>
                        </div>
                        <textarea name="html" id="htmlInput"
                                  rows="12" required
                                  style="width:100%;border:none;outline:none;font-family:monospace;font-size:.75rem;line-height:1.6;padding:1rem;resize:vertical;background:transparent;box-sizing:border-box;color:var(--text);"
                                  oninput="onHtmlInput()">{{ old('html') }}</textarea>
                    </div>

                    {{-- Overlay drag --}}
                    <div id="dragOverlay" style="display:none;position:absolute;inset:0;border-radius:8px;background:rgba(37,99,235,.06);border:2px dashed var(--primary);z-index:10;align-items:center;justify-content:center;flex-direction:column;gap:.5rem;color:var(--primary);font-weight:600;font-size:.95rem;">
                        <i class="fas fa-file-import" style="font-size:2rem;"></i>
                        Déposez le fichier ici
                    </div>
                </div>

                <div style="display:flex;justify-content:flex-end;">
                    <button type="submit" class="btn btn-primary" id="btnHtml">
                        <i class="fas fa-magic"></i> Extraire les produits
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Liste des fichiers générés --}}
    <div class="card" style="margin-top:1.5rem;">
        <div class="card-header">
            <h3 class="card-title">Fichiers générés</h3>
            <span class="badge secondary">{{ count($files) }}</span>
        </div>

        @if(empty($files))
        <div class="card-body" style="text-align:center;padding:3rem;color:var(--gray);">
            <i class="fas fa-folder-open" style="font-size:2.5rem;opacity:.3;display:block;margin-bottom:.75rem;"></i>
            Aucun fichier pour l'instant. Lancez votre premier scraping ci-dessus.
        </div>
        @else
        <div class="card-body" style="padding:0;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Fichier</th>
                        <th>Source</th>
                        <th>Produits</th>
                        <th>Date</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($files as $file)
                    <tr>
                        <td>
                            <div style="font-weight:600;font-size:.9rem;">
                                {{ $file['label'] ?: pathinfo($file['filename'], PATHINFO_FILENAME) }}
                            </div>
                            <div style="font-size:.75rem;color:var(--gray);">{{ $file['filename'] }}</div>
                        </td>
                        <td>
                            @if($file['url'])
                            <a href="{{ $file['url'] }}" target="_blank" rel="noopener"
                               style="font-size:.8rem;color:var(--primary);max-width:280px;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                               title="{{ $file['url'] }}">{{ $file['url'] }}</a>
                            @else
                            <span style="font-size:.8rem;color:var(--gray);">HTML collé</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $file['count'] > 0 ? 'success' : 'secondary' }}">
                                {{ $file['count'] }} produit{{ $file['count'] > 1 ? 's' : '' }}
                            </span>
                        </td>
                        <td style="font-size:.85rem;color:var(--gray);">
                            {{ $file['scraped_at'] ? \Carbon\Carbon::parse($file['scraped_at'])->format('d/m/Y H:i') : '—' }}
                        </td>
                        <td style="text-align:right;white-space:nowrap;">
                            <a href="{{ route('admin.scraper.show', $file['filename']) }}" class="btn btn-outline btn-sm">
                                <i class="fas fa-eye"></i> Voir
                            </a>
                            <form action="{{ route('admin.scraper.destroy', $file['filename']) }}" method="POST" style="display:inline;"
                                  onsubmit="return confirm('Supprimer ce fichier ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

<script>
const dropZone   = document.getElementById('dropZone');
const emptyState = document.getElementById('emptyState');
const filledState= document.getElementById('filledState');
const htmlInput  = document.getElementById('htmlInput');
const htmlSize   = document.getElementById('htmlSize');
const dragOverlay= document.getElementById('dragOverlay');
const fileInput  = document.getElementById('fileInput');

function showFilled(value) {
    htmlInput.value = value;
    emptyState.style.display  = 'none';
    filledState.style.display = '';
    updateSize();
    dropZone.style.border = '2px solid var(--border)';
}

function clearHtml() {
    htmlInput.value = '';
    filledState.style.display = 'none';
    emptyState.style.display  = '';
    dropZone.style.border = '2px dashed var(--border)';
    htmlSize.textContent = '';
}

function updateSize() {
    const kb = (htmlInput.value.length / 1024).toFixed(1);
    htmlSize.textContent = htmlInput.value.length > 0 ? kb + ' Ko' : '';
}

function onHtmlInput() {
    if (!htmlInput.value.trim()) { clearHtml(); return; }
    updateSize();
}

// Paste anywhere on the drop zone (empty state)
dropZone.addEventListener('paste', function(e) {
    const text = e.clipboardData.getData('text');
    if (text) { e.preventDefault(); showFilled(text); }
});

// Allow paste even when clicking the empty state area
emptyState.addEventListener('click', function() { dropZone.focus(); });
dropZone.setAttribute('tabindex', '0');

// File input
fileInput.addEventListener('change', function() {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => showFilled(e.target.result);
    reader.readAsText(file);
    this.value = '';
});

// Drag & drop
['dragenter','dragover'].forEach(evt => {
    dropZone.addEventListener(evt, function(e) {
        e.preventDefault();
        dragOverlay.style.display = 'flex';
    });
});
['dragleave','dragend'].forEach(evt => {
    dropZone.addEventListener(evt, function(e) {
        if (!dropZone.contains(e.relatedTarget)) dragOverlay.style.display = 'none';
    });
});
dropZone.addEventListener('drop', function(e) {
    e.preventDefault();
    dragOverlay.style.display = 'none';
    const file = e.dataTransfer.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = ev => showFilled(ev.target.result);
        reader.readAsText(file);
    } else {
        const text = e.dataTransfer.getData('text');
        if (text) showFilled(text);
    }
});

// Restore if old('html') present
@if(old('html'))
emptyState.style.display  = 'none';
filledState.style.display = '';
updateSize();
dropZone.style.border = '2px solid var(--border)';
@endif

// Submit spinner
document.getElementById('formHtml').addEventListener('submit', function() {
    const btn = document.getElementById('btnHtml');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Extraction en cours…';
});
</script>
@endsection
