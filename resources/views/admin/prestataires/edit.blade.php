@extends('layouts.app')

@section('title', 'Modifier Prestataire')

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title m-0">Modifier le Prestataire</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.prestataires.update', $prestataire) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <h6 class="mb-3">Informations Personnelles</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nom complet <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $prestataire->user->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $prestataire->user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Téléphone</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $prestataire->user->phone) }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Statut</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="active" {{ old('status', $prestataire->user->status) == 'active' ? 'selected' : '' }}>Actif</option>
                                <option value="suspended" {{ old('status', $prestataire->user->status) == 'suspended' ? 'selected' : '' }}>Suspendu</option>
                                <option value="blocked" {{ old('status', $prestataire->user->status) == 'blocked' ? 'selected' : '' }}>Bloqué</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                    </div>

                    <hr class="my-4">
                    <h6 class="mb-3">Informations Professionnelles</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Métier <span class="text-danger">*</span></label>
                            <input type="text" name="metier" class="form-control @error('metier') is-invalid @enderror" value="{{ old('metier', $prestataire->metier) }}" required>
                            @error('metier')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tarif horaire (FCFA)</label>
                            <input type="number" name="tarif_horaire" step="0.01" min="0" class="form-control @error('tarif_horaire') is-invalid @enderror" value="{{ old('tarif_horaire', $prestataire->tarif_horaire) }}">
                            @error('tarif_horaire')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $prestataire->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.prestataires.show', $prestataire) }}" class="btn btn-label-secondary">Annuler</a>
                        <button type="submit" class="btn btn-primary">Mettre à jour</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

