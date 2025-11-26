@extends('layouts.app')

@section('title', 'Rapports des Commissions')

@section('content')
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title m-0">Rapports des Commissions</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.commissions.reports') }}" class="mb-4">
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
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <span class="fw-semibold d-block mb-1">Commission Due</span>
                        <h3 class="card-title mb-2 text-warning">{{ number_format($commissionDue, 0, ',', ' ') }} FCFA</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <span class="fw-semibold d-block mb-1">Commission Payée</span>
                        <h3 class="card-title mb-2 text-success">{{ number_format($commissionPayee, 0, ',', ' ') }} FCFA</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <span class="fw-semibold d-block mb-1">Écart</span>
                        <h3 class="card-title mb-2 {{ $ecart > 0 ? 'text-danger' : 'text-success' }}">
                            {{ number_format($ecart, 0, ',', ' ') }} FCFA
                        </h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title m-0">Prestataires avec Solde Négatif</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Prestataire</th>
                                <th>Solde</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($prestatairesSoldeNegatif as $prestataire)
                            <tr>
                                <td>{{ $prestataire->user->name }}</td>
                                <td><span class="badge bg-label-danger">{{ number_format($prestataire->solde, 0, ',', ' ') }} FCFA</span></td>
                                <td>
                                    <a href="{{ route('admin.soldes-negatifs.show', $prestataire) }}" class="btn btn-sm btn-info">Voir</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">Aucun solde négatif</td>
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

