@extends('layouts.app')

@section('title', 'Rapports Commandes')

@section('content')
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title m-0">Rapports Commandes</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.rapports.commandes') }}" class="mb-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Date début</label>
                    <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Date fin</label>
                    <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                </div>
            </div>
        </form>

        <!-- Statistiques -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <span class="fw-semibold d-block mb-1">Total Commandes</span>
                        <h3 class="card-title mb-2">{{ number_format($stats['total']) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <span class="fw-semibold d-block mb-1">Acceptées</span>
                        <h3 class="card-title mb-2 text-success">{{ number_format($stats['acceptees']) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <span class="fw-semibold d-block mb-1">Taux d'Acceptation</span>
                        <h3 class="card-title mb-2 text-info">{{ number_format($stats['taux_acceptation'], 1) }}%</h3>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <span class="fw-semibold d-block mb-1">Taux d'Annulation</span>
                        <h3 class="card-title mb-2 text-danger">{{ number_format($stats['taux_annulation'], 1) }}%</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Commandes par Statut -->
        <div class="row mb-4">
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title m-0">Commandes par Statut</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Statut</th>
                                        <th>Nombre</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($commandesParStatut as $item)
                                    <tr>
                                        <td>{{ ucfirst(str_replace('_', ' ', $item->statut)) }}</td>
                                        <td><strong>{{ $item->total }}</strong></td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted">Aucune donnée</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Commandes par Catégorie -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title m-0">Commandes par Catégorie</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Catégorie</th>
                                        <th>Nombre</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($commandesParCategorie as $item)
                                    <tr>
                                        <td>{{ $item->nom }}</td>
                                        <td><strong>{{ $item->total }}</strong></td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted">Aucune donnée</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

