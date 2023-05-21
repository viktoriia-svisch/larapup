@extends("layout.Coordinator.coordinator-layout")
@section('title', 'Manage Semester')
@push("custom-css")
    <style>
        .border-4 {
            border: 2px solid transparent;
        }
    </style>
@endpush
@section('breadcrumb')
    <div class="container">
    </div>
@endsection
@section('coordinator-content')
    <div class="container">
        <h1>Current Semester</h1>
        @if($facSemesterCoordinator)
        <div class="card mb-4 border-4 border-secondary">
            <div class="card-body row m-0">
                <div class="col row p-0 m-3">
                    <div class="col row d-flex align-items-center">
                        <h2 class="col-12 heading-title">{{$facSemesterCoordinator->faculty_semester->semester->name}}</h2>
                        <p class="col-12 m-0">
                        </p>
                    </div>
                    <div class="col-auto row m-0 d-flex align-items-center">
                        <div class="col-12 col-md-5 d-flex align-items-center">
                            <h3 class="mb-0 m-3">
                                Start:{{\App\Helpers\DateTimeHelper::formatDate($facSemesterCoordinator->faculty_semester->semester->start_date)}}</h3>
                        </div>
                        <div class="col-12 col-md-5 d-flex align-items-center">
                            <h3 class="mb-0 m-3">
                                End:{{\App\Helpers\DateTimeHelper::formatDate($facSemesterCoordinator->faculty_semester->semester->end_date)}} </h3>
                        </div>
                    </div>
                </div>
                <div class="col-auto d-flex align-items-center">
                    <a class="btn btn-icon btn-default" type="button"  href="{{route('coordinator.chooseSemesterFaculty', [$facSemesterCoordinator->id])}}">
                        <i class="fas fa-cog top-0"></i>
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>
@endsection
@push("custom-js")
    <script>
        function resetSearch() {
        }
    </script>
@endpush
