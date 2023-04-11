@extends("layout.Admin.admin-layout")
@section('title', 'Static Information')
@push("custom-css")
@endpush
@section("admin-content")
    @if ($count = count($info) == 0)
        <h2 class="text-center text-muted">No record found</h2>
    @else
        <h2 class="">Số Bài nộp:{{$info->count($info)}}</h2>
        <h2 class="">Số H/S nộp:{{$countstudent}}</h2>
        <h2 class="">Trung bình điểm của kì này: {{$grade_avg}}</h2>
        <h2 class="">Số bài đúng hạn: {{$inTime}}</h2>
        <h2 class="">Số bài nộp muộn: {{$outOfDate}}</h2>
        <h2 class="">Điểm cao nhất: {{$maxgrade}}</h2>
        <h2 class="">Điểm thấp nhất: {{$mingrade}}</h2>
    @endif
    <div class="container">
        @foreach($info as $semester)
            <a class="card mb-3">
                <div class="card-body">
                    <h2 class="col-12">Faculty: {{$semester->faculties_name}}</h2>
                    <p class="font-weight-bold">
                        Name:
                        <span class="font-weight-normal">{{$semester->students_name}}</span>
                        Grade:
                        <span class="font-weight-normal">{{$semester->grade}}</span>
                    </p>
                </div>
            </a>
        @endforeach
    </div>
    <div class="table-responsive">
        <table class="table align-items-center">
            <thead class="thead-light">
            <tr>
                <th scope="col">Faculty</th>
                <th scope="col">Student Name</th>
                <th scope="col">Submit Date</th>
                <th scope="col"></th>
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
                                <div class="progress-bar bg-danger" role="progressbar" aria-valuenow="{{$outOfDate}}"
                                     aria-valuemin="0" aria-valuemax="{{$countstudent}}" style="width:@php echo $outOfDate/$countstudent*100 @endphp%"></div>
                            </div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <span class="mr-2">{{$inTime}}</span>
                        <div>
                            <div class="progress">
                                <div class="progress-bar bg-success" role="progressbar" aria-valuenow="{{$inTime}}"
                                     aria-valuemin="0" aria-valuemax="{{$countstudent}}" style="width:@php echo $inTime/$countstudent*100 @endphp%"></div>
                            </div>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="mb-0 text-sm">{{$maxgrade}}</span>
                </td>
                <td>
                    <span class="mb-0 text-sm">{{$mingrade}}</span>
                </td>
            </tr>
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
            <tr>
                <th scope="row">
                    <div class="media align-items-center">
                        <a href="#" class="avatar rounded-circle mr-3">
                            <img alt="Image placeholder" src="../../assets/img/theme/angular.jpg">
                        </a>
                        <div class="media-body">
                            <span class="mb-0 text-sm">Angular Now UI Kit PRO</span>
                        </div>
                    </div>
                </th>
                <td>
                    $1,800 USD
                </td>
                <td>
                    <span class="badge badge-dot">
                        <i class="bg-success"></i> completed
                    </span>
                </td>
                <td>
                    <div class="avatar-group">
                        <a href="#" class="avatar avatar-sm" data-toggle="tooltip" data-original-title="Ryan Tompson">
                            <img alt="Image placeholder" src="../../assets/img/theme/team-1-800x800.jpg"
                                 class="rounded-circle">
                        </a>
                        <a href="#" class="avatar avatar-sm" data-toggle="tooltip" data-original-title="Romina Hadid">
                            <img alt="Image placeholder" src="../../assets/img/theme/team-2-800x800.jpg"
                                 class="rounded-circle">
                        </a>
                        <a href="#" class="avatar avatar-sm" data-toggle="tooltip"
                           data-original-title="Alexander Smith">
                            <img alt="Image placeholder" src="../../assets/img/theme/team-3-800x800.jpg"
                                 class="rounded-circle">
                        </a>
                        <a href="#" class="avatar avatar-sm" data-toggle="tooltip" data-original-title="Jessica Doe">
                            <img alt="Image placeholder" src="../../assets/img/theme/team-4-800x800.jpg"
                                 class="rounded-circle">
                        </a>
                    </div>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <span class="mr-2">100%</span>
                        <div>
                            <div class="progress">
                                <div class="progress-bar bg-success" role="progressbar" aria-valuenow="100"
                                     aria-valuemin="0" aria-valuemax="100" style="width: 100%;"></div>
                            </div>
                        </div>
                    </div>
                </td>
                <td class="text-right">
                    <div class="dropdown">
                        <a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown"
                           aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                            <a class="dropdown-item" href="#">Action</a>
                            <a class="dropdown-item" href="#">Another action</a>
                            <a class="dropdown-item" href="#">Something else here</a>
                        </div>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
@endsection
@push("custom-js")
@endpush
