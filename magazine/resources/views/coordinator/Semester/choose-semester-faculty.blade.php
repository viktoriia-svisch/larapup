@extends("layout.Coordinator.coordinator-layout")
@section('title', 'Manage Semester')
@push("custom-css")
    <style>
        .border-4 {
            border: 2px solid transparent;
        }
    </style>
@endpush
@section('coordinator-content')
    <div class="container">
        <br>
        <h1 class="text-primary">Faculties you in-charge in semester {{$semester->name}}</h1>
        <p class="text-muted">Display all the faculty within the system.</p>
        <hr>
        @if (count($listFacultySemester) == 0)
            <h2 class="text-center text-muted">No record found</h2>
        @endif
        @foreach( $listFacultySemester as $facultySemester)
            <div class="card mb-4 border-4 border-secondary">
                <div class="card-body row m-0">
                    <div class="col row p-0 m-3">
                        <div class="col row d-flex align-items-center">
                            <h2 class="col-12 heading-title">{{$facultySemester->faculty->name}}</h2>
                            <p class="col-12 m-0">
                                {{$facultySemester->description}}
                            </p>
                        </div>
                        <div class="col-auto row m-0 d-flex align-items-center">
                            <div class="col-12 col-md-5 d-flex align-items-center">
                                <h3 class="mb-0 m-3">
                                    First deadline:
                                    {{\App\Helpers\DateTimeHelper::formatDateTime($facultySemester->first_deadline)}}
                                </h3>
                            </div>
                            <div class="col-12 col-md-5 d-flex align-items-center">
                                <h3 class="mb-0 m-3">
                                    Second deadline:
                                    {{\App\Helpers\DateTimeHelper::formatDateTime($facultySemester->second_deadline)}}
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto d-flex align-items-center m-3">
                        <a class="btn btn-success text-white m-3"
                           href="{{route('coordinator.addStudentFaculty', [$facultySemester->id])}}">
                            Add Student
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
        <hr>
        <div class="col-12 d-flex justify-content-center">
            {{$listFacultySemester->links()}}
        </div>
    </div>
@endsection
