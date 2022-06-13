@extends("layout.Admin.admin-layout")
@section('title', 'Create Faculty')
@push("custom-css")
@endpush
@section('breadcrumb')
    <div class="container">
        {{ Breadcrumbs::render('dashboard.faculty', route('admin.dashboard'), route('admin.faculty')) }}
    </div>
@endsection
@section("admin-content")
    <div class="container">
        <h1 class="text-primary">Add new faculty</h1>
        <p class="text-muted">Add faculty to the system.</p>
        <form action="{{route('admin.createFaculty_post')}}" method="post">
            {{csrf_field()}}
            <h2 class="text-primary">Faculty name</h2>
                <div class="form-group input-group-alternative">
                    <input type="text" title="Faculty name" placeholder="Faculty name"
                        class="form-control form-control-alternative" id="name" name="name">
                </div>
            <div class="col-12 row m-0">
                <div class="col-12">
                        <button class="btn btn-block m-0 btn-success">Create</button>
                </div>
            </div>
        </form>
        <br>
        <h1 class="text-primary">Faculties</h1>
        <p class="text-muted">Display all the faculty within the system.</p>
        <hr>
        @if(count($listFaculty) > 1 )
            @foreach($listFaculty as $faculty)
            <div class="card mb-3">
                <div class="card-body">
                        <h2 class="col-12">{{$faculty->name}}</h2>
                <br>
                </div>
            </div>
            @endforeach
        @else 
        <h1 class="m-auto text-muted">No faculty in this school</h1>
        @endif
    </div>
@endsection
@push("custom-js")
@endpush
