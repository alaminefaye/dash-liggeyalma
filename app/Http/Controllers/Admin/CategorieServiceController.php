<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategorieService;
use Illuminate\Http\Request;

class CategorieServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = CategorieService::query();

        // Recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('nom', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }

        // Filtre par statut actif
        if ($request->filled('active')) {
            $query->where('active', $request->active == '1');
        }

        $categories = $query->withCount('sousCategories')->latest()->paginate(15);

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255|unique:categorie_services,nom',
            'description' => 'nullable|string',
            'icone' => 'nullable|string|max:50',
            'couleur' => 'nullable|string|max:7',
            'prix_fixe' => 'boolean',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'active' => 'boolean',
        ]);

        CategorieService::create($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Catégorie créée avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(CategorieService $category)
    {
        $category->load(['sousCategories', 'commandes.prestataire.user', 'commandes.client.user']);
        
        $stats = [
            'sous_categories_total' => $category->sousCategories()->count(),
            'commandes_total' => $category->commandes()->count(),
            'commandes_terminees' => $category->commandes()->where('statut', 'terminee')->count(),
            'revenus_total' => $category->commandes()->where('statut', 'terminee')->sum('montant_total'),
        ];

        return view('admin.categories.show', compact('category', 'stats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CategorieService $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CategorieService $category)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255|unique:categorie_services,nom,' . $category->id,
            'description' => 'nullable|string',
            'icone' => 'nullable|string|max:50',
            'couleur' => 'nullable|string|max:7',
            'prix_fixe' => 'boolean',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'active' => 'boolean',
        ]);

        $category->update($validated);

        return redirect()->route('admin.categories.show', $category)
            ->with('success', 'Catégorie mise à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CategorieService $category)
    {
        // Vérifier s'il y a des sous-catégories
        if ($category->sousCategories()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Impossible de supprimer cette catégorie car elle contient des sous-catégories.');
        }

        // Vérifier s'il y a des commandes
        if ($category->commandes()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Impossible de supprimer cette catégorie car elle est utilisée dans des commandes.');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Catégorie supprimée avec succès.');
    }
}
