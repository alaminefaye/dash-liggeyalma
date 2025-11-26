@extends('layouts.app')

@section('title', 'Créer une Sous-Catégorie')

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title m-0">Créer une nouvelle Sous-Catégorie</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.sous-categories.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Catégorie parente <span class="text-danger">*</span></label>
                        <select name="categorie_service_id" class="form-select @error('categorie_service_id') is-invalid @enderror" required>
                            <option value="">Sélectionner une catégorie</option>
                            @foreach($categories as $categorie)
                                <option value="{{ $categorie->id }}" {{ old('categorie_service_id') == $categorie->id ? 'selected' : '' }}>
                                    {{ $categorie->nom }}
                                </option>
                            @endforeach
                        </select>
                        @error('categorie_service_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nom de la sous-catégorie <span class="text-danger">*</span></label>
                        <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror" value="{{ old('nom') }}" required>
                        @error('nom')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Prix fixe (FCFA)</label>
                        <input type="number" name="prix_fixe" step="0.01" min="0" class="form-control @error('prix_fixe') is-invalid @enderror" value="{{ old('prix_fixe') }}" placeholder="Laisser vide si prix variable">
                        <small class="text-muted">Si la catégorie parente a les prix fixes activés, ce champ est obligatoire</small>
                        @error('prix_fixe')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="active" id="active" value="1" {{ old('active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="active">
                                Sous-catégorie active
                            </label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.sous-categories.index') }}" class="btn btn-label-secondary">Annuler</a>
                        <button type="submit" class="btn btn-primary">Créer la sous-catégorie</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

