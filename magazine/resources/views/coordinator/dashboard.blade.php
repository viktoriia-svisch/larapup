@extends("layout.Coordinator.coordinator-layout")
@section('title', 'Dashboard')
@push("custom-css")
@endpush
@section('breadcrumb')
    <div class="container">
        {{ Breadcrumbs::render('dashboard', route('coordinator.dashboard')) }}
    </div>
@endsection
@section("coordinator-content")
    <div class="container">
        <h1 class="text-center">
            Welcome to Greenwich Magazine
            <br>
            <strong>Coordinator portal</strong>
        </h1>
    </div>
@endsection
@push("custom-js")
@endpush
