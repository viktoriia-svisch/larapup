@extends("coordinator.Faculty.faculty-detail")
@section('title', 'Faculty '.$facultySemester->faculty->name.' - Coordinator Manage')
@push("custom-css")
@endpush
@section('faculty-detail')
    <form
        action="{{route("coordinator.faculty.coordinators.manage", [$facultySemester->faculty_id, $facultySemester->semester_id])}}"
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
            <a href="{{route("coordinator.faculty.coordinators.manage", [$facultySemester->faculty_id, $facultySemester->semester_id])}}"
               class="btn btn-icon btn-danger" style="border-radius: 0 .375rem .375rem 0">
                <i class="fa fa-times"></i>
            </a>
        </div>
    </form>
    <hr>
    <div class="col-12">
        @include('layout.response.errors')
    </div>
    <div class="row m-0">
        <div class="col-12 col-md-6">
            <h2>
                Coordinator in-charge
                <small class="text-muted">{{$coordinatorUnAvailable->total()}}</small>
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
                    @foreach($coordinatorUnAvailable as $coordinator)
                        <tr>
                            <th scope="row" class="col">
                                <div class="media align-items-center">
                                    <div class="media-body">
                                        <span class="mb-0 text-sm">
                                            {{$coordinator->first_name . ' ' . $coordinator->last_name}}
                                        </span>
                                    </div>
                                </div>
                            </th>
                            <td class="col-auto">
                                <a href="{{route("coordinator.faculty.coordinators.manage.remove", [$facultySemester->faculty_id, $facultySemester->semester_id, $coordinator->id])}}"
                                   class="btn btn-icon btn-danger">
                                    <i class="fas fa-trash"></i>
                                    Remove
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            {{$coordinatorUnAvailable->appends(['available' => $coordinatorAvailable->currentPage()])->links()}}
        </div>
        <div class="col-12 col-md-6">
            <h2>
                Coordinator available to add
                <small class="text-muted">{{$coordinatorAvailable->total()}}</small>
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
                    @foreach($coordinatorAvailable as $coordinator)
                        <tr>
                            <th scope="row" class="col">
                                <div class="media align-items-center">
                                    <div class="media-body">
                                        <span class="mb-0 text-sm">
                                            {{$coordinator->first_name . ' ' . $coordinator->last_name}}
                                        </span>
                                    </div>
                                </div>
                            </th>
                            <td class="col-auto">
                                <a href="{{route("coordinator.faculty.coordinators.manage.add", [$facultySemester->faculty_id, $facultySemester->semester_id, $coordinator->id])}}"
                                   class="btn btn-icon btn-success">
                                    <i class="fas fa-plus"></i>
                                    Add
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            {{$coordinatorAvailable->appends(['unavailable' => $coordinatorUnAvailable->currentPage()])->links()}}
        </div>
    </div>
    <hr>
    <div class="col-12 d-flex">
        <a href="{{route("coordinator.faculty.students", [$facultySemester->faculty_id, $facultySemester->semester_id])}}"
           class="btn btn-icon btn-secondary">
            <i class="fas fa-arrow-left"></i>
            <span>Back</span>
        </a>
    </div>
@endsection
@push("custom-js")
@endpush
