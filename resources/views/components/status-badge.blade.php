@props(['status'])

<span class="badge bg-{{ $status->color() }}">{{ $status->label() }}</span>
