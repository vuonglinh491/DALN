@props(['label' => '', 'tone' => 'secondary'])
@php
$icon = match($tone) {
    'success' => 'bi-check2-circle',
    'pending' => 'bi-hourglass-split',
    'danger' => 'bi-exclamation-circle',
    'primary' => 'bi-info-circle',
    'info' => 'bi-stars',
    default => 'bi-dot',
};
@endphp
<span class="badge badge-soft badge-{{ $tone }}">
    <i class="bi {{ $icon }}"></i> {{ $label }}
</span>
