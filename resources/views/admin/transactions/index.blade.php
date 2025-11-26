@extends('layouts.app')

@section('title', 'Gestion des Transactions')

@section('content')
<div class="row mb-4">
    <!-- Statistiques -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <span class="fw-semibold d-block mb-1">Total Transactions</span>
                <h3 class="card-title mb-2">{{ number_format($stats['total']) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <span class="fw-semibold d-block mb-1">Paiements</span>
                <h3 class="card-title mb-2">{{ number_format($stats['paiements'], 0, ',', ' ') }} FCFA</h3>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <span class="fw-semibold d-block mb-1">Commissions</span>
                <h3 class="card-title mb-2">{{ number_format($stats['commissions'], 0, ',', ' ') }} FCFA</h3>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <span class="fw-semibold d-block mb-1">Retraits</span>
                <h3 class="card-title mb-2">{{ number_format($stats['retraits'], 0, ',', ' ') }} FCFA</h3>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0">Liste des Transactions</h5>
        <a href="{{ route('admin.transactions.export') }}" class="btn btn-success">
            <i class="bx bx-file"></i> Export Excel
        </a>
    </div>
    <div class="card-body">
        <!-- Filtres -->
        <form method="GET" action="{{ route('admin.transactions.index') }}" class="mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Rechercher (ID, référence)" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="type" class="form-select">
                        <option value="">Tous les types</option>
                        <option value="paiement" {{ request('type') == 'paiement' ? 'selected' : '' }}>Paiement</option>
                        <option value="retrait" {{ request('type') == 'retrait' ? 'selected' : '' }}>Retrait</option>
                        <option value="commission" {{ request('type') == 'commission' ? 'selected' : '' }}>Commission</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="statut" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="en_attente" {{ request('statut') == 'en_attente' ? 'selected' : '' }}>En attente</option>
                        <option value="validee" {{ request('statut') == 'validee' ? 'selected' : '' }}>Validée</option>
                        <option value="refusee" {{ request('statut') == 'refusee' ? 'selected' : '' }}>Refusée</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="methode_paiement" class="form-select">
                        <option value="">Toutes méthodes</option>
                        <option value="cash" {{ request('methode_paiement') == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="mobile_money" {{ request('methode_paiement') == 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                        <option value="carte" {{ request('methode_paiement') == 'carte' ? 'selected' : '' }}>Carte</option>
                    </select>
                </div>
                <div class="col-md-1">
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
                        <th>Type</th>
                        <th>Client/Prestataire</th>
                        <th>Montant</th>
                        <th>Commission</th>
                        <th>Méthode</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                    <tr>
                        <td>#{{ $transaction->id }}</td>
                        <td>
                            @php
                                $typeColors = [
                                    'paiement' => 'primary',
                                    'retrait' => 'info',
                                    'commission' => 'warning',
                                ];
                                $typeColor = $typeColors[$transaction->type] ?? 'secondary';
                            @endphp
                            <span class="badge bg-label-{{ $typeColor }}">{{ ucfirst($transaction->type) }}</span>
                        </td>
                        <td>
                            @if($transaction->client)
                                <small>{{ $transaction->client->user->name }}</small>
                            @elseif($transaction->prestataire)
                                <small>{{ $transaction->prestataire->user->name }}</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td><strong>{{ number_format($transaction->montant, 0, ',', ' ') }} FCFA</strong></td>
                        <td>{{ number_format($transaction->commission, 0, ',', ' ') }} FCFA</td>
                        <td>
                            @if($transaction->methode_paiement)
                                <span class="badge bg-label-secondary">{{ ucfirst(str_replace('_', ' ', $transaction->methode_paiement)) }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @php
                                $statusColors = [
                                    'en_attente' => 'warning',
                                    'validee' => 'success',
                                    'refusee' => 'danger',
                                ];
                                $statusColor = $statusColors[$transaction->statut] ?? 'secondary';
                            @endphp
                            <span class="badge bg-label-{{ $statusColor }}">{{ ucfirst($transaction->statut) }}</span>
                        </td>
                        <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.transactions.show', $transaction) }}" class="btn btn-sm btn-info">
                                    <i class="bx bx-show"></i>
                                </a>
                                @if($transaction->statut === 'en_attente')
                                    <form action="{{ route('admin.transactions.validate', $transaction) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Valider cette transaction ?')">
                                            <i class="bx bx-check"></i>
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $transaction->id }}">
                                        <i class="bx bx-x"></i>
                                    </button>
                                @endif
                            </div>

                            <!-- Modal Refus -->
                            <div class="modal fade" id="rejectModal{{ $transaction->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('admin.transactions.reject', $transaction) }}" method="POST">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Refuser la transaction</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Motif du refus <span class="text-danger">*</span></label>
                                                    <textarea name="motif" class="form-control" rows="3" required></textarea>
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
                        <td colspan="9" class="text-center text-muted">Aucune transaction trouvée</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $transactions->links() }}
        </div>
    </div>
</div>
@endsection

