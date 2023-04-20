@extends("layout.Coordinator.coordinator-layout")
@section('title', 'Update Information Student')
@push("custom-css")
@endpush
@section("coordinator-content")
    <div class="container row col-md-12">
        <div class="col-6 m-auto">
            <h1>Update</h1>
            <hr>
                <form method="post" action="{{route('coordinator.faculty.settingPost', [$facultyUpdate->faculty_id, $facultyUpdate->semester_id])}}">
                    {{csrf_field()}}
                    @if($errors->has('first_deadline'))
                        <div class="card bg-danger text-white rounded-0">
                            <div class="card-body p-1 rounded-0">
                                {{$errors->first('first_deadline')}}
                            </div>
                        </div>
                    @endif
                    <div class="card-body p-1 rounded-0">
                        <label>First-Deadline<span style="color: red">*</span></label>
                        <input class="form-control datepicker" type="text" name="first_deadline"
                               placeholder="{{\App\Helpers\DateTimeHelper::formatDateInput($facultyUpdate->first_deadline)}}"
                               value="{{\App\Helpers\DateTimeHelper::formatDateInput($facultyUpdate->first_deadline)}}">
                    </div>
                    @if($errors->has('second_deadline'))
                        <div class="card bg-danger text-white rounded-0">
                            <div class="card-body p-1 rounded-0">
                                {{$errors->first('second_deadline')}}
                            </div>
                        </div>
                    @endif
                    <div class="card-body p-1 rounded-0" style="margin-top: 2vw">
                        <label>Second-Deadline<span style="color: red">*</span></label>
                        <input class="form-control datepicker" type="text" name="second_deadline"
                               placeholder="{{\App\Helpers\DateTimeHelper::formatDateInput($facultyUpdate->second_deadline)}}"
                               value="{{\App\Helpers\DateTimeHelper::formatDateInput($facultyUpdate->second_deadline)}}">
                    </div>
                    @if($errors->has('description'))
                        <div class="card bg-danger text-white rounded-0">
                            <div class="card-body p-1 rounded-0">
                                {{$errors->first('description')}}
                            </div>
                        </div>
                    @endif
                    <div class="card-body p-1 rounded-0" style="margin-top: 2vw">
                        <label>Description</label>
                        <input class="form-control" type="text" name="description" placeholder="{{$facultyUpdate->description}}"
                               value="{{$facultyUpdate->description}}">
                    </div>
                    <hr>
                    <input type="hidden" name="semester_id" value="{{$facultyUpdate->semester_id}}">
                    <input type="hidden" name="faculty_id" value="{{$facultyUpdate->faculty_id}}">
                    <input class="btn btn-twitter col-sm-12" type="submit" value="Update Faculty"/>
               </form>
        </div>
    </div>
@endsection
@push("custom-js")
    <script>
        $(document).ready(function () {
            let endDate = Date.createFromMysql('{{$facultyUpdate->semester->end_date}}');
            let startDate = Date.createFromMysql('{{$facultyUpdate->semester->start_date}}');
            $('.datepicker').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                endDate: endDate,
                startDate: startDate
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
@endpush
