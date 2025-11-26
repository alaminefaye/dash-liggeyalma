@extends('layouts.app')

@section('title', 'Gestion des Retraits')

@section('content')
<div class="row mb-4">
    <!-- Statistiques -->
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <span class="fw-semibold d-block mb-1">Retraits en attente</span>
                <h3 class="card-title mb-2">{{ $stats['en_attente'] }}</h3>
                <small class="text-warning">Total: {{ number_format($stats['total_montant_attente'], 0, ',', ' ') }} FCFA</small>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <span class="fw-semibold d-block mb-1">Retraits validés</span>
                <h3 class="card-title mb-2">{{ number_format($stats['valides'], 0, ',', ' ') }} FCFA</h3>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title m-0">Liste des Retraits</h5>
    </div>
    <div class="card-body">
        <!-- Filtres -->
        <form method="GET" action="{{ route('admin.retraits.index') }}" class="mb-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Rechercher (ID, prestataire)" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="statut" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="en_attente" {{ request('statut') == 'en_attente' ? 'selected' : '' }}>En attente</option>
                        <option value="valide" {{ request('statut') == 'valide' ? 'selected' : '' }}>Validé</option>
                        <option value="refuse" {{ request('statut') == 'refuse' ? 'selected' : '' }}>Refusé</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="methode" class="form-select">
                        <option value="">Toutes méthodes</option>
                        <option value="mobile_money" {{ request('methode') == 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                        <option value="virement" {{ request('methode') == 'virement' ? 'selected' : '' }}>Virement</option>
                        <option value="especes" {{ request('methode') == 'especes' ? 'selected' : '' }}>Espèces</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bx bx-search"></i>
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
                        <th>Prestataire</th>
                        <th>Montant</th>
                        <th>Frais</th>
                        <th>Net</th>
                        <th>Méthode</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($retraits as $retrait)
                    <tr>
                        <td>#{{ $retrait->id }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-2">
                                    <img src="{{ $retrait->prestataire->user->photo ?? asset('assets/img/avatars/1.png') }}" alt class="w-px-40 h-auto rounded-circle" />
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $retrait->prestataire->user->name }}</h6>
                                    <small class="text-muted">Solde: {{ number_format($retrait->prestataire->solde, 0, ',', ' ') }} FCFA</small>
                                </div>
                            </div>
                        </td>
                        <td><strong>{{ number_format($retrait->montant, 0, ',', ' ') }} FCFA</strong></td>
                        <td>{{ number_format($retrait->frais_retrait, 0, ',', ' ') }} FCFA</td>
                        <td><strong class="text-success">{{ number_format($retrait->montant_net, 0, ',', ' ') }} FCFA</strong></td>
                        <td>
                            <span class="badge bg-label-secondary">{{ ucfirst(str_replace('_', ' ', $retrait->methode)) }}</span>
                        </td>
                        <td>
                            @php
                                $statusColors = [
                                    'en_attente' => 'warning',
                                    'valide' => 'success',
                                    'refuse' => 'danger',
                                ];
                                $statusColor = $statusColors[$retrait->statut] ?? 'secondary';
                            @endphp
                            <span class="badge bg-label-{{ $statusColor }}">{{ ucfirst($retrait->statut) }}</span>
                        </td>
                        <td>{{ $retrait->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.retraits.show', $retrait) }}" class="btn btn-sm btn-info">
                                    <i class="bx bx-show"></i>
                                </a>
                                @if($retrait->statut === 'en_attente')
                                    <form action="{{ route('admin.retraits.validate', $retrait) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Valider ce retrait ? Le solde du prestataire sera débité.')">
                                            <i class="bx bx-check"></i>
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $retrait->id }}">
                                        <i class="bx bx-x"></i>
                                    </button>
                                @endif
                            </div>

                            <!-- Modal Refus -->
                            <div class="modal fade" id="rejectModal{{ $retrait->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('admin.retraits.reject', $retrait) }}" method="POST">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Refuser le retrait</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Motif du refus <span class="text-danger">*</span></label>
                                                    <textarea name="motif_refus" class="form-control" rows="3" required></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Annuler</button>
                                                <button type="submit" class="btn btn-danger">Refuser</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted">Aucun retrait trouvé</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $retraits->links() }}
        </div>
    </div>
</div>
@endsection

