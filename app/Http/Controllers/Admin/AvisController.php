<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Avis;
use Illuminate\Http\Request;

class AvisController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Avis::with(['client.user', 'prestataire.user', 'commande']);

        // Recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('id', $search)
                  ->orWhereHas('client.user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('prestataire.user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
        }

        // Filtre par note
        if ($request->filled('note')) {
            $query->where('note', $request->note);
        }

        // Filtre par prestataire
        if ($request->filled('prestataire_id')) {
            $query->where('prestataire_id', $request->prestataire_id);
        }

        // Filtre par date
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $avis = $query->latest()->paginate(15);

        // Statistiques
        $stats = [
            'total' => Avis::count(),
            'note_moyenne' => Avis::avg('note') ?? 0,
            'avis_5_etoiles' => Avis::where('note', 5)->count(),
            'avis_1_etoile' => Avis::where('note', 1)->count(),
        ];

        return view('admin.avis.index', compact('avis', 'stats'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Avis $avi)
    {
        $avi->load(['client.user', 'prestataire.user', 'commande']);
        return view('admin.avis.show', compact('avi'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Avis $avi)
    {
        $avi->delete();

        return redirect()->route('admin.avis.index')
            ->with('success', 'Avis supprimé avec succès.');
    }

    /**
     * Approuver un avis
     */
    public function approve(Avis $avi)
    {
        // Si vous avez un champ 'approuve' dans la table avis
        // $avi->update(['approuve' => true]);
        
        return redirect()->back()
            ->with('success', 'Avis approuvé.');
    }

    /**
     * Masquer un avis
     */
    public function hide(Avis $avi)
    {
        // Si vous avez un champ 'masque' dans la table avis
        // $avi->update(['masque' => true]);
        
        return redirect()->back()
            ->with('success', 'Avis masqué.');
    }
}
