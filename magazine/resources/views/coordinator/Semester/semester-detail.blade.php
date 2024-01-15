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
        {{ Breadcrumbs::render(
        'dashboard.semester.info',
        route('coordinator.dashboard'),
        route('coordinator.manageSemester'),
        $semester,
        route("coordinator.semester.detail", [$semester->id])
        ) }}
    </div>
@endsection
@section('coordinator-content')
    <div class="container">
        <br>
        <h1 class="text-primary">Faculties you in-charge in semester {{$semester->name}}</h1>
        <p class="text-muted">Display all the faculty within the system.</p>
        <hr>
        @if (count($listFacultySemester) == 0)
            <h2 class="text-center text-muted">No record found</h2>
        @endif
        <div class="col-12">
            @include('layout.response.errors')
        </div>
        @if (\Illuminate\Support\Facades\Auth::guard(COORDINATOR_GUARD)->user()->type == COORDINATOR_LEVEL['MASTER']
        && !\App\Helpers\DateTimeHelper::isNowPassedDate($semester->start_date))
            <div class="col-12">
                <a href="{{route("coordinator.semester.detail.add", [$semester->id])}}" class="btn btn-block btn-icon btn-success">
                    <i class="fas fa-plus"></i>
                    Add faculty
                </a>
            </div>
            <br>
        @endif
        @foreach( $listFacultySemester as $facultySemester)
            <div class="card mb-4 border-4 border-secondary">
                <div class="card-body row m-0">
                    <div class="col row p-0 m-2">
                        <div class="col row d-flex align-items-center">
                            <h2 class="col-12 heading-title">{{$facultySemester->faculty->name}}</h2>
                            <p class="col-12 m-0">
                                {{$facultySemester->description}}
                            </p>
                        </div>
                        @if (\Illuminate\Support\Facades\Auth::guard(COORDINATOR_GUARD)->user()->type == COORDINATOR_LEVEL['MASTER'])
                            <div class="col-12">
                                <p class="text-muted p-0">
                                    Coordinator:
                                    @foreach($facultySemester->faculty_semester_coordinator as $coor)
                                        <span class="text-info mr-4">
                                            {{$coor->coordinator->first_name . ' '. $coor->coordinator->last_name}}
                                        </span>
                                    @endforeach
                                </p>
                            </div>
                        @endif
                        <div class="col-12 row m-0">
                            <div class="col-12 col-md-6 d-flex align-items-center">
                                <h3 class="mb-0 m-3">
                                    First deadline:
                                    {{\App\Helpers\DateTimeHelper::formatDateTime($facultySemester->first_deadline)}}
                                </h3>
                            </div>
                            <div class="col-12 col-md-6 d-flex align-items-center">
                                <h3 class="mb-0 m-3">
                                    Second deadline:
                                    {{\App\Helpers\DateTimeHelper::formatDateTime($facultySemester->second_deadline)}}
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto d-flex flex-column justify-content-around">
                        <a href="{{route('coordinator.faculty.dashboard', [$facultySemester->faculty_id, $facultySemester->semester_id])}}"
                           class="btn btn-primary btn-icon m-0">
                            <i class="fas fa-info-circle"></i>
                            <span>Detail</span>
                        </a>
                        @if (\Illuminate\Support\Facades\Auth::guard(COORDINATOR_GUARD)->user()->type == COORDINATOR_LEVEL['MASTER']
                        && !\App\Helpers\DateTimeHelper::isNowPassedDate($semester->start_date))
                            <button class="btn btn-danger btn-icon" data-toggle="modal"
                                    data-target="#deleteConfirmation"
                                    onclick="assignSemester({{$facultySemester->faculty->id}})">
                                <i class="fas fa-trash"></i>
                                <span>Remove</span>
                            </button>
                        @endif
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
@section('modal')
    <div class="modal fade" id="deleteConfirmation" tabindex="-1" role="dialog"
         aria-labelledby="deleteConfirmationLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <form class="modal-content" href="{{route("coordinator.semester.detail.remove", [$semester->id])}}"
                  method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteConfirmationLabel">Confirm remove</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @csrf
                    You are going to remove this faculty and all its related information.
                    This action <strong>cannot be undo</strong>.
                    <input type="hidden" name="faculty_id" id="faculty_id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('custom-js')
    <script>
        function assignSemester(facultyID) {
            let domInput = $("#faculty_id");
            domInput.val(facultyID);
        }
    </script>
@endpush
