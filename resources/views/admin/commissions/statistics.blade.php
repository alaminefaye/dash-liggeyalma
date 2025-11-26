@extends('layouts.app')

@section('title', 'Statistiques des Commissions')

@section('content')
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title m-0">Statistiques des Commissions</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.commissions.statistics') }}" class="mb-4">
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

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <span class="fw-semibold d-block mb-1">Total Commission</span>
                        <h3 class="card-title mb-2">{{ number_format($stats['total'], 0, ',', ' ') }} FCFA</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <span class="fw-semibold d-block mb-1">Commission Due</span>
                        <h3 class="card-title mb-2 text-warning">{{ number_format($stats['commission_due'], 0, ',', ' ') }} FCFA</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <span class="fw-semibold d-block mb-1">Commission Payée</span>
                        <h3 class="card-title mb-2 text-success">{{ number_format($stats['commission_payee'], 0, ',', ' ') }} FCFA</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title m-0">Commission par Catégorie</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Catégorie</th>
                                <th>Montant</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stats['par_categorie'] as $item)
                            <tr>
                                <td>{{ $item->nom }}</td>
                                <td><strong>{{ number_format($item->total, 0, ',', ' ') }} FCFA</strong></td>
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

