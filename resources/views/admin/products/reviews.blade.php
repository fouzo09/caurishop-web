@extends('tpl.admin')

@section('content')
<div class="content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Avis clients</h1>
            <div class="page-breadcrumb">
                <span>CAURISHOP</span>
                <i class="fas fa-chevron-right"></i>
                <a href="{{ route('admin.products.index') }}">Produits</a>
                <i class="fas fa-chevron-right"></i>
                <a href="{{ route('admin.products.show', $product) }}">{{ $product->name }}</a>
                <i class="fas fa-chevron-right"></i>
                <span>Avis</span>
            </div>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <a href="{{ route('shop.products.show', $product->id) }}" target="_blank" class="btn btn-outline btn-sm">
                <i class="fas fa-external-link-alt"></i> Voir la fiche publique
            </a>
            <a href="{{ route('admin.products.show', $product) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-arrow-left"></i> Retour au produit
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert success">
        <i class="fas fa-check-circle"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="alert danger">
        <i class="fas fa-times-circle"></i>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-comments"></i></div>
            <div class="stat-info">
                <div class="stat-label">Total avis</div>
                <div class="stat-value">{{ $total }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon orange"><i class="fas fa-star"></i></div>
            <div class="stat-info">
                <div class="stat-label">Note moyenne</div>
                <div class="stat-value">{{ $published ? number_format($average, 1, ',', ' ') . '/5' : '—' }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-eye"></i></div>
            <div class="stat-info">
                <div class="stat-label">Publiés</div>
                <div class="stat-value">{{ $published }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(239,68,68,.1); color: var(--danger);"><i class="fas fa-eye-slash"></i></div>
            <div class="stat-info">
                <div class="stat-label">Masqués</div>
                <div class="stat-value">{{ $hidden }}</div>
            </div>
        </div>
    </div>

    <div class="card">
        {{-- Filtres --}}
        <div style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--border); background: var(--light);">
            <form method="GET" action="{{ route('admin.products.reviews.index', $product) }}" style="display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: flex-end;">
                <div>
                    <input type="text" name="search" class="form-input" placeholder="Rechercher dans les commentaires..."
                        value="{{ request('search') }}" style="min-width: 260px;">
                </div>
                <div>
                    <select name="status" class="form-input">
                        <option value="">Tous les statuts</option>
                        <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Publiés</option>
                        <option value="hidden" {{ request('status') === 'hidden' ? 'selected' : '' }}>Masqués</option>
                    </select>
                </div>
                <div>
                    <select name="rating" class="form-input">
                        <option value="">Toutes les notes</option>
                        @for($note = 5; $note >= 1; $note--)
                        <option value="{{ $note }}" {{ (string) request('rating') === (string) $note ? 'selected' : '' }}>{{ $note }} étoile{{ $note > 1 ? 's' : '' }}</option>
                        @endfor
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-search"></i> Filtrer
                </button>
                @if(request()->hasAny(['search', 'status', 'rating']))
                <a href="{{ route('admin.products.reviews.index', $product) }}" class="btn btn-outline btn-sm">
                    <i class="fas fa-times"></i> Réinitialiser
                </a>
                @endif
            </form>
        </div>

        <div class="card-body" style="padding: 0;">
            <div class="table-container">
                <table>
                    <thead>
                    <tr>
                        <th style="width: 190px;">Client</th>
                        <th style="width: 110px;">Note</th>
                        <th>Commentaire</th>
                        <th style="width: 110px;">Statut</th>
                        <th style="width: 110px;">Date</th>
                        <th style="width: 110px;">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($reviews as $review)
                    <tr>
                        <td>
                            <div style="font-weight: 600;">{{ $review->customer?->first_name }} {{ $review->customer?->last_name }}</div>
                            <div style="font-size: 0.8rem; color: var(--gray);">
                                {{ $review->customer?->email ?: $review->customer?->phone ?: '—' }}
                            </div>
                            @if($review->is_verified)
                            <span class="badge success" style="margin-top: .3rem;">
                                <i class="fas fa-check-circle"></i> Achat vérifié
                            </span>
                            @endif
                        </td>
                        <td>
                            <span style="color: #F59E0B; white-space: nowrap;">
                                @for($i = 1; $i <= 5; $i++)
                                <i class="fa{{ $i <= $review->rating ? 's' : 'r' }} fa-star" style="font-size: .8rem;"></i>
                                @endfor
                            </span>
                            <div style="font-size: 0.8rem; color: var(--gray);">{{ $review->rating }}/5</div>
                        </td>
                        <td>
                            @if($review->title)
                            <div style="font-weight: 600; margin-bottom: .2rem;">{{ $review->title }}</div>
                            @endif
                            <div style="color: var(--gray); line-height: 1.5; white-space: pre-line;">{{ $review->comment }}</div>
                        </td>
                        <td>
                            @if($review->is_approved)
                            <span class="badge success">Publié</span>
                            @else
                            <span class="badge danger">Masqué</span>
                            @endif
                        </td>
                        <td style="font-size: 0.85rem; color: var(--gray);">
                            {{ $review->created_at?->format('d/m/Y') }}
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.5rem;">
                                <form method="POST" action="{{ route('admin.products.reviews.toggle', [$product, $review]) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-outline btn-sm"
                                        title="{{ $review->is_approved ? 'Masquer du site' : 'Republier' }}">
                                        <i class="fas {{ $review->is_approved ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                    </button>
                                </form>
                                <button type="button" class="btn btn-outline btn-sm" style="color: var(--danger);" title="Supprimer"
                                    onclick="openDeleteReviewModal({{ $review->id }}, @js($review->customer?->first_name . ' ' . $review->customer?->last_name))">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <form id="delete-review-{{ $review->id }}" method="POST"
                                action="{{ route('admin.products.reviews.destroy', [$product, $review]) }}" style="display:none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2.5rem; color: var(--gray);">
                            <i class="fas fa-comment-slash" style="font-size: 3rem; opacity: 0.3; display: block; margin-bottom: 1rem;"></i>
                            @if(request()->hasAny(['search', 'status', 'rating']))
                            Aucun avis ne correspond à ces filtres.
                            @else
                            Ce produit n'a encore reçu aucun avis.
                            @endif
                        </td>
                    </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($reviews->hasPages())
            <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--border);">
                {{ $reviews->withQueryString()->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal suppression d'un avis --}}
<div id="deleteReviewModal" class="modal-overlay">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3><i class="fas fa-exclamation-triangle"></i> Supprimer l'avis</h3>
            <button class="close-btn" onclick="closeModal('deleteReviewModal')">&times;</button>
        </div>
        <div class="modal-body">
            <p>Voulez-vous vraiment supprimer l'avis de <strong id="deleteReviewAuthor"></strong> ?</p>
            <div class="info-box danger">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Cette action est irréversible. Pour retirer l'avis du site sans le détruire, utilisez « Masquer ».</span>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('deleteReviewModal')">Annuler</button>
            <button class="btn btn-primary" style="background: var(--danger);" onclick="confirmDeleteReview()">Supprimer</button>
        </div>
    </div>
</div>

<style>
    .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; }
    .modal-overlay.active { display: flex; }
    .modal-dialog { background: var(--white); border-radius: 8px; width: 90%; max-width: 450px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
    .modal-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
    .modal-header h3 { margin: 0; font-size: 1.1rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; }
    .close-btn { background: none; border: none; font-size: 1.5rem; color: var(--gray); cursor: pointer; padding: 0; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; border-radius: 4px; }
    .close-btn:hover { background: var(--light); color: var(--dark); }
    .modal-body { padding: 1.5rem; }
    .modal-body p { margin-bottom: 1rem; line-height: 1.6; }
    .info-box { padding: 0.75rem 1rem; border-radius: 6px; display: flex; align-items: center; gap: 0.75rem; font-size: 0.9rem; }
    .info-box.danger { background: rgba(239,68,68,0.1); color: var(--danger); border-left: 3px solid var(--danger); }
    .modal-footer { padding: 1rem 1.5rem; border-top: 1px solid var(--border); display: flex; gap: 0.75rem; justify-content: flex-end; }
</style>

<script>
    let currentReviewId = null;

    function openDeleteReviewModal(id, author) {
        currentReviewId = id;
        document.getElementById('deleteReviewAuthor').textContent = author.trim() || 'ce client';
        document.getElementById('deleteReviewModal').classList.add('active');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('active');
        currentReviewId = null;
    }

    function confirmDeleteReview() {
        if (currentReviewId) {
            document.getElementById('delete-review-' + currentReviewId).submit();
        }
    }

    window.onclick = function (e) {
        if (e.target.classList.contains('modal-overlay')) {
            e.target.classList.remove('active');
            currentReviewId = null;
        }
    }
</script>
@endsection
