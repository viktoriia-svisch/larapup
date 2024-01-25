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
    123 admin
@endsection
@push("custom-js")
@endpush
