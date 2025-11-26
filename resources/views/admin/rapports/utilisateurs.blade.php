@extends('layouts.app')

@section('title', 'Rapport Utilisateurs')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0">Rapport Utilisateurs</h5>
        <a href="{{ route('admin.rapports.index') }}" class="btn btn-label-secondary">
            <i class="bx bx-arrow-back"></i> Retour
        </a>
    </div>
    <div class="card-body">
        <!-- Filtres -->
        <form method="GET" action="{{ route('admin.rapports.utilisateurs') }}" class="mb-4">
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
                        <span class="fw-semibold d-block mb-1">Clients</span>
                        <h3 class="card-title mb-2">{{ $stats['clients_total'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <span class="fw-semibold d-block mb-1">Prestataires</span>
                        <h3 class="card-title mb-2">{{ $stats['prestataires_total'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <span class="fw-semibold d-block mb-1">Prestataires Validés</span>
                        <h3 class="card-title mb-2 text-success">{{ $stats['prestataires_valides'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <span class="fw-semibold d-block mb-1">Prestataires Actifs</span>
                        <h3 class="card-title mb-2 text-primary">{{ $stats['prestataires_actifs'] }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inscriptions par jour -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title m-0">Inscriptions par Jour</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Clients</th>
                                <th>Prestataires</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($inscriptionsParJour as $date => $inscriptions)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</td>
                                <td>{{ $inscriptions->where('role', 'client')->sum('total') }}</td>
                                <td>{{ $inscriptions->where('role', 'prestataire')->sum('total') }}</td>
                                <td><strong>{{ $inscriptions->sum('total') }}</strong></td>
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

