@extends("student.faculty.faculty-detail")
@section('title', 'Faculty '.$facultySemester->faculty->name.' - Members')
@push("custom-css")
@endpush
@section('faculty-detail')
    <form action="{{route("student.faculty.members", [$facultySemester->faculty_id, $facultySemester->semester_id])}}"
          class="col-12 row">
        <div class="col">
            <div class="form-group">
                <input type="text" name="search" id="searchInput" class="form-control form-control-alternative"
                       placeholder="Searching name, or email of a person" value="{{isset($search) && $search ? $search : ''}}">
            </div>
        </div>
        <div class="col-auto pr-0">
            <button class="btn btn-icon btn-default" type="submit" style="border-radius: .375rem 0 0 .375rem">
                <i class="fa fa-search"></i>
                Search
            </button>
        </div>
        <div class="col-auto pl-0">
            <a href="{{route("student.faculty.members", [$facultySemester->faculty_id, $facultySemester->semester_id])}}"
               class="btn btn-icon btn-danger" style="border-radius: 0 .375rem .375rem 0">
                <i class="fa fa-times"></i>
            </a>
        </div>
    </form>
    <hr>
    <div class="row m-0">
        <div class="col-12 col-md-6">
            <h2>Coordinators in-charge
                <small class="text-muted">{{sizeof($coordinators)}}</small>
            </h2>
            <div class="table-responsive">
                <table class="table align-items-center">
                    <thead class="thead-light">
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Email</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($coordinators as $coor)
                        <tr>
                            <th scope="row">
                                <div class="media align-items-center">
                                    <div class="media-body">
                                        <span class="mb-0 text-sm">{{$coor->first_name . ' ' . $coor->last_name}}</span>
                                    </div>
                                </div>
                            </th>
                            <td>
                                {{$coor->email}}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <h2>Students participated
                <small class="text-muted">{{sizeof($students)}}</small>
            </h2>
            <div class="table-responsive">
                <table class="table align-items-center">
                    <thead class="thead-light">
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Email</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($students as $stu)
                        <tr>
                            <th scope="row">
                                <div class="media align-items-center">
                                    <div class="media-body">
                                        <span class="mb-0 text-sm">{{$stu->first_name . ' ' . $stu->last_name}}</span>
                                    </div>
                                </div>
                            </th>
                            <td>
                                {{$stu->email}}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <hr>
    <div class="col-12 d-flex justify-content-center align-items-center">
        {{$students->links()}}
    </div>
@endsection
@push("custom-js")
@endpush
