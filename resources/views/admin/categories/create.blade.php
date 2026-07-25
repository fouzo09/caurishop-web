@extends('tpl.admin')

@section('content')
<div class="content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Nouvelle catégorie</h1>
            <div class="page-breadcrumb">
                <span>CAURISHOP</span>
                <i class="fas fa-chevron-right"></i>
                <a href="{{ route('admin.categories.index') }}">Catégories</a>
                <i class="fas fa-chevron-right"></i>
                <span>Nouvelle</span>
            </div>
        </div>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>

    <div class="card" style="max-width:680px;">
        <div class="card-body">
            @if($errors->any())
            <div class="alert danger" style="margin-bottom:1.25rem;">
                @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
            </div>
            @endif

            <form action="{{ route('admin.categories.store') }}" method="POST">
                @csrf
                @include('admin.categories._form')
                <div style="display:flex;justify-content:flex-end;gap:.75rem;">
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline">Annuler</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
