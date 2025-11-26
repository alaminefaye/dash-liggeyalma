@extends('layouts.app')

@section('title', 'Rapport Services')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0">Rapport Services</h5>
        <a href="{{ route('admin.rapports.index') }}" class="btn btn-label-secondary">
            <i class="bx bx-arrow-back"></i> Retour
        </a>
    </div>
    <div class="card-body">
        <!-- Filtres -->
        <form method="GET" action="{{ route('admin.rapports.services') }}" class="mb-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Date début</label>
                    <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Date fin</label>
                    <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bx bx-search"></i> Filtrer
                    </button>
                </div>
            </div>
        </form>

        <!-- Commandes par catégorie -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title m-0">Commandes par Catégorie</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Catégorie</th>
                                <th>Nombre de commandes</th>
                                <th>Revenus</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($commandesParCategorie as $categorie)
                            <tr>
                                <td><strong>{{ $categorie->nom }}</strong></td>
                                <td><span class="badge bg-label-info">{{ $categorie->total }}</span></td>
                                <td><strong>{{ number_format($categorie->revenus, 0, ',', ' ') }} FCFA</strong></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">Aucune donnée</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Top prestataires -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title m-0">Top 10 Prestataires</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Rang</th>
                                <th>Prestataire</th>
                                <th>Métier</th>
                                <th>Commandes terminées</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topPrestataires as $index => $prestataire)
                            <tr>
                                <td>
                                    <span class="badge bg-label-primary">#{{ $index + 1 }}</span>
                                </td>
                                <td>{{ $prestataire->user->name }}</td>
                                <td>{{ $prestataire->metier }}</td>
                                <td><strong>{{ $prestataire->commandes_count }}</strong></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">Aucune donnée</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

