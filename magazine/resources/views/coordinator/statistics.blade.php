@extends("layout.Coordinator.coordinator-layout")
@section('title', 'View statistics')
@push("custom-css")
@endpush
<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.2/html5shiv.js"></script>
<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
@section('breadcrumb')
    <div class="container">
        {{ Breadcrumbs::render('dashboard.faculty', route('coordinator.coordinator'), route('coordinator.statistics')) }}
    </div>
@endsection
@section("coordinator-content")
    <div class="container">
        <h1>View statistics</h1>
        <p class="text-muted">You can view statistics of specific or all faculties in this section</p>
        <hr>
        <p class="text-muted">Statistics will show all articles and contributors within the selected faculty and semester</p>
    <body>
        <div class="container">
            <div class="col-lg-4">
                {{Form::open()}}
                <div class="form-group">
                        <label for="">Select faculty</label>
                        <select class="form-control" name="faculties" id="faculties">
                          <option value="0" disable="true" selected="true">=== Select Faculties ===</option>
                            @foreach ($faculties as $key => $value)
                              <option value="{{$value->id}}">{{ $value->name }}</option>
                            @endforeach
                        </select>
                </div>
                <div class="form-group">
                        <label for="">Select semester</label>
                        <select class="form-control" name="semesters" id="semesters">
                          <option value="0" disable="true" selected="true">=== Select Semester ===</option>
                        </select>
                </div>
                {{Form::close()}}
            </div>
        </div>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
        <script type="text/javascript">
            $('#faculties').on('change', function(e){
              console.log(e);
              var faculty_id = e.target.value;
              $.get('statistics/json-semester?faculty_id=' + faculty_id,function(data) {
                console.log(data);
                $('#semesters').empty();
                $('#semesters').append('<option value="0" disable="true" selected="true">=== Select Semester ===</option>');
                $.each(data, function(index, semestersObj){
                  $('#semesters').append('<option value="'+ semestersObj.id +'">'+ semestersObj.name +'</option>');
                })
              });
            });
        </script>
    </body>
@endsection
