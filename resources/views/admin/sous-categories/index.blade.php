@extends('layouts.app')

@section('title', 'Gestion des Sous-Catégories')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0">Liste des Sous-Catégories</h5>
        <a href="{{ route('admin.sous-categories.create') }}" class="btn btn-primary">
            <i class="bx bx-plus"></i> Nouvelle Sous-Catégorie
        </a>
    </div>
    <div class="card-body">
        <!-- Filtres -->
        <form method="GET" action="{{ route('admin.sous-categories.index') }}" class="mb-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Rechercher (nom, description)" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="categorie_id" class="form-select">
                        <option value="">Toutes les catégories</option>
                        @foreach($categories as $categorie)
                            <option value="{{ $categorie->id }}" {{ request('categorie_id') == $categorie->id ? 'selected' : '' }}>
                                {{ $categorie->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="active" class="form-select">
                        <option value="">Tous</option>
                        <option value="1" {{ request('active') == '1' ? 'selected' : '' }}>Actives</option>
                        <option value="0" {{ request('active') == '0' ? 'selected' : '' }}>Inactives</option>
                    </select>
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
                        <th>Catégorie</th>
                        <th>Nom</th>
                        <th>Prix Fixe</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sousCategories as $sousCategorie)
                    <tr>
                        <td>#{{ $sousCategorie->id }}</td>
                        <td>
                            <span class="badge bg-label-primary">{{ $sousCategorie->categorieService->nom }}</span>
                        </td>
                        <td>
                            <strong>{{ $sousCategorie->nom }}</strong>
                            @if($sousCategorie->description)
                                <br><small class="text-muted">{{ Str::limit($sousCategorie->description, 50) }}</small>
                            @endif
                        </td>
                        <td>
                            @if($sousCategorie->prix_fixe)
                                <span class="badge bg-label-success">{{ number_format($sousCategorie->prix_fixe, 0, ',', ' ') }} FCFA</span>
                            @else
                                <span class="badge bg-label-secondary">Variable</span>
                            @endif
                        </td>
                        <td>
                            @if($sousCategorie->active)
                                <span class="badge bg-label-success">Active</span>
                            @else
                                <span class="badge bg-label-danger">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.sous-categories.edit', $sousCategorie) }}" class="btn btn-sm btn-primary">
                                    <i class="bx bx-edit"></i>
                                </a>
                                <form action="{{ route('admin.sous-categories.destroy', $sousCategorie) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cette sous-catégorie ?')">
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
                        <td colspan="6" class="text-center text-muted">Aucune sous-catégorie trouvée</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $sousCategories->links() }}
        </div>
    </div>
</div>
@endsection

