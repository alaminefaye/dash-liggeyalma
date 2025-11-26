<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture #{{ $commande->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            border-bottom: 2px solid #696cff;
            padding-bottom: 20px;
        }
        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #696cff;
        }
        .invoice-info {
            text-align: right;
        }
        .invoice-body {
            margin-bottom: 30px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #696cff;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #696cff;
            color: white;
        }
        .total-row {
            font-weight: bold;
            font-size: 18px;
        }
        .text-right {
            text-align: right;
        }
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-header">
        <div>
            <div class="invoice-title">FACTURE</div>
            <div>Commande #{{ $commande->id }}</div>
        </div>
        <div class="invoice-info">
            <div><strong>Date :</strong> {{ $commande->created_at->format('d/m/Y') }}</div>
            <div><strong>Statut :</strong> {{ ucfirst(str_replace('_', ' ', $commande->statut)) }}</div>
        </div>
    </div>

    <div class="invoice-body">
        <div class="section">
            <div class="section-title">Informations Client</div>
            <div>
                <strong>{{ $commande->client->user->name }}</strong><br>
                {{ $commande->client->user->email }}<br>
                @if($commande->client->user->phone)
                    {{ $commande->client->user->phone }}
                @endif
            </div>
        </div>

        <div class="section">
            <div class="section-title">Informations Prestataire</div>
            <div>
                @if($commande->prestataire)
                    <strong>{{ $commande->prestataire->user->name }}</strong><br>
                    {{ $commande->prestataire->user->email }}<br>
                    @if($commande->prestataire->user->phone)
                        {{ $commande->prestataire->user->phone }}
                    @endif
                @else
                    <em>En attente d'assignation</em>
                @endif
            </div>
        </div>

        <div class="section">
            <div class="section-title">Service</div>
            <div>
                <strong>{{ $commande->categorieService->nom ?? 'N/A' }}</strong><br>
                @if($commande->sousCategorieService)
                    {{ $commande->sousCategorieService->nom }}
                @endif
            </div>
            @if($commande->description)
                <div class="mt-2">
                    <strong>Description :</strong><br>
                    {{ $commande->description }}
                </div>
            @endif
        </div>

        <div class="section">
            <div class="section-title">Adresse d'intervention</div>
            <div>{{ $commande->adresse_intervention }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-right">Montant</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Service : {{ $commande->categorieService->nom ?? 'N/A' }}</td>
                <td class="text-right">{{ number_format($commande->montant_total, 0, ',', ' ') }} FCFA</td>
            </tr>
            @if($commande->montant_commission > 0)
            <tr>
                <td>Commission ({{ number_format(($commande->montant_commission / $commande->montant_total) * 100, 2) }}%)</td>
                <td class="text-right">- {{ number_format($commande->montant_commission, 0, ',', ' ') }} FCFA</td>
            </tr>
            @endif
            <tr class="total-row">
                <td><strong>TOTAL</strong></td>
                <td class="text-right"><strong>{{ number_format($commande->montant_total, 0, ',', ' ') }} FCFA</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="section mt-4">
        <div class="section-title">Informations de paiement</div>
        <div>
            <strong>Méthode :</strong> {{ ucfirst(str_replace('_', ' ', $commande->methode_paiement ?? 'N/A')) }}<br>
            <strong>Statut :</strong> {{ ucfirst($commande->statut_paiement ?? 'N/A') }}
        </div>
    </div>

    <div class="no-print" style="margin-top: 30px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #696cff; color: white; border: none; border-radius: 5px; cursor: pointer;">
            Imprimer la facture
        </button>
    </div>
</body>
</html>

