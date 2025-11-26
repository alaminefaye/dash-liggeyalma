@extends('layouts.app')

@section('title', 'Rapport Financier')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0">Rapport Financier</h5>
        <a href="{{ route('admin.rapports.index') }}" class="btn btn-label-secondary">
            <i class="bx bx-arrow-back"></i> Retour
        </a>
    </div>
    <div class="card-body">
        <!-- Filtres -->
        <form method="GET" action="{{ route('admin.rapports.financier') }}" class="mb-4">
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

        <!-- Statistiques -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <span class="fw-semibold d-block mb-1">Revenus Total</span>
                        <h3 class="card-title mb-2 text-success">{{ number_format($stats['revenus_total'], 0, ',', ' ') }} FCFA</h3>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <span class="fw-semibold d-block mb-1">Paiements Total</span>
                        <h3 class="card-title mb-2 text-primary">{{ number_format($stats['paiements_total'], 0, ',', ' ') }} FCFA</h3>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <span class="fw-semibold d-block mb-1">Retraits Total</span>
                        <h3 class="card-title mb-2 text-info">{{ number_format($stats['retraits_total'], 0, ',', ' ') }} FCFA</h3>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <span class="fw-semibold d-block mb-1">Commandes</span>
                        <h3 class="card-title mb-2">{{ $stats['commandes_total'] }}</h3>
                        <small class="text-muted">{{ $stats['commandes_terminees'] }} terminées</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenus par jour -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title m-0">Revenus par Jour</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Revenus</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($revenusParJour as $revenu)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($revenu->date)->format('d/m/Y') }}</td>
                                <td><strong>{{ number_format($revenu->total, 0, ',', ' ') }} FCFA</strong></td>
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
@endsection

