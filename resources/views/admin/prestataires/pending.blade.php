@extends('layouts.app')

@section('title', 'Prestataires en Attente de Validation')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0">Prestataires en Attente de Validation</h5>
        <a href="{{ route('admin.prestataires.index') }}" class="btn btn-label-secondary">
            <i class="bx bx-arrow-back"></i> Retour
        </a>
    </div>
    <div class="card-body">
        @if($prestataires->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Métier</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Date demande</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($prestataires as $prestataire)
                        <tr>
                            <td>#{{ $prestataire->id }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-2">
                                        <img src="{{ $prestataire->user->photo ?? asset('assets/img/avatars/1.png') }}" alt class="w-px-40 h-auto rounded-circle" />
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $prestataire->user->name }}</h6>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $prestataire->metier }}</td>
                            <td>{{ $prestataire->user->email }}</td>
                            <td>{{ $prestataire->user->phone ?? '-' }}</td>
                            <td>{{ $prestataire->created_at->format('d/m/Y') }}</td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.prestataires.show', $prestataire) }}" class="btn btn-sm btn-info">
                                        <i class="bx bx-show"></i> Voir
                                    </a>
                                    <form action="{{ route('admin.prestataires.validate', $prestataire) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Valider ce compte prestataire ?')">
                                            <i class="bx bx-check"></i> Valider
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $prestataire->id }}">
                                        <i class="bx bx-x"></i> Refuser
                                    </button>
                                </div>

                                <!-- Modal Refus -->
                                <div class="modal fade" id="rejectModal{{ $prestataire->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('admin.prestataires.reject', $prestataire) }}" method="POST">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Refuser le compte prestataire</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Motif du refus <span class="text-danger">*</span></label>
                                                        <textarea name="motif" class="form-control" rows="4" required placeholder="Expliquez pourquoi ce compte est refusé..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Annuler</button>
                                                    <button type="submit" class="btn btn-danger">Confirmer le refus</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $prestataires->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="bx bx-check-circle text-success" style="font-size: 4rem;"></i>
                <p class="mt-3 text-muted">Aucun prestataire en attente de validation</p>
            </div>
        @endif
    </div>
</div>
@endsection

