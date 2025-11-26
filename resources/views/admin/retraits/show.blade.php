@extends('layouts.app')

@section('title', 'Détails Retrait')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title m-0">Retrait #{{ $retrait->id }}</h5>
                <div>
                    @php
                        $statusColors = [
                            'en_attente' => 'warning',
                            'valide' => 'success',
                            'refuse' => 'danger',
                        ];
                        $statusColor = $statusColors[$retrait->statut] ?? 'secondary';
                    @endphp
                    <span class="badge bg-label-{{ $statusColor }}" style="font-size: 1rem;">
                        {{ ucfirst($retrait->statut) }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Montant demandé :</strong>
                        <h4 class="text-primary">{{ number_format($retrait->montant, 0, ',', ' ') }} FCFA</h4>
                    </div>
                    <div class="col-md-4">
                        <strong>Frais de retrait :</strong>
                        <h4 class="text-warning">{{ number_format($retrait->frais_retrait, 0, ',', ' ') }} FCFA</h4>
                    </div>
                    <div class="col-md-4">
                        <strong>Montant net :</strong>
                        <h4 class="text-success">{{ number_format($retrait->montant_net, 0, ',', ' ') }} FCFA</h4>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Méthode de retrait :</strong>
                        <p>
                            <span class="badge bg-label-secondary">{{ ucfirst(str_replace('_', ' ', $retrait->methode)) }}</span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <strong>Numéro de compte :</strong>
                        <p><code>{{ $retrait->numero_compte }}</code></p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Date de demande :</strong>
                        <p>{{ $retrait->created_at->format('d/m/Y à H:i') }}</p>
                    </div>
                    @if($retrait->date_validation)
                    <div class="col-md-6">
                        <strong>Date de validation :</strong>
                        <p>{{ $retrait->date_validation->format('d/m/Y à H:i') }}</p>
                    </div>
                    @endif
                </div>

                @if($retrait->motif_refus)
                <div class="alert alert-danger">
                    <strong>Motif du refus :</strong>
                    <p class="mb-0">{{ $retrait->motif_refus }}</p>
                </div>
                @endif

                @if($retrait->validePar)
                <div class="mb-3">
                    <strong>Validé par :</strong>
                    <p>{{ $retrait->validePar->name }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Informations Prestataire -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title m-0">Prestataire</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="avatar avatar-sm me-2">
                        <img src="{{ $retrait->prestataire->user->photo ?? asset('assets/img/avatars/1.png') }}" alt class="w-px-40 h-auto rounded-circle" />
                    </div>
                    <div>
                        <h6 class="mb-0">{{ $retrait->prestataire->user->name }}</h6>
                        <small class="text-muted">{{ $retrait->prestataire->metier }}</small>
                    </div>
                </div>
                <div class="mb-2">
                    <strong>Solde actuel :</strong>
                    <p>
                        @if($retrait->prestataire->solde < 0)
                            <span class="badge bg-label-danger">{{ number_format($retrait->prestataire->solde, 0, ',', ' ') }} FCFA</span>
                        @else
                            <span class="badge bg-label-success">{{ number_format($retrait->prestataire->solde, 0, ',', ' ') }} FCFA</span>
                        @endif
                    </p>
                </div>
                <div class="d-grid gap-2 mt-3">
                    <a href="{{ route('admin.prestataires.show', $retrait->prestataire) }}" class="btn btn-outline-primary">
                        <i class="bx bx-user"></i> Voir le prestataire
                    </a>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title m-0">Actions</h5>
            </div>
            <div class="card-body">
                @if($retrait->statut === 'en_attente')
                    @if($retrait->prestataire->solde >= $retrait->montant)
                        <form action="{{ route('admin.retraits.validate', $retrait) }}" method="POST" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-success w-100" onclick="return confirm('Valider ce retrait ? Le solde du prestataire sera débité de {{ number_format($retrait->montant, 0, ',', ' ') }} FCFA.')">
                                <i class="bx bx-check"></i> Valider le retrait
                            </button>
                        </form>
                    @else
                        <div class="alert alert-danger">
                            <strong>⚠️ Solde insuffisant</strong>
                            <p class="mb-0">Le solde du prestataire ({{ number_format($retrait->prestataire->solde, 0, ',', ' ') }} FCFA) est inférieur au montant demandé.</p>
                        </div>
                    @endif
                    <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="bx bx-x"></i> Refuser
                    </button>
                @elseif($retrait->statut === 'valide')
                    <div class="alert alert-success">
                        <strong>Retrait validé</strong>
                        <p class="mb-0">Le {{ $retrait->date_validation->format('d/m/Y à H:i') }}</p>
                    </div>
                @else
                    <div class="alert alert-danger">
                        <strong>Retrait refusé</strong>
                        @if($retrait->motif_refus)
                            <p class="mb-0">{{ $retrait->motif_refus }}</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Refus -->
<div class="modal fade" id="rejectModal" tabindex="-1">
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
@endsection

