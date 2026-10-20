@extends('layouts.app')

@section('content')
<div class="row g-4">
    <aside class="col-lg-3" aria-label="Menu">
        @include(($panel ?? 'user') === 'admin' ? 'partials.sidebar-admin' : 'partials.sidebar-user')
    </aside>
    <div class="col-lg-9">
        @yield('panel')
    </div>
</div>
@endsection
