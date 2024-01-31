@extends("layout.Admin.admin-layout")
@section('title', 'Manage Semester')
@push("custom-css")
    <style>
        .border-4 {
            border: 2px solid transparent;
        }
    </style>
@endpush
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
@section('breadcrumb')
    <div class="container">
            {{ Breadcrumbs::render('dashboard.faculty.create', route('admin.dashboard'), route('admin.faculty'), route('admin.chooseSemester')) }}
    </div>
@endsection
@section('admin-content')
<div class="container">
        <br>
        <h1 class="heading-title">Semester: {{$semester->name}}  </h1>
        <span class="text-gray">
            You can add faculty to this semester in this section and add student to available faculty
        </span>
        <hr>
        <h1 class="text-primary">Current Faculties</h1>
        <p class="text-muted">Display all the faculty within the semester.</p>
        <div class="col-12">
            @if (\Session::has('action_response'))
                @if (\Session::get('action_response')['status_ok'])
                    <div class="col-md-6 col-12 m-auto">
                        <div class="card bg-success text-white">
                            <div class="card-body" style="padding: 1rem;">
                                {{\Session::get('action_response')['status_message']}}
                            </div>
                        </div>
                    </div>
                @else
                    <div class="col-md-6 col-12 m-auto">
                        <div class="card bg-danger text-white">
                            <div class="card-body" style="padding: 1rem;">
                                {{\Session::get('action_response')['status_message']}}
                            </div>
                        </div>
                    </div>
                @endif
                <br>
            @endif
            <br>
        </div>
        @if (count($FacultySemester) == 0)
        <h2 class="text-center text-muted">No available faculty</h2>
        @endif
        @foreach($FacultySemester as $FacuSeme)
            @csrf
            <div class="card mb-2">
                <div class="card-body row">
                    <div class="col">
                        <div class="col-auto d-flex align-items-center">
                            <h1 class="heading-title">{{$FacuSeme->name}}</h1>
                        </div>
                    </div>
                    <div class="col-auto d-flex align-items-center">
                        <a class="btn btn-block m-0 btn-success" href="{{route('admin.addStudentFaculty', [$FacuSeme->id])}}">
                            Student list
                        </a>
                        &nbsp;
                        &nbsp;
                            <button class="btn btn-block m-0 btn-success" id="submit" type="submit" data-fsid={{$FacuSeme->id}} data-toggle="modal" data-target="#delete">
                                Remove faculty
                            </button>
                    </div>
                </div>
            </div>
        @endforeach
        <br>
</div>
@endsection
@section('modal')
    <div class="col-md-4" id="modal">
        <div class="modal fade" id="delete" tabindex="-1" role="dialog" aria-labelledby="modal-form"
             aria-hidden="true">
            <div class="modal-dialog modal- modal-dialog-centered modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-body p-0">
                        <div class="card bg-secondary shadow border-0">
                            <div class="card-body px-lg-5 py-lg-5">
                                    <form action="{{route('admin.deleteSemesterFaculty')}}" method="post" >
                                        {{csrf_field()}}
                                        <h2 class="text-primary">Delete this faculty ?</h2>
                                        <p class="text-center text-muted">
                                            If you delete this faculty, it will delete all students belongs to the chosen semester faculty.
                                            You can manually delete specific students in student list.
                                        </p>
                                        <div class="modal-body">
                                        <input type="hidden" name="facu_seme_id" id="facu_seme_id" value="">
                                        <div class="col-12 m-0 p-0">
                                            <button class="btn btn-block m-0 btn-success" data-dismiss="modal">
                                                Cancel
                                            </button>
                                            <br>
                                            <button class="btn btn-block m-0 btn-danger" id="submit" type="submit">
                                                Delete
                                            </button>
                                        </div>
                                        </div>
                                  </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
<script src="{{asset('js/app.js')}}"></script>
<script>
    $(function(){
        $(document).on('show.bs.modal','#delete', function(event){
        var button = $(event.relatedTarget)
        var facu_seme_id = button.data('fsid')
        var modal = $(this)
        modal.find('.modal-body #facu_seme_id').val(facu_seme_id);
        });
    });
</script>
