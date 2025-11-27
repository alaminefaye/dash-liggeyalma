<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Retrait;
use App\Models\Prestataire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RetraitController extends Controller
{
    /**
     * Get prestataire's retraits
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        if ($user->role !== 'prestataire') {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé',
            ], 403);
        }

        $prestataire = Prestataire::where('user_id', $user->id)->first();
        if (!$prestataire) {
            return response()->json([
                'success' => false,
                'message' => 'Prestataire non trouvé',
            ], 404);
        }

        $query = Retrait::where('prestataire_id', $prestataire->id);

        // Filter by status
        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }

        $perPage = $request->get('per_page', 15);
        $retraits = $query->orderBy('created_at', 'desc')->paginate($perPage);

        $data = $retraits->map(function ($retrait) {
            return [
                'id' => $retrait->id,
                'montant' => $retrait->montant,
                'frais_retrait' => $retrait->frais_retrait,
                'montant_net' => $retrait->montant_net,
                'methode' => $retrait->methode,
                'numero_compte' => $retrait->numero_compte,
                'statut' => $retrait->statut,
                'motif_refus' => $retrait->motif_refus,
                'date_validation' => $retrait->date_validation?->format('Y-m-d H:i:s'),
                'created_at' => $retrait->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $retrait->updated_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $retraits->currentPage(),
                'last_page' => $retraits->lastPage(),
                'per_page' => $retraits->perPage(),
                'total' => $retraits->total(),
            ],
        ]);
    }

    /**
     * Create a new retrait request
     */
    public function store(Request $request)
    {
        $user = $request->user();
        
        if ($user->role !== 'prestataire') {
            return response()->json([
                'success' => false,
                'message' => 'Seuls les prestataires peuvent demander un retrait',
            ], 403);
        }

        $prestataire = Prestataire::where('user_id', $user->id)->first();
        if (!$prestataire) {
            return response()->json([
                'success' => false,
                'message' => 'Prestataire non trouvé',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'montant' => 'required|numeric|min:5000',
            'methode' => 'required|in:mobile_money,virement,especes',
            'numero_compte' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Vérifier le solde disponible
        if ($prestataire->solde < $request->montant) {
            return response()->json([
                'success' => false,
                'message' => 'Solde insuffisant. Solde disponible: ' . number_format($prestataire->solde, 0, ',', ' ') . ' FCFA',
            ], 400);
        }

        // Vérifier s'il y a un retrait en attente
        $retraitEnAttente = Retrait::where('prestataire_id', $prestataire->id)
            ->where('statut', 'en_attente')
            ->first();

        if ($retraitEnAttente) {
            return response()->json([
                'success' => false,
                'message' => 'Vous avez déjà un retrait en attente de validation',
            ], 400);
        }

        // Récupérer les frais de retrait depuis les paramètres
        $fraisRetrait = \App\Models\Parametre::where('cle', 'frais_retrait')->first();
        $frais = $fraisRetrait ? (float) $fraisRetrait->valeur : 500;
        
        $montantNet = $request->montant - $frais;

        // Créer le retrait
        $retrait = Retrait::create([
            'prestataire_id' => $prestataire->id,
            'montant' => $request->montant,
            'frais_retrait' => $frais,
            'montant_net' => $montantNet,
            'methode' => $request->methode,
            'numero_compte' => $request->numero_compte,
            'statut' => 'en_attente',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Demande de retrait créée avec succès',
            'data' => [
                'id' => $retrait->id,
                'montant' => $retrait->montant,
                'frais_retrait' => $retrait->frais_retrait,
                'montant_net' => $retrait->montant_net,
                'methode' => $retrait->methode,
                'statut' => $retrait->statut,
                'created_at' => $retrait->created_at->format('Y-m-d H:i:s'),
            ],
        ], 201);
    }

    /**
     * Get retrait details
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        
        if ($user->role !== 'prestataire') {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé',
            ], 403);
        }

        $prestataire = Prestataire::where('user_id', $user->id)->first();
        if (!$prestataire) {
            return response()->json([
                'success' => false,
                'message' => 'Prestataire non trouvé',
            ], 404);
        }

        $retrait = Retrait::where('id', $id)
            ->where('prestataire_id', $prestataire->id)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $retrait->id,
                'montant' => $retrait->montant,
                'frais_retrait' => $retrait->frais_retrait,
                'montant_net' => $retrait->montant_net,
                'methode' => $retrait->methode,
                'numero_compte' => $retrait->numero_compte,
                'statut' => $retrait->statut,
                'motif_refus' => $retrait->motif_refus,
                'date_validation' => $retrait->date_validation?->format('Y-m-d H:i:s'),
                'created_at' => $retrait->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $retrait->updated_at->format('Y-m-d H:i:s'),
            ],
        ]);
    }
}

