@extends("layout.Admin.admin-layout")
@section('title', 'Manage Semester')
@push("custom-css")
@endpush
@section('admin-content')
    <div class="container">
        <div class="col-12 p-5">
            <a href="#" class="btn btn-default float-right">
                <i class="fas fa-cog"></i>
                Setup Semester
            </a>
            <a href="{{route('admin.createSemester')}}" class="btn btn-success float-right mr-3">
                <i class="fas fa-plus"></i>
                New Semester
            </a>
            <br>
            <hr>
        </div>
        <div class="col-12">
        </div>
    </div>
@endsection
@push("custom-js")
@endpush
