<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Exports\ClientsExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Client::with('user');

        // Recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filtre par statut
        if ($request->filled('status')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('status', $request->status);
            });
        }

        // Filtre par date d'inscription
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $clients = $query->latest()->paginate(15);

        return view('admin.clients.index', compact('clients'));
    }

    /**
     * Export clients to Excel
     */
    public function export()
    {
        return Excel::download(new ClientsExport, 'clients_' . date('Y-m-d_His') . '.xlsx');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.clients.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
            'address' => 'nullable|string',
        ]);

        DB::transaction(function() use ($validated) {
            $user = \App\Models\User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'password' => bcrypt($validated['password']),
                'role' => 'client',
                'status' => 'active',
            ]);

            Client::create([
                'user_id' => $user->id,
                'address' => $validated['address'] ?? null,
            ]);
        });

        return redirect()->route('admin.clients.index')
            ->with('success', 'Client créé avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client)
    {
        $client->load(['user', 'commandes.prestataire.user', 'commandes.categorieService', 'avis.prestataire.user']);
        
        $stats = [
            'commandes_total' => $client->commandes()->count(),
            'commandes_terminees' => $client->commandes()->where('statut', 'terminee')->count(),
            'avis_donnes' => $client->avis()->count(),
            'montant_total' => $client->commandes()->sum('montant_total'),
        ];

        return view('admin.clients.show', compact('client', 'stats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client)
    {
        $client->load('user');
        return view('admin.clients.edit', compact('client'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $client->user_id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8',
            'address' => 'nullable|string',
            'status' => 'required|in:active,suspended,blocked',
        ]);

        DB::transaction(function() use ($validated, $client) {
            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'status' => $validated['status'],
            ];

            if (!empty($validated['password'])) {
                $userData['password'] = bcrypt($validated['password']);
            }

            $client->user->update($userData);

            $client->update([
                'address' => $validated['address'] ?? null,
            ]);
        });

        return redirect()->route('admin.clients.show', $client)
            ->with('success', 'Client mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {
        DB::transaction(function() use ($client) {
            $client->user->delete(); // Soft delete si configuré
            $client->delete();
        });

        return redirect()->route('admin.clients.index')
            ->with('success', 'Client supprimé avec succès.');
    }

    /**
     * Suspendre un client
     */
    public function suspend(Client $client)
    {
        $client->user->update(['status' => 'suspended']);

        return redirect()->back()
            ->with('success', 'Client suspendu avec succès.');
    }

    /**
     * Réactiver un client
     */
    public function activate(Client $client)
    {
        $client->user->update(['status' => 'active']);

        return redirect()->back()
            ->with('success', 'Client réactivé avec succès.');
    }
}
