@extends('layouts.app')

@section('title', 'Gestion des Clients')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0">Liste des Clients</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.clients.export') }}" class="btn btn-success">
                <i class="bx bx-file"></i> Export Excel
            </a>
            <a href="{{ route('admin.clients.create') }}" class="btn btn-primary">
                <i class="bx bx-plus"></i> Nouveau Client
            </a>
        </div>
    </div>
    <div class="card-body">
        <!-- Filtres -->
        <form method="GET" action="{{ route('admin.clients.index') }}" class="mb-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Rechercher (nom, email, téléphone)" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actif</option>
                        <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspendu</option>
                        <option value="blocked" {{ request('status') == 'blocked' ? 'selected' : '' }}>Bloqué</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" class="form-control" placeholder="Date début" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_to" class="form-control" placeholder="Date fin" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-1">
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
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Inscription</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clients as $client)
                    <tr>
                        <td>#{{ $client->id }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-2">
                                    <img src="{{ $client->user->photo ?? asset('assets/img/avatars/1.png') }}" alt class="w-px-40 h-auto rounded-circle" />
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $client->user->name }}</h6>
                                </div>
                            </div>
                        </td>
                        <td>{{ $client->user->email }}</td>
                        <td>{{ $client->user->phone ?? '-' }}</td>
                        <td>{{ $client->created_at->format('d/m/Y') }}</td>
                        <td>
                            @php
                                $statusColors = [
                                    'active' => 'success',
                                    'suspended' => 'warning',
                                    'blocked' => 'danger',
                                ];
                                $color = $statusColors[$client->user->status] ?? 'secondary';
                            @endphp
                            <span class="badge bg-label-{{ $color }}">{{ ucfirst($client->user->status) }}</span>
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.clients.show', $client) }}" class="btn btn-sm btn-info">
                                    <i class="bx bx-show"></i>
                                </a>
                                <a href="{{ route('admin.clients.edit', $client) }}" class="btn btn-sm btn-primary">
                                    <i class="bx bx-edit"></i>
                                </a>
                                @if($client->user->status === 'active')
                                    <form action="{{ route('admin.clients.suspend', $client) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Suspendre ce client ?')">
                                            <i class="bx bx-pause"></i>
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.clients.activate', $client) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Réactiver ce client ?')">
                                            <i class="bx bx-check"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">Aucun client trouvé</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $clients->links() }}
        </div>
    </div>
</div>
@endsection

