<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SousCategorieService;
use App\Models\CategorieService;
use Illuminate\Http\Request;

class SousCategorieServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = SousCategorieService::with('categorieService');

        // Recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('nom', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }

        // Filtre par catégorie
        if ($request->filled('categorie_id')) {
            $query->where('categorie_service_id', $request->categorie_id);
        }

        // Filtre par statut
        if ($request->filled('active')) {
            $query->where('active', $request->active == '1');
        }

        $sousCategories = $query->latest()->paginate(15);
        $categories = CategorieService::where('active', true)->get();

        return view('admin.sous-categories.index', compact('sousCategories', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = CategorieService::where('active', true)->get();
        return view('admin.sous-categories.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'categorie_service_id' => 'required|exists:categorie_services,id',
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'prix_fixe' => 'nullable|numeric|min:0',
            'active' => 'boolean',
        ]);

        SousCategorieService::create($validated);

        return redirect()->route('admin.sous-categories.index')
            ->with('success', 'Sous-catégorie créée avec succès.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SousCategorieService $sousCategorie)
    {
        $categories = CategorieService::where('active', true)->get();
        return view('admin.sous-categories.edit', compact('sousCategorie', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SousCategorieService $sousCategorie)
    {
        $validated = $request->validate([
            'categorie_service_id' => 'required|exists:categorie_services,id',
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'prix_fixe' => 'nullable|numeric|min:0',
            'active' => 'boolean',
        ]);

        $sousCategorie->update($validated);

        return redirect()->route('admin.sous-categories.index')
            ->with('success', 'Sous-catégorie mise à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SousCategorieService $sousCategorie)
    {
        // Vérifier s'il y a des commandes
        if ($sousCategorie->commandes()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Impossible de supprimer cette sous-catégorie car elle est utilisée dans des commandes.');
        }

        $sousCategorie->delete();

        return redirect()->route('admin.sous-categories.index')
            ->with('success', 'Sous-catégorie supprimée avec succès.');
    }
}
