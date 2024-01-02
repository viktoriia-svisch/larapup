@extends("coordinator.Faculty.faculty-detail")
@section('title', 'Faculty '.$facultySemester->faculty->name.' - Members Manage')
@push("custom-css")
@endpush
@section('faculty-detail')
    <form
        action="{{route("coordinator.faculty.students.manage", [$facultySemester->faculty_id, $facultySemester->semester_id])}}"
        class="col-12 row">
        @csrf
        <div class="col">
            <div class="form-group">
                <input type="text" name="search" id="searchInput" class="form-control form-control-alternative"
                       placeholder="Searching name, or email of a person"
                       value="{{isset($search) && $search ? $search : ''}}">
            </div>
        </div>
        <div class="col-auto pr-0">
            <button class="btn btn-icon btn-default" type="submit" style="border-radius: .375rem 0 0 .375rem">
                <i class="fa fa-search"></i>
                Search
            </button>
        </div>
        <div class="col-auto pl-0">
            <a href="{{route("coordinator.faculty.students.manage", [$facultySemester->faculty_id, $facultySemester->semester_id])}}"
               class="btn btn-icon btn-danger" style="border-radius: 0 .375rem .375rem 0">
                <i class="fa fa-times"></i>
            </a>
        </div>
    </form>
    <hr>
    <div class="row m-0">
        <div class="col-12 col-md-6">
            <h2>
                Student participated
                <small class="text-muted">{{$studentUnAvailable->total()}}</small>
            </h2>
            <div class="table-responsive">
                <table class="table align-items-center">
                    <thead class="thead-light">
                    <tr>
                        <th scope="col" class="col">Name</th>
                        <th scope="col" class="col-auto text-center">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($studentUnAvailable as $student)
                        <tr>
                            <th scope="row" class="col">
                                <div class="media align-items-center">
                                    <div class="media-body">
                                        <span class="mb-0 text-sm">
                                            {{$student->first_name . ' ' . $student->last_name}}
                                        </span>
                                    </div>
                                </div>
                            </th>
                            <td class="col-auto">
                                <a href="" class="btn btn-icon btn-danger">
                                    <i class="fas fa-trash"></i>
                                    Remove
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            {{$studentUnAvailable->appends(['available' => $studentAvailable->currentPage()])->links()}}
        </div>
        <div class="col-12 col-md-6">
            <h2>
                Student available to add
                <small class="text-muted">{{$studentAvailable->total()}}</small>
            </h2>
            <div class="table-responsive">
                <table class="table align-items-center">
                    <thead class="thead-light">
                    <tr>
                        <th scope="col" class="col">Name</th>
                        <th scope="col" class="col-auto text-center">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($studentAvailable as $student)
                        <tr>
                            <th scope="row" class="col">
                                <div class="media align-items-center">
                                    <div class="media-body">
                                        <span class="mb-0 text-sm">
                                            {{$student->first_name . ' ' . $student->last_name}}
                                        </span>
                                    </div>
                                </div>
                            </th>
                            <td class="col-auto">
                                <a href="" class="btn btn-icon btn-success">
                                    <i class="fas fa-plus"></i>
                                    Add
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            {{$studentAvailable->appends(['unavailable' => $studentUnAvailable->currentPage()])->links()}}
        </div>
    </div>
    <hr>
@endsection
@push("custom-js")
@endpush
