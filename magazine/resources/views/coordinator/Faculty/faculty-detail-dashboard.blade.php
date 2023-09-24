@extends("coordinator.faculty.faculty-detail")
@section('title', 'Faculty '.$facultySemester->faculty->name.' - Dashboard')
@push("custom-css")
    <style>
        .time-section {
            position: absolute;
            top: calc(25px + 1rem);
            left: 0;
            -webkit-transform: translate(-50%, -50%);
            -moz-transform: translate(-50%, -50%);
            -ms-transform: translate(-50%, -50%);
            -o-transform: translate(-50%, -50%);
            transform: translate(-50%, -50%);
        }
        .message {
            display: none;
            position: absolute;
            left: 0;
            top: 50%;
            transform: translate(calc(-100% - 5px), -50%);
        }
        .time-section:hover .message {
            display: inline-block;
        }
    </style>
@endpush
@section('faculty-detail')
    <div class="col-12">
        <h2 class="heading-title">Submission</h2>
        <div class="card">
            <div class="card-body">
                <div class="col-12 row m-0 p-0">
                    <div class="col-12 col-sm-6 text-center">
                        Deadline for upload:
                        @if (\App\Helpers\DateTimeHelper::isNowPassedDate($facultySemester->first_deadline))
                            <span class="text-muted">
                                {{\App\Helpers\DateTimeHelper::formatDateTime($facultySemester->first_deadline)}}
                            </span>
                        @else
                            <span class="text-danger">
                                {{\App\Helpers\DateTimeHelper::formatDateTime($facultySemester->first_deadline)}}
                            </span>
                        @endif
                    </div>
                    <div class="col-12 col-sm-6 text-center">
                        Deadline for final:
                        @if (\App\Helpers\DateTimeHelper::isNowPassedDate($facultySemester->second_deadline))
                            <span class="text-muted">
                                {{\App\Helpers\DateTimeHelper::formatDateTime($facultySemester->second_deadline)}}
                            </span>
                        @else
                            <span class="text-danger">
                                {{\App\Helpers\DateTimeHelper::formatDateTime($facultySemester->second_deadline)}}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <br>
        <hr>
        <div class="container-fluid">
            @include('layout.response.errors')
            <div class="table-responsive">
                <table class="table align-items-center">
                    <thead class="thead-light">
                    <tr>
                        <th scope="col" class="text-center">Total Student</th>
                        <th scope="col" class="text-center">Total Submission</th>
                        <th scope="col" class="text-center">Submission<br>Late</th>
                        <th scope="col" class="text-center">Submission<br>On-Time</th>
                        <th scope="col" class="text-center">Submission<br>Average Grade</th>
                        <th scope="col" class="text-center">Submission<br>Highest Grade</th>
                        <th scope="col" class="text-center">Submission<br>Lowest Grade</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td class="text-center">
                            <span class="mb-0 text-sm">{{$student_total}}</span>
                        </td>
                        <td class="text-center">
                            <span class="mb-0 text-sm">{{$submissions_total}}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="mr-2">{{$submissions_late}}</span>
                                @if($submissions_total != 0)
                                    <div class="progress">
                                        <div class="progress-bar bg-danger" role="progressbar"
                                             aria-valuenow="{{$submissions_late}}"
                                             aria-valuemin="0" aria-valuemax="{{$submissions_total}}"
                                             style="width:{{$submissions_late/$submissions_total*100}}%"></div>
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="mr-2">{{$submissions_onTime}}</span>
                                @if($submissions_total != 0)
                                    <div class="progress">
                                        <div class="progress-bar bg-success" role="progressbar"
                                             aria-valuenow="{{$submissions_onTime}}"
                                             aria-valuemin="0" aria-valuemax="{{$submissions_total}}"
                                             style="width:{{$submissions_onTime/$submissions_total*100}}%">
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="mb-0 text-sm">{{$grade_average ?? 'N/D'}}</span>
                        </td>
                        <td class="text-center">
                            <span class="mb-0 text-sm">{{$grade_highest ?? 'N/D'}}</span>
                        </td>
                        <td class="text-center">
                            <span class="mb-0 text-sm">{{$grade_lowest ?? 'N/D'}}</span>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <a href="{{route("coordinator.faculty.backupsDownload", [
            $facultySemester->faculty_id, $facultySemester->semester_id
            ])}}"
               class="btn btn-default text-white btn-block">
                Download All submission data
            </a>
        </div>
    </div>
@endsection
@push("custom-js")
@endpush
