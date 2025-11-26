@extends('layouts.app')

@section('title', 'Détails Catégorie')

@section('content')
<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-body text-center">
                @if($category->icone)
                    <i class="bx {{ $category->icone }}" style="font-size: 4rem; color: {{ $category->couleur ?? '#696cff' }};"></i>
                @endif
                <h4 class="mt-3 mb-1">{{ $category->nom }}</h4>
                @if($category->description)
                    <p class="text-muted">{{ $category->description }}</p>
                @endif
                
                <div class="d-flex justify-content-center gap-2 mt-3">
                    <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-primary">
                        <i class="bx bx-edit"></i> Modifier
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title m-0">Statistiques</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted">Sous-catégories</small>
                    <h4 class="mb-0">{{ $stats['sous_categories_total'] }}</h4>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Commandes totales</small>
                    <h4 class="mb-0">{{ $stats['commandes_total'] }}</h4>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Commandes terminées</small>
                    <h4 class="mb-0">{{ $stats['commandes_terminees'] }}</h4>
                </div>
                <div>
                    <small class="text-muted">Revenus total</small>
                    <h4 class="mb-0">{{ number_format($stats['revenus_total'], 0, ',', ' ') }} FCFA</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <!-- Informations -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title m-0">Informations</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Nom :</strong>
                        <p>{{ $category->nom }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Taux de commission :</strong>
                        <p>{{ number_format($category->commission_rate, 2) }}%</p>
                    </div>
                </div>
                @if($category->description)
                <div class="mb-3">
                    <strong>Description :</strong>
                    <p>{{ $category->description }}</p>
                </div>
                @endif
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Prix fixes :</strong>
                        <p>
                            @if($category->prix_fixe)
                                <span class="badge bg-label-success">Activés</span>
                            @else
                                <span class="badge bg-label-secondary">Désactivés</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-4">
                        <strong>Statut :</strong>
                        <p>
                            @if($category->active)
                                <span class="badge bg-label-success">Active</span>
                            @else
                                <span class="badge bg-label-danger">Inactive</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-4">
                        <strong>Couleur :</strong>
                        <p>
                            @if($category->couleur)
                                <span class="badge" style="background-color: {{ $category->couleur }}; color: white;">
                                    {{ $category->couleur }}
                                </span>
                            @else
                                -
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sous-catégories -->
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title m-0">Sous-catégories</h5>
                <a href="#" class="btn btn-sm btn-outline-primary">Gérer les sous-catégories</a>
            </div>
            <div class="card-body">
                @if($category->sousCategories->count() > 0)
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Prix fixe</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($category->sousCategories as $sousCategorie)
                                <tr>
                                    <td>{{ $sousCategorie->nom }}</td>
                                    <td>
                                        @if($sousCategorie->prix_fixe)
                                            {{ number_format($sousCategorie->prix_fixe, 0, ',', ' ') }} FCFA
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($sousCategorie->active)
                                            <span class="badge bg-label-success">Active</span>
                                        @else
                                            <span class="badge bg-label-danger">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center mb-0">Aucune sous-catégorie</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

