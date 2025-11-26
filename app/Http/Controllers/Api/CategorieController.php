<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CategorieService;
use App\Models\SousCategorieService;
use Illuminate\Http\Request;

class CategorieController extends Controller
{
    /**
     * Get all active categories
     */
    public function index()
    {
        $categories = CategorieService::where('active', true)
            ->with('sousCategories')
            ->get()
            ->map(function ($categorie) {
                return [
                    'id' => $categorie->id,
                    'nom' => $categorie->nom,
                    'description' => $categorie->description,
                    'icone' => $categorie->icone,
                    'couleur' => $categorie->couleur,
                    'prix_fixe' => $categorie->prix_fixe,
                    'sous_categories' => $categorie->sousCategories->map(function ($sousCat) {
                        return [
                            'id' => $sousCat->id,
                            'nom' => $sousCat->nom,
                            'description' => $sousCat->description,
                            'prix' => $sousCat->prix,
                        ];
                    }),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Get a single category with subcategories
     */
    public function show($id)
    {
        $categorie = CategorieService::where('active', true)
            ->with('sousCategories')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $categorie->id,
                'nom' => $categorie->nom,
                'description' => $categorie->description,
                'icone' => $categorie->icone,
                'couleur' => $categorie->couleur,
                'prix_fixe' => $categorie->prix_fixe,
                'sous_categories' => $categorie->sousCategories->map(function ($sousCat) {
                    return [
                        'id' => $sousCat->id,
                        'nom' => $sousCat->nom,
                        'description' => $sousCat->description,
                        'prix' => $sousCat->prix,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Get all subcategories
     */
    public function sousCategories()
    {
        $sousCategories = SousCategorieService::with('categorieService')
            ->get()
            ->map(function ($sousCat) {
                return [
                    'id' => $sousCat->id,
                    'nom' => $sousCat->nom,
                    'description' => $sousCat->description,
                    'prix' => $sousCat->prix,
                    'categorie_id' => $sousCat->categorie_service_id,
                    'categorie_nom' => $sousCat->categorieService->nom ?? null,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $sousCategories,
        ]);
    }

    /**
     * Get subcategories by category
     */
    public function sousCategoriesByCategorie($categorieId)
    {
        $sousCategories = SousCategorieService::where('categorie_service_id', $categorieId)
            ->get()
            ->map(function ($sousCat) {
                return [
                    'id' => $sousCat->id,
                    'nom' => $sousCat->nom,
                    'description' => $sousCat->description,
                    'prix' => $sousCat->prix,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $sousCategories,
        ]);
    }
}

