@extends("layout.Student.student-layout")
@section('title', 'Dashboard')
@push("custom-css")
@endpush
@section('breadcrumb')
    <div class="container">
        {{ Breadcrumbs::render('dashboard', route('student.dashboard')) }}
    </div>
@endsection
@section("student-content")
    <div class="container-fluid">
        <div class="container">
            <div class="row p-3">
                <div class="card col-12 col-sm-5 col-md-4">
                    <div class="card-body">
                        <h1 class="card-title">Active Semester</h1>
                        <p class="card-text">
                            @if ($activeSemester !== null)
                                {{\Illuminate\Support\Str::limit($activeSemester->name, 34)}}
                            @else
                                <span class="text-muted">
                                    Currently not in any
                                </span>
                            @endif
                        </p>
                        <span class="text-muted">
                            End date:
                            @if ($activeSemester !== null)
                                {{\App\Helpers\DateTimeHelper::formatDate($activeSemester->end_date)}}
                            @else
                                <span class="text-muted">
                                    N/A
                                </span>
                            @endif
                        </span>
                    </div>
                </div>
                <div class="card col-12 col-sm-7 col-md-8">
                    <div class="card-body">
                        <h1 class="card-title">Current Faculty</h1>
                        <div class="card-text row m-0 p-0">
                            @if ($activeFaculty)
                                <div class="col pl-0">
                                <h2 class="col-12 p-0 mb-0 font-weight-bold">
                                    {{\Illuminate\Support\Str::limit($activeFaculty->name, 24)}}
                                </h2>
                                    <hr class="m-0 mb-2">
                                    <div class="col-12 pl-0">
                                        <span class="text-muted">Next due date: </span>
                                        <span class="text-warning">
                                            @if (
                                            \App\Helpers\DateTimeHelper::isNowPassedDate($activeFaculty->first_deadline)
                                            && !\App\Helpers\DateTimeHelper::isNowPassedDate($activeFaculty->second_deadline)
                                            )
                                                {{\App\Helpers\DateTimeHelper::formatDateTime($activeFaculty->second_deadline)}}
                                            @elseif (
                                            !\App\Helpers\DateTimeHelper::isNowPassedDate($activeFaculty->first_deadline)
                                            && !\App\Helpers\DateTimeHelper::isNowPassedDate($activeFaculty->second_deadline)
                                            )
                                                {{\App\Helpers\DateTimeHelper::formatDateTime($activeFaculty->first_deadline)}}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <a href="{{route('student.faculty.detail', [$activeFaculty->id])}}" class="btn btn-info text-white">
                                        <span>Detail</span>
                                        <span class="badge badge-dark text-white">4</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <hr>
        </div>
        <div class="container">
        </div>
    </div>
@endsection
@push("custom-js")
@endpush
