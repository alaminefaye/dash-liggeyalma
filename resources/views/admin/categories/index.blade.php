@extends('layouts.app')

@section('title', 'Gestion des Catégories de Services')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0">Liste des Catégories de Services</h5>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
            <i class="bx bx-plus"></i> Nouvelle Catégorie
        </a>
    </div>
    <div class="card-body">
        <!-- Filtres -->
        <form method="GET" action="{{ route('admin.categories.index') }}" class="mb-4">
            <div class="row g-3">
                <div class="col-md-8">
                    <input type="text" name="search" class="form-control" placeholder="Rechercher (nom, description)" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="active" class="form-select">
                        <option value="">Tous</option>
                        <option value="1" {{ request('active') == '1' ? 'selected' : '' }}>Actives</option>
                        <option value="0" {{ request('active') == '0' ? 'selected' : '' }}>Inactives</option>
                    </select>
                </div>
                <div class="col-md-2">
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
                        <th>Icône</th>
                        <th>Couleur</th>
                        <th>Sous-catégories</th>
                        <th>Prix Fixe</th>
                        <th>Commission</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                    <tr>
                        <td>#{{ $category->id }}</td>
                        <td>
                            <strong>{{ $category->nom }}</strong>
                            @if($category->description)
                                <br><small class="text-muted">{{ Str::limit($category->description, 50) }}</small>
                            @endif
                        </td>
                        <td>
                            @if($category->icone)
                                <i class="bx {{ $category->icone }}" style="font-size: 1.5rem;"></i>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($category->couleur)
                                <span class="badge" style="background-color: {{ $category->couleur }}; color: white;">
                                    {{ $category->couleur }}
                                </span>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-label-info">{{ $category->sous_categories_count }}</span>
                        </td>
                        <td>
                            @if($category->prix_fixe)
                                <span class="badge bg-label-success">Oui</span>
                            @else
                                <span class="badge bg-label-secondary">Non</span>
                            @endif
                        </td>
                        <td>{{ number_format($category->commission_rate, 2) }}%</td>
                        <td>
                            @if($category->active)
                                <span class="badge bg-label-success">Active</span>
                            @else
                                <span class="badge bg-label-danger">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.categories.show', $category) }}" class="btn btn-sm btn-info">
                                    <i class="bx bx-show"></i>
                                </a>
                                <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm btn-primary">
                                    <i class="bx bx-edit"></i>
                                </a>
                                <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cette catégorie ?')">
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
                        <td colspan="9" class="text-center text-muted">Aucune catégorie trouvée</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $categories->links() }}
        </div>
    </div>
</div>
@endsection

