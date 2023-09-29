@extends("layout.Admin.admin-layout")
@section('title', 'Update Semester')
@push("custom-css")
@endpush
@section("admin-content")
    <div class="container">
        <form method="post" action="{{route('admin.updateSemesterPost',[$currentSemester->id])}}">
            {{csrf_field()}}
            <div class="row">
                <div class="col-md-6 center">
                    <label><h3>Semester name</h3></label>
                    <div class="form-group">
                        <input type="text" class="form-control" id="name" name="name"
                              value="{{$currentSemester->name}}">
                    </div>
                    <label><h3>Semester description</h3></label>
                    <div class="form-group">
                        <input type="text" class="form-control" id="description" name="description"
                               value="{{$currentSemester->description}}">
                    </div>
                    <label><h3>Semester start date</h3></label>
                    <div class="form-group">
                        <input type="text" class="form-control" id="start_date" name="start_date"
                               value="{{$currentSemester->start_date}}">
                    </div>
                    <label><h3>Semester end date</h3></label>
                    <div class="form-group">
                        <input type="text" class="form-control" id="end_date" name="end_date"
                               value="{{$currentSemester->end_date}}">
                    </div>
                    <button class="btn btn-twitter col-sm-12" type="submit">Update Information</button>
                </div>
            </div>
        </form>
    </div>
@endsection
@push("custom-js")
@endpush
