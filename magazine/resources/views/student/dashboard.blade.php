@extends("layout.Student.student-layout")
@section('title', 'Dashboard')
@push("custom-css")
@endpush
@section("student-content")
    <div class="container-fluid">
        <div class="container">
            <br>
            <div class="row">
                <div class="col-12 col-sm-5 col-md-4">
                    <h2>Current Semester: Fall Semester</h2>
                    <p>Duration: 2222/22/22 - 3333/33/33</p>
                </div>
                <div class="col-12 col-sm-7 col-md-8">
                    <h2>Current Active Faculty:</h2>
                    <div class="card">
                        <div class="card-body row m-0">
                            <div class="col">
                                <h1 class="col-12 m-0">Faculty Math</h1>
                                <p>Next Deadline: 1111/11/11 11:11:11</p>
                            </div>
                            <div class="col-auto d-flex align-items-center">
                                <a class="btn btn-primary">
                                    More
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <hr>
        <div class="container">
        </div>
    </div>
@endsection
@push("custom-js")
@endpush
