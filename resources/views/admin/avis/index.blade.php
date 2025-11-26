@extends('layouts.app')

@section('title', 'Gestion des Avis')

@section('content')
<div class="row mb-4">
    <!-- Statistiques -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <span class="fw-semibold d-block mb-1">Total Avis</span>
                <h3 class="card-title mb-2">{{ $stats['total'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <span class="fw-semibold d-block mb-1">Note Moyenne</span>
                <h3 class="card-title mb-2">{{ number_format($stats['note_moyenne'], 1) }}/5.0</h3>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <span class="fw-semibold d-block mb-1">5 Étoiles</span>
                <h3 class="card-title mb-2 text-success">{{ $stats['avis_5_etoiles'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <span class="fw-semibold d-block mb-1">1 Étoile</span>
                <h3 class="card-title mb-2 text-danger">{{ $stats['avis_1_etoile'] }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title m-0">Liste des Avis</h5>
    </div>
    <div class="card-body">
        <!-- Filtres -->
        <form method="GET" action="{{ route('admin.avis.index') }}" class="mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Rechercher (ID, client, prestataire)" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="note" class="form-select">
                        <option value="">Toutes les notes</option>
                        <option value="5" {{ request('note') == '5' ? 'selected' : '' }}>5 étoiles</option>
                        <option value="4" {{ request('note') == '4' ? 'selected' : '' }}>4 étoiles</option>
                        <option value="3" {{ request('note') == '3' ? 'selected' : '' }}>3 étoiles</option>
                        <option value="2" {{ request('note') == '2' ? 'selected' : '' }}>2 étoiles</option>
                        <option value="1" {{ request('note') == '1' ? 'selected' : '' }}>1 étoile</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
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
                        <th>Client</th>
                        <th>Prestataire</th>
                        <th>Note</th>
                        <th>Commentaire</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($avis as $avi)
                    <tr>
                        <td>#{{ $avi->id }}</td>
                        <td>{{ $avi->client->user->name ?? 'N/A' }}</td>
                        <td>{{ $avi->prestataire->user->name ?? 'N/A' }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="bx {{ $i <= $avi->note ? 'bxs-star text-warning' : 'bx-star text-muted' }}"></i>
                                @endfor
                                <span class="ms-2">({{ $avi->note }}/5)</span>
                            </div>
                        </td>
                        <td>
                            @if($avi->commentaire)
                                <small>{{ Str::limit($avi->commentaire, 50) }}</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $avi->created_at->format('d/m/Y') }}</td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.avis.show', $avi) }}" class="btn btn-sm btn-info">
                                    <i class="bx bx-show"></i>
                                </a>
                                <form action="{{ route('admin.avis.destroy', $avi) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cet avis ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">Aucun avis trouvé</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $avis->links() }}
        </div>
    </div>
</div>
@endsection

