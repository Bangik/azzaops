@foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'] as $key => $type)
    @if (session($key))
        <div class="alert alert-{{ $type }} alert-dismissible fade show" role="alert">
            <i class="bi bi-{{ $key === 'success' ? 'check-circle' : ($key === 'error' ? 'exclamation-triangle' : ($key === 'warning' ? 'exclamation-circle' : 'info-circle')) }}-fill me-1"></i>
            {{ session($key) }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
@endforeach
