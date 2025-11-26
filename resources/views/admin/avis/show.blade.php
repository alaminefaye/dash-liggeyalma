@extends('layouts.app')

@section('title', 'Détails Avis')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title m-0">Avis #{{ $avi->id }}</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Client :</strong>
                        <p>
                            <a href="{{ route('admin.clients.show', $avi->client) }}">
                                {{ $avi->client->user->name }}
                            </a>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <strong>Prestataire :</strong>
                        <p>
                            <a href="{{ route('admin.prestataires.show', $avi->prestataire) }}">
                                {{ $avi->prestataire->user->name }}
                            </a>
                        </p>
                    </div>
                </div>

                <div class="mb-3">
                    <strong>Note :</strong>
                    <div class="mt-2">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="bx {{ $i <= $avi->note ? 'bxs-star text-warning' : 'bx-star text-muted' }}" style="font-size: 1.5rem;"></i>
                        @endfor
                        <span class="ms-2">({{ $avi->note }}/5)</span>
                    </div>
                </div>

                @if($avi->commentaire)
                <div class="mb-3">
                    <strong>Commentaire :</strong>
                    <p class="mt-2">{{ $avi->commentaire }}</p>
                </div>
                @endif

                @if($avi->criteres && count($avi->criteres) > 0)
                <div class="mb-3">
                    <strong>Critères :</strong>
                    <div class="mt-2">
                        @foreach($avi->criteres as $critere => $note)
                            <div class="mb-2">
                                <strong>{{ ucfirst(str_replace('_', ' ', $critere)) }} :</strong>
                                <div class="d-inline-block ms-2">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="bx {{ $i <= $note ? 'bxs-star text-warning' : 'bx-star text-muted' }}"></i>
                                    @endfor
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($avi->photos && count($avi->photos) > 0)
                <div class="mb-3">
                    <strong>Photos :</strong>
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        @foreach($avi->photos as $photo)
                            <img src="{{ asset('storage/' . $photo) }}" alt="Photo" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
                        @endforeach
                    </div>
                </div>
                @endif

                @if($avi->reponse_prestataire)
                <div class="alert alert-info">
                    <strong>Réponse du prestataire :</strong>
                    <p class="mb-0 mt-2">{{ $avi->reponse_prestataire }}</p>
                    @if($avi->date_reponse)
                        <small class="text-muted">Le {{ $avi->date_reponse->format('d/m/Y à H:i') }}</small>
                    @endif
                </div>
                @endif

                <div class="mb-3">
                    <strong>Date de publication :</strong>
                    <p>{{ $avi->created_at->format('d/m/Y à H:i') }}</p>
                </div>

                @if($avi->commande)
                <div class="mb-3">
                    <strong>Commande associée :</strong>
                    <p>
                        <a href="{{ route('admin.commandes.show', $avi->commande) }}" class="btn btn-sm btn-outline-primary">
                            Voir la commande #{{ $avi->commande->id }}
                        </a>
                    </p>
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
                <form action="{{ route('admin.avis.destroy', $avi) }}" method="POST" onsubmit="return confirm('Supprimer cet avis ?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger w-100">
                        <i class="bx bx-trash"></i> Supprimer l'avis
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

