@extends('layouts.app')

@section('title', 'Paramètres')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0">Paramètres de l'Application</h5>
        <form action="{{ route('admin.parametres.initialize') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-outline-primary" onclick="return confirm('Initialiser les paramètres par défaut ?')">
                <i class="bx bx-refresh"></i> Initialiser les paramètres
            </button>
        </form>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.parametres.update') }}" method="POST">
            @csrf
            
            @foreach($parametres as $groupe => $params)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title m-0">{{ ucfirst($groupe) }}</h5>
                </div>
                <div class="card-body">
                    @foreach($params as $param)
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">
                                {{ $param->description ?? ucfirst(str_replace('_', ' ', $param->cle)) }}
                            </label>
                        </div>
                        <div class="col-md-8">
                            @if($param->type === 'boolean')
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" 
                                           name="parametres[{{ $param->cle }}]" 
                                           value="1" 
                                           id="param_{{ $param->cle }}"
                                           {{ $param->valeur == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="param_{{ $param->cle }}">
                                        {{ $param->valeur == '1' ? 'Activé' : 'Désactivé' }}
                                    </label>
                                </div>
                            @elseif($param->type === 'integer')
                                <input type="number" 
                                       name="parametres[{{ $param->cle }}]" 
                                       class="form-control" 
                                       value="{{ $param->valeur }}">
                            @else
                                <input type="text" 
                                       name="parametres[{{ $param->cle }}]" 
                                       class="form-control" 
                                       value="{{ $param->valeur }}">
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">
                    <i class="bx bx-save"></i> Enregistrer les paramètres
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

