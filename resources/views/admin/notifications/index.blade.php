@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title m-0">Notifications & Alertes</h5>
    </div>
    <div class="card-body">
        @if(count($notifications) > 0)
            <div class="list-group">
                @foreach($notifications as $notification)
                <a href="{{ $notification['url'] }}" class="list-group-item list-group-item-action">
                    <div class="d-flex w-100 justify-content-between">
                        <div>
                            <h6 class="mb-1">
                                @if($notification['priority'] === 'high')
                                    <span class="badge bg-label-danger me-2">Urgent</span>
                                @elseif($notification['priority'] === 'medium')
                                    <span class="badge bg-label-warning me-2">Important</span>
                                @endif
                                {{ $notification['title'] }}
                            </h6>
                            <p class="mb-1">{{ $notification['message'] }}</p>
                        </div>
                        <small class="text-muted">{{ $notification['date']->format('d/m/Y H:i') }}</small>
                    </div>
                </a>
                @endforeach
            </div>
        @else
            <div class="text-center py-5">
                <i class="bx bx-check-circle text-success" style="font-size: 4rem;"></i>
                <p class="mt-3 text-muted">Aucune notification</p>
            </div>
        @endif
    </div>
</div>
@endsection

