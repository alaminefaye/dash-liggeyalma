@extends('layouts.app')

@section('title', 'Détails Contournement')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title m-0">Contournement #{{ $contournement->id }}</h5>
                <div>
                    @php
                        $statusColors = [
                            'detecte' => 'warning',
                            'confirme' => 'danger',
                            'rejete' => 'success',
                        ];
                        $statusColor = $statusColors[$contournement->statut] ?? 'secondary';
                    @endphp
                    <span class="badge bg-label-{{ $statusColor }}" style="font-size: 1rem;">
                        {{ ucfirst($contournement->statut) }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Type de contournement :</strong>
                        <p>
                            <span class="badge bg-label-danger">
                                {{ ucfirst(str_replace('_', ' ', $contournement->type)) }}
                            </span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <strong>Date de détection :</strong>
                        <p>{{ $contournement->created_at->format('d/m/Y à H:i') }}</p>
                    </div>
                </div>

                <div class="mb-3">
                    <strong>Description :</strong>
                    <p>{{ $contournement->description }}</p>
                </div>

                @if($contournement->preuves && count($contournement->preuves) > 0)
                <div class="mb-3">
                    <strong>Preuves :</strong>
                    <div class="mt-2">
                        @foreach($contournement->preuves as $key => $preuve)
                            <div class="alert alert-info">
                                <strong>{{ ucfirst(str_replace('_', ' ', $key)) }} :</strong>
                                <pre class="mb-0">{{ is_array($preuve) ? json_encode($preuve, JSON_PRETTY_PRINT) : $preuve }}</pre>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($contournement->sanction_appliquee)
                <div class="alert alert-danger">
                    <strong>⚠️ Sanctions appliquées :</strong>
                    <p class="mb-0">{{ $contournement->sanction_appliquee }}</p>
                </div>
                @endif

                @if($contournement->commande)
                <div class="mb-3">
                    <strong>Commande associée :</strong>
                    <p>
                        <a href="{{ route('admin.commandes.show', $contournement->commande) }}" class="btn btn-sm btn-outline-primary">
                            Voir la commande #{{ $contournement->commande->id }}
                        </a>
                    </p>
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
                        <img src="{{ $contournement->prestataire->user->photo ?? asset('assets/img/avatars/1.png') }}" alt class="w-px-40 h-auto rounded-circle" />
                    </div>
                    <div>
                        <h6 class="mb-0">{{ $contournement->prestataire->user->name }}</h6>
                        <small class="text-muted">{{ $contournement->prestataire->metier }}</small>
                    </div>
                </div>
                <div class="mb-2">
                    <strong>Email :</strong>
                    <p>{{ $contournement->prestataire->user->email }}</p>
                </div>
                <div class="mb-2">
                    <strong>Statut compte :</strong>
                    <p>
                        @php
                            $statusColors = [
                                'active' => 'success',
                                'suspended' => 'warning',
                                'blocked' => 'danger',
                            ];
                            $color = $statusColors[$contournement->prestataire->user->status] ?? 'secondary';
                        @endphp
                        <span class="badge bg-label-{{ $color }}">{{ ucfirst($contournement->prestataire->user->status) }}</span>
                    </p>
                </div>
                <div class="mb-2">
                    <strong>Nombre de contournements confirmés :</strong>
                    <p>
                        <span class="badge bg-label-danger">
                            {{ $contournement->prestataire->contournements()->where('statut', 'confirme')->count() }}
                        </span>
                    </p>
                </div>
                <div class="d-grid gap-2 mt-3">
                    <a href="{{ route('admin.prestataires.show', $contournement->prestataire) }}" class="btn btn-outline-primary">
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
                @if($contournement->statut === 'detecte')
                    <form action="{{ route('admin.contournements.validate', $contournement) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-danger w-100" onclick="return confirm('⚠️ Confirmer ce contournement ? Les sanctions seront appliquées automatiquement.')">
                            <i class="bx bx-check"></i> Confirmer (Appliquer sanctions)
                        </button>
                    </form>
                    <form action="{{ route('admin.contournements.reject', $contournement) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-success w-100" onclick="return confirm('Rejeter ce contournement (faux positif) ?')">
                            <i class="bx bx-x"></i> Rejeter (Faux positif)
                        </button>
                    </form>
                    <form action="{{ route('admin.contournements.warn', $contournement) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-warning w-100">
                            <i class="bx bx-error"></i> Avertir le prestataire
                        </button>
                    </form>
                    <form action="{{ route('admin.contournements.block', $contournement) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-danger w-100" onclick="return confirm('⚠️ Bloquer définitivement ce prestataire ?')">
                            <i class="bx bx-block"></i> Bloquer le compte
                        </button>
                    </form>
                @elseif($contournement->statut === 'confirme')
                    <div class="alert alert-danger">
                        <strong>Contournement confirmé</strong>
                        <p class="mb-0">Les sanctions ont été appliquées.</p>
                    </div>
                @else
                    <div class="alert alert-success">
                        <strong>Contournement rejeté</strong>
                        <p class="mb-0">Faux positif confirmé.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

