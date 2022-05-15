@extends("layout.Admin.admin-layout")
@section('title', 'Manage Semester')
@push("custom-css")
@endpush
@section('breadcrumb')
    <div class="container">
        {{ Breadcrumbs::render('dashboard.semester.create', route('admin.dashboard'), route('admin.semester'), route('admin.createSemester')) }}
    </div>
@endsection
@section('admin-content')
    <div class="container">
        <br>
        <h1 class="text-primary">Create semester</h1>
        <span class="text-gray">
            The semester must be in the future and once it
            created and is in active, you can only change the name of the semester
        </span>
        <hr>
        <form action="{{route('admin.createSemester_post')}}" method="post" class="row m-0">
            {{csrf_field()}}
            <div class="col-12">
                @if(\Illuminate\Support\Facades\Session::has('success'))
                    @if(\Illuminate\Support\Facades\Session::get('success') == 1)
                        <div class="card bg-success text-white">
                            <div class="card-body">Create semester successfully. Click
                                <a href="{{route('admin.semester')}}" class="text-underline">here</a>
                                to go back to list semester.
                            </div>
                        </div>
                    @else
                        <div class="card bg-danger text-white">
                            <div class="card-body">Create semester unsuccessfully. Please try again
                            </div>
                        </div>
                    @endif
                @endif
                <br>
            </div>
            <div class="col-12 col-md-6">
                @if($errors->has('name'))
                    <div class="card bg-danger text-white rounded-0">
                        <div class="card-body p-1 rounded-0">
                            {{$errors->first('name')}}
                        </div>
                    </div>
                @endif
                <div class="form-group input-group-alternative">
                    <input type="text" title="Semester name" placeholder="Semester name"
                           class="form-control form-control-alternative" id="name" name="name" required>
                </div>
                <div class="form-group input-group-alternative">
                    <textarea name="description" id="description" class="form-control form-control-alternative"
                              placeholder="Semester description" rows="5" style="resize: none;"></textarea>
                </div>
                @if($errors->has('description'))
                    <div class="card bg-danger text-white">
                        <div class="card-body p-1 rounded-0">
                            {{$errors->first('description')}}
                        </div>
                    </div>
                @endif
                <hr>
                <p class="text-gray">
                    Current semester available date start from:
                </p>
                @if($errors->has('start_date'))
                    <div class="card bg-danger text-white rounded-0">
                        <div class="card-body p-1 rounded-0">
                            {{$errors->first('start_date')}}
                        </div>
                    </div>
                @endif
                <div class="form-group">
                    <div class="input-group input-group-alternative">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                        </div>
                        <input id="start_date" name="start_date" onchange="listenStartDate(this)"
                               class="form-control datepicker" placeholder="Start Date" type="text">
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
                        <input id="end_date" class="form-control datepicker" disabled placeholder="Start Date"
                               type="text">
                        <input type="hidden" id="end_date_hidden" name="end_date" class="form-control datepicker"
                               placeholder="Start Date">
                    </div>
                </div>
                @if($errors->has('end_date'))
                    <div class="card bg-danger text-white">
                        <div class="card-body p-1 rounded-0">
                            {{$errors->first('end_date')}}
                        </div>
                    </div>
                @endif
                <div class="col-12">
                    <button class="btn btn-block btn-primary" type="submit">Create</button>
                </div>
                <br>
            </div>
            <div class="col-12 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h1>Latest semester</h1>
                        <p>{{$lastSemester->name}}</p>
                        <hr>
                        <div class="row">
                            <h3 class="col-12">Duration:</h3>
                            <p class="col">From: {{\App\Helpers\DateTimeHelper::formatDate($lastSemester->start_date)}}</p>
                            <p class="col">To: {{\App\Helpers\DateTimeHelper::formatDate($lastSemester->end_date)}}</p>
                        </div>
                        <hr>
                        <h3>Status:</h3>
                        @if(\App\Helpers\DateTimeHelper::isNowPassedDate($lastSemester->end_date))
                            <h1 class="text-muted font-weight-light">Ended</h1>
                        @elseif(\App\Helpers\DateTimeHelper::isNowPassedDate($lastSemester->start_date)
                        && !\App\Helpers\DateTimeHelper::isNowPassedDate($lastSemester->end_date))
                            <h1 class="text-success font-weight-light">Ongoing</h1>
                        @else
                            <h1 class="text-warning font-weight-light">Incoming</h1>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
@push("custom-js")
    <script>
        $(document).ready(function () {
            $('.datepicker').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                startDate: "today",
                // endDate: "today",
                // maxDate: today
            });
        });
        function listenStartDate(inputSection) {
            let end_date_hidden = document.getElementById('end_date_hidden');
            let end_date = document.getElementById('end_date');
            let valueDate = inputSection.value;
            let from = valueDate.split('-');
            let valueParsed = new Date(from[0], from[1], from[2]);
            valueParsed.setMonth(valueParsed.getMonth() + 3);
            let year = valueParsed.getFullYear();
            let month = valueParsed.getMonth();
            let date = valueParsed.getDate();
            if (month < 10) month = '0' + month;
            if (date < 10) date = '0' + date;
            let valueAssign = year + "-" + month + "-" + date;
            end_date_hidden.value = valueAssign;
            end_date.value = valueAssign;
        }
    </script>
@endpush
