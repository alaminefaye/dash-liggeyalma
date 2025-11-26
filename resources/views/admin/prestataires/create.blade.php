@extends('layouts.app')

@section('title', 'Créer un Prestataire')

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title m-0">Créer un nouveau Prestataire</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.prestataires.store') }}" method="POST">
                    @csrf

                    <h6 class="mb-3">Informations Personnelles</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nom complet <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Téléphone</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mot de passe <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <hr class="my-4">
                    <h6 class="mb-3">Informations Professionnelles</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Métier <span class="text-danger">*</span></label>
                            <input type="text" name="metier" class="form-control @error('metier') is-invalid @enderror" value="{{ old('metier') }}" required>
                            @error('metier')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tarif horaire (FCFA)</label>
                            <input type="number" name="tarif_horaire" step="0.01" min="0" class="form-control @error('tarif_horaire') is-invalid @enderror" value="{{ old('tarif_horaire') }}">
                            @error('tarif_horaire')
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

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.prestataires.index') }}" class="btn btn-label-secondary">Annuler</a>
                        <button type="submit" class="btn btn-primary">Créer le prestataire</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

