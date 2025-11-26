@extends('layouts.app')

@section('title', 'Modifier Catégorie')

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title m-0">Modifier la Catégorie</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.categories.update', $category) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label class="form-label">Nom de la catégorie <span class="text-danger">*</span></label>
                            <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror" value="{{ old('nom', $category->nom) }}" required>
                            @error('nom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Taux de commission (%) <span class="text-danger">*</span></label>
                            <input type="number" name="commission_rate" step="0.01" min="0" max="100" class="form-control @error('commission_rate') is-invalid @enderror" value="{{ old('commission_rate', $category->commission_rate) }}" required>
                            @error('commission_rate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $category->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Icône (Boxicons)</label>
                            <input type="text" name="icone" class="form-control @error('icone') is-invalid @enderror" value="{{ old('icone', $category->icone) }}" placeholder="Ex: bx-wrench">
                            <small class="text-muted">Nom de l'icône Boxicons</small>
                            @error('icone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Couleur (Hex)</label>
                            <input type="color" name="couleur" class="form-control form-control-color @error('couleur') is-invalid @enderror" value="{{ old('couleur', $category->couleur ?? '#696cff') }}">
                            @error('couleur')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="prix_fixe" id="prix_fixe" value="1" {{ old('prix_fixe', $category->prix_fixe) ? 'checked' : '' }}>
                                <label class="form-check-label" for="prix_fixe">
                                    Activer les prix fixes pour cette catégorie
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="active" id="active" value="1" {{ old('active', $category->active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="active">
                                    Catégorie active
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.categories.show', $category) }}" class="btn btn-label-secondary">Annuler</a>
                        <button type="submit" class="btn btn-primary">Mettre à jour</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

