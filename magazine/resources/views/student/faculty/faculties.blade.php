@extends("layout.Student.student-layout")
@section('title', 'All Faculties')
@push("custom-css")
@endpush
@section('breadcrumb')
    <div class="container">
        {{ Breadcrumbs::render('dashboard.faculty', route('student.dashboard'), route('student.faculty')) }}
    </div>
@endsection
@section("student-content")
    <div class="container">
        <h1>Current Active faculty</h1>
        <br>
        @if ($currentFaculty !== null)
            <div class="card">
                <div class="card-body">
                    <h2>{{$currentFaculty->name}}</h2>
                </div>
            </div>
        @else
            <h2 class="text-muted m-auto">No activity yet</h2>
            <br>
        @endif
        <hr>
        <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="pills-home-tab" data-toggle="pill"
                   href="#pills-passed" role="tab" aria-controls="pills-home" aria-selected="true">
                    Previous semesters's faculty
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="pills-profile-tab" data-toggle="pill"
                   href="#pills-future" role="tab" aria-controls="pills-profile" aria-selected="false">
                    Future Semesters's faculty
                </a>
            </li>
        </ul>
        <div class="tab-content" id="pills-tabContent">
            <div class="tab-pane fade show active" id="pills-passed" role="tabpanel" aria-labelledby="pills-home-tab">
                @if (count($passedFaculties) > 0)
                    @foreach($passedFaculties as $faculty)
                        <div class="card">
                            <div class="card-body">
                                <h2>{{$faculty->name}}</h2>
                            </div>
                        </div>
                    @endforeach
                @else
                    <h2 class="text-muted m-auto">No record found</h2>
                @endif
            </div>
            <div class="tab-pane fade" id="pills-future" role="tabpanel" aria-labelledby="pills-profile-tab">
                @if (count($futureFaculties) > 0)
                    @foreach($futureFaculties as $faculty)
                        <div class="card">
                            <div class="card-body">
                                <h2>{{$futureFaculties->name}}</h2>
                            </div>
                        </div>
                    @endforeach
                @else
                    <h2 class="text-muted m-auto">No record found</h2>
                @endif
            </div>
        </div>
    </div>
@endsection
@push("custom-js")
@endpush
