@extends("layout.Coordinator.coordinator-layout")
@section('title', 'Manage Semester')
@push("custom-css")
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
@section("coordinator-content")
    <div class="container" style="margin-bottom: 10vw">
        <form action="{{route("coordinator.semester.detail.add", [$semester->id])}}" method="get">
            <label class="col-12" for="searchCoordinator">
                Available coordinator
            </label>
            <div class="col-12 row m-0">
                <div class="col">
                    <div class="form-group">
                        <input style="margin-top: 1vw" class="form-control form-control-alternative"
                               type="text" placeholder="search for name, email" id="searchCoordinator">
                    </div>
                </div>
                <div class="col-auto d-flex align-items-center">
                    <button class="btn btn-icon">
                        <i class="fas fa-search"></i>
                        <span>Search</span>
                    </button>
                </div>
            </div>
            <small class="text-muted">
                Only faculties that haven't appeared and coordinators haven't participated are shown.
            </small>
        </form>
        <hr>
        @include("layout.response.errors")
        <form action="{{route("coordinator.semester.detail.addPost", [$semester->id])}}" id="addPostForm" method="post">
            @csrf
            <div class="form-group">
                <label for="faculty" class="form-control-label">Available Faculties</label>
                <select class="form-control form-control-alternative" name="faculty_id" id="faculty"
                        data-dependent="Faculty">
                    <option value="-1">------------------</option>
                    @foreach($faculties as $faculty)
                        <option value="{{$faculty->id}}">{{$faculty->name}}</option>
                    @endforeach
                </select>
                <input type="hidden" id="coordinator_id" name="coordinator_id">
            </div>
            <hr>
            <div class="form-group">
                <label for="firstDeadline">First deadline</label>
                <input id="firstDeadline" class="form-control form-control-alternative datepicker"
                       placeholder="Expected End Date" name="first_deadline" type="text">
            </div>
            <div class="form-group">
                <label for="secondDeadline">Second deadline</label>
                <input id="secondDeadline" name="second_deadline" placeholder="Expected End Date" type="text"
                       class="form-control form-control-alternative datepicker">
            </div>
            <div class="form-group">
                <label for="description">Description for the faculty</label>
                <textarea name="description" id="description" rows="10" maxlength="1500"
                          minlength="3" class="form-control form-control-alternative">N/D</textarea>
            </div>
            <div class="col-12 d-flex justify-content-between">
                <a href="{{route("coordinator.semester.detail", [$semester->id])}}"
                   class="btn btn-secondary btn-icon">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back</span>
                </a>
                <button type="submit" class="btn btn-success btn-icon">
                    <span>Add new Faculty</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </form>
    </div>
    {{csrf_field()}}
@endsection
@push("custom-js")
    <script>
        function submitting(coordinator_id) {
            $("#coordinator_id").val(coordinator_id);
            $("#addPostForm").submit();
        }
        $(document).ready(function () {
            let startDate = Date.createFromMysql('{{$semester->start_date}}');
            let endDate = Date.createFromMysql('{{$semester->end_date}}');
            $('.datepicker').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                startDate: startDate,
                endDate: endDate
            });
        });
        Date.createFromMysql = function (mysql_string) {
            let t, result = null;
            if (typeof mysql_string === 'string') {
                t = mysql_string.split(/[- :]/);
                //when t[3], t[4] and t[5] are missing they defaults to zero
                result = new Date(t[0], t[1] - 1, t[2], t[3] || 0, t[4] || 0, t[5] || 0);
                result.setDate(+t[2] + 1);
            }
            return result;
        };
    </script>
    <script>
    </script>
@endpush
