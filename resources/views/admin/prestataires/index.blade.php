@extends('layouts.app')

@section('title', 'Gestion des Prestataires')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0">Liste des Prestataires</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.prestataires.export') }}" class="btn btn-success">
                <i class="bx bx-file"></i> Export Excel
            </a>
            <a href="{{ route('admin.prestataires.pending') }}" class="btn btn-warning">
                <i class="bx bx-time"></i> En attente ({{ \App\Models\Prestataire::where('statut_inscription', 'en_attente')->count() }})
            </a>
            <a href="{{ route('admin.prestataires.create') }}" class="btn btn-primary">
                <i class="bx bx-plus"></i> Nouveau Prestataire
            </a>
        </div>
    </div>
    <div class="card-body">
        <!-- Filtres -->
        <form method="GET" action="{{ route('admin.prestataires.index') }}" class="mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Rechercher (nom, email, métier)" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actif</option>
                        <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspendu</option>
                        <option value="blocked" {{ request('status') == 'blocked' ? 'selected' : '' }}>Bloqué</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="statut_inscription" class="form-select">
                        <option value="">Tous</option>
                        <option value="en_attente" {{ request('statut_inscription') == 'en_attente' ? 'selected' : '' }}>En attente</option>
                        <option value="valide" {{ request('statut_inscription') == 'valide' ? 'selected' : '' }}>Validé</option>
                        <option value="refuse" {{ request('statut_inscription') == 'refuse' ? 'selected' : '' }}>Refusé</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="solde_negatif" class="form-select">
                        <option value="">Tous</option>
                        <option value="1" {{ request('solde_negatif') == '1' ? 'selected' : '' }}>Solde négatif</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bx bx-search"></i> Rechercher
                    </button>
                </div>
            </div>
        </form>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Métier</th>
                        <th>Email</th>
                        <th>Statut</th>
                        <th>Solde</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($prestataires as $prestataire)
                    <tr>
                        <td>#{{ $prestataire->id }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-2">
                                    <img src="{{ $prestataire->user->photo ?? asset('assets/img/avatars/1.png') }}" alt class="w-px-40 h-auto rounded-circle" />
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $prestataire->user->name }}</h6>
                                    <small class="text-muted">
                                        @if($prestataire->statut_inscription === 'en_attente')
                                            <span class="badge bg-label-warning">En attente</span>
                                        @elseif($prestataire->statut_inscription === 'valide')
                                            <span class="badge bg-label-success">Validé</span>
                                        @else
                                            <span class="badge bg-label-danger">Refusé</span>
                                        @endif
                                    </small>
                                </div>
                            </div>
                        </td>
                        <td>{{ $prestataire->metier }}</td>
                        <td>{{ $prestataire->user->email }}</td>
                        <td>
                            @php
                                $statusColors = [
                                    'active' => 'success',
                                    'suspended' => 'warning',
                                    'blocked' => 'danger',
                                ];
                                $color = $statusColors[$prestataire->user->status] ?? 'secondary';
                            @endphp
                            <span class="badge bg-label-{{ $color }}">{{ ucfirst($prestataire->user->status) }}</span>
                        </td>
                        <td>
                            @if($prestataire->solde < 0)
                                <span class="badge bg-label-danger">{{ number_format($prestataire->solde, 0, ',', ' ') }} FCFA</span>
                            @else
                                <span class="badge bg-label-success">{{ number_format($prestataire->solde, 0, ',', ' ') }} FCFA</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.prestataires.show', $prestataire) }}" class="btn btn-sm btn-info">
                                    <i class="bx bx-show"></i>
                                </a>
                                <a href="{{ route('admin.prestataires.edit', $prestataire) }}" class="btn btn-sm btn-primary">
                                    <i class="bx bx-edit"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">Aucun prestataire trouvé</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $prestataires->links() }}
        </div>
    </div>
</div>
@endsection

