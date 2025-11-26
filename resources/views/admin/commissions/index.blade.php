@extends('layouts.app')

@section('title', 'Gestion des Commissions')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title m-0">Configuration des Taux de Commission</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.commissions.update') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label class="form-label">Taux de commission par défaut (%) <span class="text-danger">*</span></label>
                <input type="number" name="default_rate" step="0.01" min="0" max="100" class="form-control" value="{{ $defaultRate }}" required>
                <small class="text-muted">Ce taux sera appliqué aux nouvelles catégories</small>
            </div>

            <h6 class="mb-3">Taux par Catégorie de Service</h6>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Catégorie</th>
                            <th>Taux actuel (%)</th>
                            <th>Nouveau taux (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $categorie)
                        <tr>
                            <td><strong>{{ $categorie->nom }}</strong></td>
                            <td>{{ number_format($categorie->commission_rate, 2) }}%</td>
                            <td>
                                <input type="number" name="categories[{{ $categorie->id }}]" step="0.01" min="0" max="100" class="form-control" value="{{ $categorie->commission_rate }}" required>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bx bx-save"></i> Enregistrer les modifications
                </button>
            </div>
        </form>

        <hr class="my-4">

        <div class="d-flex gap-2">
            <a href="{{ route('admin.commissions.statistics') }}" class="btn btn-outline-info">
                <i class="bx bx-bar-chart"></i> Statistiques
            </a>
            <a href="{{ route('admin.commissions.reports') }}" class="btn btn-outline-primary">
                <i class="bx bx-file"></i> Rapports
            </a>
        </div>
    </div>
</div>
@endsection

