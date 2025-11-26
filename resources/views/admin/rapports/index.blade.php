@extends('layouts.app')

@section('title', 'Rapports')

@section('content')
<div class="row">
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card">
            <div class="card-body text-center">
                <i class="bx bx-money text-primary" style="font-size: 3rem;"></i>
                <h5 class="mt-3">Rapport Financier</h5>
                <p class="text-muted">Revenus, commissions, retraits</p>
                <a href="{{ route('admin.rapports.financier') }}" class="btn btn-primary">
                    <i class="bx bx-show"></i> Voir le rapport
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card">
            <div class="card-body text-center">
                <i class="bx bx-group text-info" style="font-size: 3rem;"></i>
                <h5 class="mt-3">Rapport Utilisateurs</h5>
                <p class="text-muted">Clients, prestataires, inscriptions</p>
                <a href="{{ route('admin.rapports.utilisateurs') }}" class="btn btn-info">
                    <i class="bx bx-show"></i> Voir le rapport
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card">
            <div class="card-body text-center">
                <i class="bx bx-briefcase text-success" style="font-size: 3rem;"></i>
                <h5 class="mt-3">Rapport Services</h5>
                <p class="text-muted">Catégories, top prestataires</p>
                <a href="{{ route('admin.rapports.services') }}" class="btn btn-success">
                    <i class="bx bx-show"></i> Voir le rapport
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title m-0">Exports</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.rapports.export') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Type de rapport</label>
                <select name="type" class="form-select" required>
                    <option value="financier">Financier</option>
                    <option value="commandes">Commandes</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Date début</label>
                <input type="date" name="date_from" class="form-control" value="{{ now()->startOfMonth()->format('Y-m-d') }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Date fin</label>
                <input type="date" name="date_to" class="form-control" value="{{ now()->endOfMonth()->format('Y-m-d') }}" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bx bx-download"></i> Exporter CSV
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

