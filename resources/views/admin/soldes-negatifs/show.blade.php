@extends('layouts.app')

@section('title', 'Détails Solde Négatif')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title m-0">Informations Prestataire</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Nom :</strong>
                        <p>{{ $prestataire->user->name }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Email :</strong>
                        <p>{{ $prestataire->user->email }}</p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Métier :</strong>
                        <p>{{ $prestataire->metier }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Solde actuel :</strong>
                        <p>
                            <span class="badge bg-label-danger" style="font-size: 1.2rem;">
                                {{ number_format($prestataire->solde, 0, ',', ' ') }} FCFA
                            </span>
                        </p>
                    </div>
                </div>
                <div class="alert alert-danger">
                    <strong>Dette totale :</strong> {{ number_format(abs($prestataire->solde), 0, ',', ' ') }} FCFA
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title m-0">Historique des Transactions</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Montant</th>
                                <th>Commission</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($prestataire->transactions()->latest()->limit(20)->get() as $transaction)
                            <tr>
                                <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <span class="badge bg-label-info">{{ ucfirst($transaction->type) }}</span>
                                </td>
                                <td>{{ number_format($transaction->montant, 0, ',', ' ') }} FCFA</td>
                                <td>{{ number_format($transaction->commission ?? 0, 0, ',', ' ') }} FCFA</td>
                                <td>
                                    <span class="badge bg-label-{{ $transaction->statut === 'validee' ? 'success' : 'warning' }}">
                                        {{ ucfirst($transaction->statut) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Aucune transaction</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title m-0">Commandes Concernées</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Date</th>
                                <th>Client</th>
                                <th>Montant</th>
                                <th>Commission</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($prestataire->commandes()->latest()->limit(20)->get() as $commande)
                            <tr>
                                <td>#{{ $commande->id }}</td>
                                <td>{{ $commande->created_at->format('d/m/Y') }}</td>
                                <td>{{ $commande->client->user->name ?? 'N/A' }}</td>
                                <td>{{ number_format($commande->montant_total ?? 0, 0, ',', ' ') }} FCFA</td>
                                <td>{{ number_format($commande->montant_commission ?? 0, 0, ',', ' ') }} FCFA</td>
                                <td>
                                    <span class="badge bg-label-{{ $commande->statut === 'terminee' ? 'success' : 'warning' }}">
                                        {{ ucfirst(str_replace('_', ' ', $commande->statut)) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">Aucune commande</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title m-0">Actions</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Statistiques</strong>
                    <ul class="list-unstyled mt-2">
                        <li>Dette : <strong class="text-danger">{{ number_format(abs($stats['dette']), 0, ',', ' ') }} FCFA</strong></li>
                        <li>Transactions : {{ number_format($stats['transactions'], 0, ',', ' ') }} FCFA</li>
                        <li>Commandes totales : {{ $stats['commandes_total'] }}</li>
                    </ul>
                </div>

                <hr>

                <form action="{{ route('admin.soldes-negatifs.force-payment', $prestataire) }}" method="POST" class="mb-2">
                    @csrf
                    <button type="submit" class="btn btn-warning w-100" onclick="return confirm('Forcer le paiement de la commission ?')">
                        <i class="bx bx-money"></i> Forcer le paiement
                    </button>
                </form>

                <form action="{{ route('admin.soldes-negatifs.block', $prestataire) }}" method="POST" class="mb-2">
                    @csrf
                    <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Bloquer ce compte jusqu\'au paiement ?')">
                        <i class="bx bx-block"></i> Bloquer le compte
                    </button>
                </form>

                <form action="{{ route('admin.soldes-negatifs.send-reminder', $prestataire) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-info w-100">
                        <i class="bx bx-envelope"></i> Envoyer un rappel
                    </button>
                </form>

                <hr>

                <a href="{{ route('admin.prestataires.show', $prestataire) }}" class="btn btn-outline-primary w-100">
                    <i class="bx bx-show"></i> Voir le profil complet
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

