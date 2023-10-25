@extends("layout.Admin.admin-layout")
@section('title', 'Static Information')
@push("custom-css")
@endpush
@section("admin-content")
    <div class="container">
        @if ($count = count($info) == 0)
            <h1 class="text-center text-muted">No record found</h1>
        @else
            <h1>Overall Statistic of {{$currentSemester->name}}</h1>
            <div class="col-12 mb-4">
                <small class="text-muted">
                    Semester's Note: {{$currentSemester->description}}
                </small>
            </div>
            <div class="table-responsive">
                <table class="table align-items-center">
                    <thead class="thead-light">
                    <tr>
                        <th scope="col">Submissions Post</th>
                        <th scope="col">Students Submissions</th>
                        <th scope="col">Grade AVG</th>
                        <th scope="col">Overdue Submissions</th>
                        <th scope="col">Timely Submissions</th>
                        <th scope="col">Highest Grade</th>
                        <th scope="col">Lowest Grade</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>
                            <span class="mb-0 text-sm">{{$info->count($info)}}</span>
                        </td>
                        <td>
                            <span class="mb-0 text-sm">{{$countstudent}}</span>
                        </td>
                        <td>
                            <span class="mb-0 text-sm">{{$grade_avg}} </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="mr-2">{{$outOfDate}}</span>
                                <div>
                                    <div class="progress">
                                        <div class="progress-bar bg-danger" role="progressbar"
                                             aria-valuenow="{{$outOfDate}}"
                                             aria-valuemin="0" aria-valuemax="{{$countstudent}}"
                                             style="width:{{$outOfDate/$countstudent*100}}%"></div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="mr-2">{{$inTime}}</span>
                                <div>
                                    <div class="progress">
                                        <div class="progress-bar bg-success" role="progressbar"
                                             aria-valuenow="{{$inTime}}"
                                             aria-valuemin="0" aria-valuemax="{{$countstudent}}"
                                             style="width:{{$inTime/$countstudent*100}}%"></div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="mb-0 text-sm">{{$maxgrade}}</span>
                        </td>
                        <td class="text-center">
                            <span class="mb-0 text-sm">{{$mingrade}}</span>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <a href="{{route("admin.infoSemester.downloadBackup", [$currentSemester->id])}}"
               class="btn btn-default text-white btn-block">
                Download All data of this semester
            </a>
        @endif
        <hr>
        <h1>Submission of {{$currentSemester->name}}</h1>
        <div class="table-responsive">
            <table class="table align-items-center">
                <thead class="thead-light">
                <tr>
                    <th scope="col">Faculty Name</th>
                    <th scope="col">Student First Name</th>
                    <th scope="col">Student Last Name</th>
                    <th scope="col">Submit Date</th>
                    <th scope="col">Status</th>
                    <th scope="col">Grades</th>
                </tr>
                </thead>
                @foreach($info as $semester)
                    <tr>
                        <td>
                            <span class="mb-0 text-sm"> {{$semester->faculties_name}}</span>
                        </td>
                        <td>
                            <span class="mb-0 text-sm"> {{$semester->students_fname}}</span>
                        </td>
                        <td>
                            <span class="mb-0 text-sm"> {{$semester->students_lname}}</span>
                        </td>
                        <td>
                            <span class="mb-0 text-sm"> {{$semester->created_at}}</span>
                        </td>
                        <td class="text-center">
                            @if ($semester->status == 0)
                                <span class="badge badge-dot">
                            <i class="bg-danger"></i> pending</span>
                            @else
                                <span class="badge badge-dot">
                            <i class="bg-success"></i> completed</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="mb-0 text-sm"> {{$semester->grade}}</span>
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
        <hr>
        <div class="col-12 d-flex justify-content-center">
            {{ $info->links() }}
        </div>
    </div>
@endsection
@push("custom-js")
@endpush
