@extends('layouts.app')

@section('title', 'Gestion des Soldes Négatifs')

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <div class="alert alert-danger">
            <h5 class="alert-heading">⚠️ Total des dettes</h5>
            <p class="mb-0"><strong>{{ number_format(abs($totalDette), 0, ',', ' ') }} FCFA</strong></p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title m-0">Prestataires avec Solde Négatif</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Prestataire</th>
                        <th>Métier</th>
                        <th>Solde</th>
                        <th>Dette</th>
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
                                    <small class="text-muted">{{ $prestataire->user->email }}</small>
                                </div>
                            </div>
                        </td>
                        <td>{{ $prestataire->metier }}</td>
                        <td>
                            <span class="badge bg-label-danger">{{ number_format($prestataire->solde, 0, ',', ' ') }} FCFA</span>
                        </td>
                        <td>
                            <strong class="text-danger">{{ number_format(abs($prestataire->solde), 0, ',', ' ') }} FCFA</strong>
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.soldes-negatifs.show', $prestataire) }}" class="btn btn-sm btn-info">
                                    <i class="bx bx-show"></i>
                                </a>
                                <form action="{{ route('admin.soldes-negatifs.block', $prestataire) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bloquer ce compte jusqu\'au paiement ?')">
                                        <i class="bx bx-block"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">Aucun solde négatif</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $prestataires->links() }}
        </div>
    </div>
</div>
@endsection

