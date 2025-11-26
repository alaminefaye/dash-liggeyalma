@extends('layouts.app')

@section('title', 'Détails Transaction')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title m-0">Transaction #{{ $transaction->id }}</h5>
                <div>
                    @php
                        $statusColors = [
                            'en_attente' => 'warning',
                            'validee' => 'success',
                            'refusee' => 'danger',
                        ];
                        $statusColor = $statusColors[$transaction->statut] ?? 'secondary';
                    @endphp
                    <span class="badge bg-label-{{ $statusColor }}" style="font-size: 1rem;">
                        {{ ucfirst($transaction->statut) }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Type :</strong>
                        <p>
                            @php
                                $typeColors = [
                                    'paiement' => 'primary',
                                    'retrait' => 'info',
                                    'commission' => 'warning',
                                ];
                                $typeColor = $typeColors[$transaction->type] ?? 'secondary';
                            @endphp
                            <span class="badge bg-label-{{ $typeColor }}">{{ ucfirst($transaction->type) }}</span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <strong>Date :</strong>
                        <p>{{ $transaction->created_at->format('d/m/Y à H:i') }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Montant :</strong>
                        <h4 class="text-primary">{{ number_format($transaction->montant, 0, ',', ' ') }} FCFA</h4>
                    </div>
                    <div class="col-md-6">
                        <strong>Commission :</strong>
                        <h4 class="text-info">{{ number_format($transaction->commission, 0, ',', ' ') }} FCFA</h4>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Méthode de paiement :</strong>
                        <p>
                            @if($transaction->methode_paiement)
                                <span class="badge bg-label-secondary">{{ ucfirst(str_replace('_', ' ', $transaction->methode_paiement)) }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6">
                        <strong>Référence externe :</strong>
                        <p>{{ $transaction->reference_externe ?? '-' }}</p>
                    </div>
                </div>

                @if($transaction->client)
                <div class="mb-3">
                    <strong>Client :</strong>
                    <p>
                        <a href="{{ route('admin.clients.show', $transaction->client) }}">
                            {{ $transaction->client->user->name }}
                        </a>
                    </p>
                </div>
                @endif

                @if($transaction->prestataire)
                <div class="mb-3">
                    <strong>Prestataire :</strong>
                    <p>
                        <a href="{{ route('admin.prestataires.show', $transaction->prestataire) }}">
                            {{ $transaction->prestataire->user->name }}
                        </a>
                    </p>
                </div>
                @endif

                @if($transaction->commande)
                <div class="mb-3">
                    <strong>Commande associée :</strong>
                    <p>
                        <a href="{{ route('admin.commandes.show', $transaction->commande) }}" class="btn btn-sm btn-outline-primary">
                            Voir la commande #{{ $transaction->commande->id }}
                        </a>
                    </p>
                </div>
                @endif

                @if($transaction->notes)
                <div class="mb-3">
                    <strong>Notes :</strong>
                    <p>{{ $transaction->notes }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Actions -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title m-0">Actions</h5>
            </div>
            <div class="card-body">
                @if($transaction->statut === 'en_attente')
                    <form action="{{ route('admin.transactions.validate', $transaction) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-success w-100" onclick="return confirm('Valider cette transaction ?')">
                            <i class="bx bx-check"></i> Valider
                        </button>
                    </form>
                    <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="bx bx-x"></i> Refuser
                    </button>
                @elseif($transaction->statut === 'validee' && $transaction->type === 'paiement')
                    <form action="{{ route('admin.transactions.refund', $transaction) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-warning w-100" onclick="return confirm('Rembourser cette transaction ?')">
                            <i class="bx bx-refund"></i> Rembourser
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Refus -->
<div class="modal fade" id="rejectModal" tabindex="-1">
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
@endsection

