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
        <h1 class="text-primary">Faculties</h1>
        <p class="text-muted">Display all the faculty within the system.</p>
            @if (count($faculties) == 0)
                <h2 class="text-center text-muted">No record found</h2>
            @endif
            @foreach( $faculties as $faculty)
            <form  method="post" >
                @csrf
                <div class="card mb-4 border-4 border-secondary">
                    <div class="card-body row m-0">
                        <div class="col row p-0 m-3">
                            <div class="col row d-flex align-items-center">
                                <h2 class="col-12 heading-title">{{$faculty->faculty_semester->faculty->name}}</h2>
                                <p class="col-12 m-0">
                                </p>
                            </div>
                            <div class="col-auto row m-0 d-flex align-items-center">
                                <div class="col-12 col-md-5 d-flex align-items-center">
                                    <h3 class="mb-0 m-3">
                                        First deadline:{{\App\Helpers\DateTimeHelper::formatDateTime($faculty->faculty_semester->first_deadline)}}</h3>
                                </div>
                                <div class="col-12 col-md-5 d-flex align-items-center">
                                    <h3 class="mb-0 m-3">
                                        Second deadline:{{\App\Helpers\DateTimeHelper::formatDateTime($faculty->faculty_semester->second_deadline)}} </h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto d-flex align-items-center m-3">
                            <a class="btn btn-success text-white m-3" href="{{route('coordinator.addStudentFaculty', [$faculty->id])}}">
                                Add Student
                            </a>
                        </div>
                    </div>
                </div>
            </form>
            @endforeach
</div>
@endsection
