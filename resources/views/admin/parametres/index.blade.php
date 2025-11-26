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
        <form action="{{ route('admin.parametres.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <!-- Logo Upload -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title m-0">Logo de l'Application</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Logo actuel</label>
                            @php
                                $logoPath = DB::table('parametres')->where('cle', 'logo_application')->value('valeur');
                            @endphp
                            @if($logoPath)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $logoPath) }}" alt="Logo" style="max-width: 200px; max-height: 100px;">
                                </div>
                            @else
                                <p class="text-muted">Aucun logo</p>
                            @endif
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Uploader un nouveau logo</label>
                            <input type="file" name="logo" class="form-control" accept="image/*">
                            <small class="text-muted">Formats acceptés: JPG, PNG, GIF (max 2MB)</small>
                        </div>
                    </div>
                </div>
            </div>

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

            <!-- Documents Légaux -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title m-0">Documents Légaux</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Conditions d'utilisation</label>
                        <textarea name="parametres[conditions_utilisation]" id="conditions_utilisation" class="form-control" rows="10">{{ DB::table('parametres')->where('cle', 'conditions_utilisation')->value('valeur') ?? '' }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Politique de confidentialité</label>
                        <textarea name="parametres[politique_confidentialite]" id="politique_confidentialite" class="form-control" rows="10">{{ DB::table('parametres')->where('cle', 'politique_confidentialite')->value('valeur') ?? '' }}</textarea>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">
                    <i class="bx bx-save"></i> Enregistrer les paramètres
                </button>
            </div>
        </form>
    </div>
</div>

@push('vendor-js')
<!-- TinyMCE WYSIWYG Editor -->
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
@endpush

@push('page-js')
<script>
    tinymce.init({
        selector: '#conditions_utilisation, #politique_confidentialite',
        height: 400,
        menubar: false,
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount'
        ],
        toolbar: 'undo redo | blocks | ' +
            'bold italic forecolor | alignleft aligncenter ' +
            'alignright alignjustify | bullist numlist outdent indent | ' +
            'removeformat | help',
        content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
    });
</script>
@endpush
@endsection

