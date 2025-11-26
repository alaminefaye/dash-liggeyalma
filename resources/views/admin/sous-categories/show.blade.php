@extends('layouts.app')

@section('title', 'Détails Sous-Catégorie')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title m-0">{{ $sousCategorie->nom }}</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Catégorie parente :</strong>
                        <p>
                            <a href="{{ route('admin.categories.show', $sousCategorie->categorieService) }}">
                                {{ $sousCategorie->categorieService->nom }}
                            </a>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <strong>Prix fixe :</strong>
                        <p>
                            @if($sousCategorie->prix_fixe)
                                <span class="badge bg-label-success">{{ number_format($sousCategorie->prix_fixe, 0, ',', ' ') }} FCFA</span>
                            @else
                                <span class="badge bg-label-secondary">Prix libre</span>
                            @endif
                        </p>
                    </div>
                </div>

                @if($sousCategorie->description)
                <div class="mb-3">
                    <strong>Description :</strong>
                    <p>{{ $sousCategorie->description }}</p>
                </div>
                @endif

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Statut :</strong>
                        <p>
                            @if($sousCategorie->active)
                                <span class="badge bg-label-success">Active</span>
                            @else
                                <span class="badge bg-label-danger">Inactive</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6">
                        <strong>Date de création :</strong>
                        <p>{{ $sousCategorie->created_at->format('d/m/Y') }}</p>
                    </div>
                </div>

                <hr>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body text-center">
                                <h3 class="card-title">{{ $stats['commandes_total'] }}</h3>
                                <p class="text-muted mb-0">Commandes totales</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body text-center">
                                <h3 class="card-title text-success">{{ $stats['commandes_terminees'] }}</h3>
                                <p class="text-muted mb-0">Commandes terminées</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('admin.sous-categories.edit', $sousCategorie) }}" class="btn btn-primary">
                        <i class="bx bx-edit"></i> Modifier
                    </a>
                    <form action="{{ route('admin.sous-categories.destroy', $sousCategorie) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Supprimer cette sous-catégorie ?')">
                            <i class="bx bx-trash"></i> Supprimer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

