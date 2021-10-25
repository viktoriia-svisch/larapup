@extends("layout.Admin.admin-layout")
@section('title', 'Manage Semester')
@push("custom-css")
@endpush
@section('admin-content')
    <div class="container">
        <br>
        <h1 class="text-primary">Create semester</h1>
        <span class="text-gray">
            The semester must be in the future and once it
            created and is in active, you can only change the name of the semester
        </span>
        <hr>
        <form method="post" class="row">
            <div class="col-12">
                <div class="card bg-success text-white">
                    <div class="card-body">Create semester successfully. Click
                        <a href="{{route('admin.semester')}}" class="text-underline">here</a>
                        to go back to list semester.
                    </div>
                </div>
                <br>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-group input-group-alternative">
                    <input type="text" title="Semester name" placeholder="Semester name"
                           class="form-control form-control-alternative" id="name" name="name">
                </div>
                <div class="form-group input-group-alternative">
                    <textarea name="description" id="description" class="form-control form-control-alternative"
                              placeholder="Semester description" rows="5" style="resize: none;"></textarea>
                </div>
                <hr>
                <p class="text-gray">
                    Current semester available date start from:
                </p>
                <div class="form-group">
                    <div class="input-group input-group-alternative">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                        </div>
                        <input class="form-control datepicker" placeholder="Start Date" type="text" value="10/22/2018">
                    </div>
                </div>
                <p class="text-gray">
                    Expected to end by setting
                </p>
                <div class="form-group">
                    <div class="input-group input-group-alternative">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                        </div>
                        <input class="form-control datepicker" disabled placeholder="Start Date" type="text" value="10/22/2018">
                    </div>
                </div>
                <div class="col-12">
                    <button class="btn btn-block btn-primary">Create</button>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h1>Latest semester</h1>
                        <p>Fall Semester</p>
                        <hr>
                        <div class="row">
                            <h3 class="col-12">Duration:</h3>
                            <p class="col">From: 22/10/2018</p>
                            <p class="col">To: 12/12/2018</p>
                        </div>
                        <hr>
                        <h3>Status:</h3>
                        <h1 class="text-warning font-weight-light">Started</h1>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
@push("custom-js")
    <script>
        $(document).ready(function() {
            $('.datepicker').datepicker({
                format: 'mm / dd / yyyy',
                autoclose:true,
                startDate: "today",
                // endDate: "today",
                // maxDate: today
            });
        })
    </script>
@endpush
