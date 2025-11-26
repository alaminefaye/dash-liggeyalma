@extends('layouts.app')

@section('title', 'Créer une Catégorie')

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title m-0">Créer une nouvelle Catégorie</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.categories.store') }}" method="POST">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label class="form-label">Nom de la catégorie <span class="text-danger">*</span></label>
                            <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror" value="{{ old('nom') }}" required>
                            @error('nom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Taux de commission (%) <span class="text-danger">*</span></label>
                            <input type="number" name="commission_rate" step="0.01" min="0" max="100" class="form-control @error('commission_rate') is-invalid @enderror" value="{{ old('commission_rate', 10) }}" required>
                            @error('commission_rate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Icône (Boxicons)</label>
                            <input type="text" name="icone" class="form-control @error('icone') is-invalid @enderror" value="{{ old('icone') }}" placeholder="Ex: bx-wrench">
                            <small class="text-muted">Nom de l'icône Boxicons (ex: bx-wrench, bx-bolt)</small>
                            @error('icone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Couleur (Hex)</label>
                            <input type="color" name="couleur" class="form-control form-control-color @error('couleur') is-invalid @enderror" value="{{ old('couleur', '#696cff') }}">
                            @error('couleur')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="prix_fixe" id="prix_fixe" value="1" {{ old('prix_fixe') ? 'checked' : '' }}>
                                <label class="form-check-label" for="prix_fixe">
                                    Activer les prix fixes pour cette catégorie
                                </label>
                            </div>
                            <small class="text-muted">Si activé, les prestataires ne pourront pas modifier les prix</small>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="active" id="active" value="1" {{ old('active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="active">
                                    Catégorie active
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-label-secondary">Annuler</a>
                        <button type="submit" class="btn btn-primary">Créer la catégorie</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

