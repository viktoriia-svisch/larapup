@extends("layout.Admin.admin-layout")
@section('title', 'Dashboard')
@push("custom-css")
@endpush
@section('breadcrumb')
    <div class="container">
        {{ Breadcrumbs::render('dashboard', route('admin.dashboard')) }}
    </div>
@endsection
@section("admin-content")
    <div class="container">
        <h1 class="text-center">
            Welcome to Greenwich Magazine
            <br>
            <strong>Admin portal</strong>
        </h1>
    </div>
@endsection
@push("custom-js")
@endpush
