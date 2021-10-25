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
            <hr class="mb-0">
        </div>
        <div class="col-12">
            <div class="card mb-4">
                <div class="row no-gutters p-2">
                    <div class=" d-flex justify-content-center flex-column">
                        <h3>Start:<span>12/03/2019</span></h3>
                        <h3>End:<span>12/05/2019</span></h3>
                    </div>
                    <div class="col-auto">
                        <div class="card-body">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push("custom-js")
@endpush
