@extends('layouts.app')

@section('title', 'Statistiques Anti-Fraude')

@section('content')
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <span class="fw-semibold d-block mb-1">Total Contournements</span>
                <h3 class="card-title mb-2">{{ $stats['total_contournements'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <span class="fw-semibold d-block mb-1">Détectés</span>
                <h3 class="card-title mb-2 text-warning">{{ $stats['contournements_detectes'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <span class="fw-semibold d-block mb-1">Confirmés</span>
                <h3 class="card-title mb-2 text-danger">{{ $stats['contournements_confirmes'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <span class="fw-semibold d-block mb-1">Prestataires Bloqués</span>
                <h3 class="card-title mb-2 text-danger">{{ $stats['prestataires_bloques'] }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title m-0">Top 10 Prestataires à Risque</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Prestataire</th>
                                <th>Contournements</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tauxContournement as $item)
                            <tr>
                                <td>{{ $item->name }}</td>
                                <td><span class="badge bg-label-danger">{{ $item->total }}</span></td>
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

    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title m-0">Analyse Commission</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Commission théorique :</strong>
                    <p>{{ number_format($commissionTheorique, 0, ',', ' ') }} FCFA</p>
                </div>
                <div class="mb-3">
                    <strong>Commission réelle :</strong>
                    <p>{{ number_format($commissionReelle, 0, ',', ' ') }} FCFA</p>
                </div>
                <div class="mb-3">
                    <strong>Écart :</strong>
                    <p>
                        @if($ecartCommission > 0)
                            <span class="text-danger">+ {{ number_format($ecartCommission, 0, ',', ' ') }} FCFA</span>
                        @else
                            <span class="text-success">{{ number_format($ecartCommission, 0, ',', ' ') }} FCFA</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

