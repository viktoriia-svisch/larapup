@extends("layout.Student.student-layout")
@section('title', 'Faculty Math')
@push("custom-css")
@endpush
@section('breadcrumb')
    <div class="container">
        {{ Breadcrumbs::render('dashboard.faculty.detail', route('student.dashboard'), route('student.faculty'), $faculty, route('student.faculty.detail', [$faculty->id])) }}
    </div>
@endsection
@section("student-content")
    <div class="container-fluid row">
        <div class="col-sm-12 col-md-8">
            <div class="col-12">
                <span class="text-muted">
                    Faculty:
                </span>
                <h1 class="heading-title">{{$faculty->name}}</h1>
                <hr>
            </div>
            <div class="col-12">
                {{nl2br($faculty->description)}}
            </div>
            <hr>
            <div class="col-12 row">
                <div class="col-12 col-sm-6">
                    <span class="text-muted">First deadline</span>
                    <h3>{{\App\Helpers\DateTimeHelper::formatDateTime($faculty->first_deadline)}}</h3>
                </div>
                <div class="col-12 col-sm-6">
                    <span class="text-muted">Second deadline</span>
                    <h3>{{\App\Helpers\DateTimeHelper::formatDateTime($faculty->second_deadline)}}</h3>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-md-4">
            <h3 class="text-center">Coordinator</h3>
            <div class="card col-12">
                <div class="card-body row">
                    <div class="col-auto" style="width: 45px; height: 45px;">
                    </div>
                    <div class="col">
                        {{$faculty->faculty_coordinator->coordinator->name}}
                    </div>
                </div>
            </div>
            <hr>
            <h3 class="text-center">Members</h3>
        </div>
    </div>
@endsection
@push("custom-js")
@endpush
