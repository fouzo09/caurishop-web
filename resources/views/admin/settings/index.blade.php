@extends('tpl.admin')
@section('content')
<div class="content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Paramètres</h1>
            <div class="page-breadcrumb">
                <span>CAURISHOP</span>
                <i class="fas fa-chevron-right"></i>
                <span>Paramètres</span>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 220px 1fr; gap: 1.5rem; align-items: start;">

        {{-- Onglets verticaux --}}
        <div class="card" style="padding: 0.5rem;">
            @foreach($grouped as $key => $meta)
            <a href="{{ route('admin.settings.index', ['group' => $key]) }}"
               class="settings-tab {{ $activeGroup === $key ? 'active' : '' }}">
                <i class="fas {{ $meta['icon'] }}"></i>
                {{ $meta['label'] }}
            </a>
            @endforeach
        </div>

        {{-- Formulaire du groupe actif --}}
        @php $group = $grouped[$activeGroup]; @endphp
        <div>
            <form action="{{ route('admin.settings.update', $activeGroup) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas {{ $group['icon'] }}" style="color: var(--primary);"></i>
                            {{ $group['label'] }}
                        </h3>
                    </div>
                    <div class="card-body">
                        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                            @foreach($group['settings'] as $setting)
                            <div class="form-group" style="margin: 0;">
                                <label class="form-label">{{ $setting->label }}</label>

                                @if($setting->type === 'boolean')
                                <label style="display: flex; align-items: center; gap: 0.6rem; cursor: pointer;">
                                    <div class="toggle-switch">
                                        <input type="checkbox" name="{{ $setting->key }}" value="1"
                                               {{ $setting->value ? 'checked' : '' }}>
                                        <span class="toggle-slider"></span>
                                    </div>
                                    <span style="font-size: 0.9rem;">{{ $setting->value ? 'Activé' : 'Désactivé' }}</span>
                                </label>

                                @elseif($setting->type === 'textarea')
                                <textarea name="{{ $setting->key }}" class="form-input"
                                          rows="3" style="resize: vertical;">{{ $setting->value }}</textarea>

                                @elseif($setting->type === 'number')
                                <input type="number" name="{{ $setting->key }}" class="form-input"
                                       value="{{ $setting->value }}" style="max-width: 200px;"
                                       step="any" min="0">

                                @else
                                <input type="text" name="{{ $setting->key }}" class="form-input"
                                       value="{{ $setting->value }}" style="max-width: 400px;">
                                @endif

                                @if($setting->description)
                                <div style="font-size: 0.8rem; color: var(--gray); margin-top: 0.3rem;">
                                    {{ $setting->description }}
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="card-footer" style="display: flex; justify-content: flex-end;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            Enregistrer
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .settings-tab {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.7rem 1rem;
        border-radius: 7px;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 500;
        color: var(--gray);
        transition: all 0.15s;
    }

    .settings-tab:hover {
        background: var(--light);
        color: var(--dark);
    }

    .settings-tab.active {
        background: rgba(0, 102, 255, 0.08);
        color: var(--primary);
        font-weight: 600;
    }

    .settings-tab i {
        width: 16px;
        text-align: center;
        font-size: 0.85rem;
    }

    /* Toggle switch */
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 24px;
        flex-shrink: 0;
    }

    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background: var(--border);
        border-radius: 24px;
        transition: 0.2s;
    }

    .toggle-slider:before {
        content: '';
        position: absolute;
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background: white;
        border-radius: 50%;
        transition: 0.2s;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    }

    .toggle-switch input:checked + .toggle-slider {
        background: var(--primary);
    }

    .toggle-switch input:checked + .toggle-slider:before {
        transform: translateX(20px);
    }
</style>
@endsection
