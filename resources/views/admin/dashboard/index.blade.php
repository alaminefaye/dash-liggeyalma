@extends('layouts.app')

@section('title', 'Dashboard Admin')

@push('vendor-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}" />
@endpush

@section('content')
<div class="row">
    <!-- Statistiques Clients -->
    <div class="col-lg-3 col-md-6 col-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar flex-shrink-0">
                        <img src="{{ asset('assets/img/icons/unicons/chart-success.png') }}" alt="chart success" class="rounded" />
                    </div>
                </div>
                <span class="fw-semibold d-block mb-1">Clients Total</span>
                <h3 class="card-title mb-2">{{ number_format($stats['clients_total']) }}</h3>
                <small class="text-success fw-semibold">
                    <i class="bx bx-up-arrow-alt"></i> {{ $stats['clients_actifs'] }} actifs
                </small>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 col-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar flex-shrink-0">
                        <img src="{{ asset('assets/img/icons/unicons/wallet-info.png') }}" alt="Credit Card" class="rounded" />
                    </div>
                </div>
                <span>Prestataires Total</span>
                <h3 class="card-title text-nowrap mb-1">{{ number_format($stats['prestataires_total']) }}</h3>
                <small class="text-success fw-semibold">
                    <i class="bx bx-up-arrow-alt"></i> {{ $stats['prestataires_valides'] }} validés
                </small>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 col-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar flex-shrink-0">
                        <img src="{{ asset('assets/img/icons/unicons/cc-primary.png') }}" alt="Credit Card" class="rounded" />
                    </div>
                </div>
                <span class="fw-semibold d-block mb-1">Commandes Total</span>
                <h3 class="card-title mb-2">{{ number_format($stats['commandes_total']) }}</h3>
                <small class="text-info fw-semibold">
                    <i class="bx bx-info-circle"></i> {{ $stats['commandes_en_cours'] }} en cours
                </small>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 col-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar flex-shrink-0">
                        <img src="{{ asset('assets/img/icons/unicons/paypal.png') }}" alt="Credit Card" class="rounded" />
                    </div>
                </div>
                <span class="d-block mb-1">Revenus Total</span>
                <h3 class="card-title text-nowrap mb-2">{{ number_format($stats['revenus_total'], 0, ',', ' ') }} FCFA</h3>
                <small class="text-success fw-semibold">
                    <i class="bx bx-up-arrow-alt"></i> Commission: {{ number_format($stats['commission_totale'], 0, ',', ' ') }} FCFA
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Graphiques -->
<div class="row">
    <!-- Évolution des utilisateurs -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title m-0">Évolution des Utilisateurs (7 derniers jours)</h5>
            </div>
            <div class="card-body">
                <div id="chartUsers"></div>
            </div>
        </div>
    </div>

    <!-- Évolution des commandes -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title m-0">Évolution des Commandes (7 derniers jours)</h5>
            </div>
            <div class="card-body">
                <div id="chartCommandes"></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Évolution des revenus -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title m-0">Évolution des Revenus (7 derniers jours)</h5>
            </div>
            <div class="card-body">
                <div id="chartRevenus"></div>
            </div>
        </div>
    </div>

    <!-- Répartition par catégorie -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title m-0">Répartition par Catégorie de Service</h5>
            </div>
            <div class="card-body">
                <div id="chartCategories"></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Prestataires en attente -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title m-0">Prestataires en attente de validation</h5>
                <a href="{{ route('admin.prestataires.pending') }}" class="btn btn-sm btn-outline-primary">Voir tout</a>
            </div>
            <div class="card-body">
                @if($prestataires_en_attente->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Métier</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($prestataires_en_attente as $prestataire)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-2">
                                                <img src="{{ $prestataire->user->photo ?? asset('assets/img/avatars/1.png') }}" alt class="w-px-40 h-auto rounded-circle" />
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $prestataire->user->name }}</h6>
                                                <small class="text-muted">{{ $prestataire->user->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $prestataire->metier }}</td>
                                    <td>{{ $prestataire->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <a href="{{ route('admin.prestataires.show', $prestataire) }}" class="btn btn-sm btn-primary">Voir</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center mb-0">Aucun prestataire en attente</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Prestataires avec solde négatif -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title m-0">⚠️ Prestataires avec solde négatif</h5>
            </div>
            <div class="card-body">
                @if($prestataires_solde_negatif->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Solde</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($prestataires_solde_negatif as $prestataire)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-2">
                                                <img src="{{ $prestataire->user->photo ?? asset('assets/img/avatars/1.png') }}" alt class="w-px-40 h-auto rounded-circle" />
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $prestataire->user->name }}</h6>
                                                <small class="text-muted">{{ $prestataire->metier }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-danger">{{ number_format($prestataire->solde, 0, ',', ' ') }} FCFA</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.prestataires.show', $prestataire) }}" class="btn btn-sm btn-warning">Gérer</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center mb-0">Aucun solde négatif</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Commandes récentes -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title m-0">Commandes récentes</h5>
                <a href="{{ route('admin.commandes.index') }}" class="btn btn-sm btn-outline-primary">Voir tout</a>
            </div>
            <div class="card-body">
                @if($commandes_recentes->count() > 0)
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Client</th>
                                    <th>Prestataire</th>
                                    <th>Service</th>
                                    <th>Statut</th>
                                    <th>Montant</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($commandes_recentes as $commande)
                                <tr>
                                    <td>#{{ $commande->id }}</td>
                                    <td>{{ $commande->client->user->name ?? 'N/A' }}</td>
                                    <td>{{ $commande->prestataire->user->name ?? 'En attente' }}</td>
                                    <td>{{ $commande->categorieService->nom ?? 'N/A' }}</td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'en_attente' => 'warning',
                                                'acceptee' => 'info',
                                                'en_route' => 'primary',
                                                'arrivee' => 'primary',
                                                'en_cours' => 'info',
                                                'terminee' => 'success',
                                                'annulee' => 'danger',
                                            ];
                                            $color = $statusColors[$commande->statut] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-label-{{ $color }}">{{ ucfirst(str_replace('_', ' ', $commande->statut)) }}</span>
                                    </td>
                                    <td>{{ number_format($commande->montant_total, 0, ',', ' ') }} FCFA</td>
                                    <td>{{ $commande->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <a href="{{ route('admin.commandes.show', $commande) }}" class="btn btn-sm btn-primary">Voir</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center mb-0">Aucune commande récente</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('vendor-js')
<script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
@endpush

@push('page-js')
<script>
    // Graphique Évolution Utilisateurs
    var chartUsersOptions = {
        series: [{
            name: 'Clients',
            data: [{{ implode(',', array_column($evolution_users, 'clients')) }}]
        }, {
            name: 'Prestataires',
            data: [{{ implode(',', array_column($evolution_users, 'prestataires')) }}]
        }],
        chart: {
            type: 'line',
            height: 300
        },
        xaxis: {
            categories: [@foreach($evolution_users as $item)'{{ $item['label'] }}',@endforeach]
        },
        colors: ['#71dd37', '#696cff'],
        stroke: {
            curve: 'smooth'
        }
    };
    var chartUsers = new ApexCharts(document.querySelector("#chartUsers"), chartUsersOptions);
    chartUsers.render();

    // Graphique Évolution Commandes
    var chartCommandesOptions = {
        series: [{
            name: 'Commandes',
            data: [{{ implode(',', array_column($evolution_commandes, 'total')) }}]
        }],
        chart: {
            type: 'area',
            height: 300
        },
        xaxis: {
            categories: [@foreach($evolution_commandes as $item)'{{ $item['label'] }}',@endforeach]
        },
        colors: ['#696cff'],
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.7,
                opacityTo: 0.9,
            }
        }
    };
    var chartCommandes = new ApexCharts(document.querySelector("#chartCommandes"), chartCommandesOptions);
    chartCommandes.render();

    // Graphique Évolution Revenus
    var chartRevenusOptions = {
        series: [{
            name: 'Revenus (FCFA)',
            data: [{{ implode(',', array_column($evolution_revenus, 'montant')) }}]
        }],
        chart: {
            type: 'bar',
            height: 300
        },
        xaxis: {
            categories: [@foreach($evolution_revenus as $item)'{{ $item['label'] }}',@endforeach]
        },
        colors: ['#71dd37'],
        plotOptions: {
            bar: {
                borderRadius: 4,
                horizontal: false,
            }
        }
    };
    var chartRevenus = new ApexCharts(document.querySelector("#chartRevenus"), chartRevenusOptions);
    chartRevenus.render();

    // Graphique Répartition Catégories
    var chartCategoriesOptions = {
        series: [{{ implode(',', $commandes_par_categorie->pluck('total')->toArray()) }}],
        chart: {
            type: 'donut',
            height: 300
        },
        labels: [@foreach($commandes_par_categorie as $cat)'{{ $cat->nom }}',@endforeach],
        colors: ['#696cff', '#71dd37', '#ffab00', '#ff3e1d', '#03c3ec'],
    };
    var chartCategories = new ApexCharts(document.querySelector("#chartCategories"), chartCategoriesOptions);
    chartCategories.render();
</script>
@endpush

