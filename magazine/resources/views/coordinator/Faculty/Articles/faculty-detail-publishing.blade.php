@extends("layout.Coordinator.coordinator-layout")
@section('title', 'Faculty '.$facultySemester->faculty->name.' - Publishing')
@push("custom-css")
    <style>
        .image-holder {
            width: 100%;
            height: 100%;
            min-height: 200px;
            max-height: 300px;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 0.5rem;
            border: 1px solid whitesmoke;
            position: relative;
        }
        .card-Placeholder::before {
            display: block;
            content: 'Attached Image';
            color: silver;
            position: absolute;
            top: 0;
            left: 2rem;
            -webkit-transform: translate(0, -50%);
            -moz-transform: translate(0, -50%);
            -ms-transform: translate(0, -50%);
            -o-transform: translate(0, -50%);
            transform: translate(0, -50%);
            background: white;
            padding: 0 0.5rem;
        }
        .cloning-section {
            position: relative;
        }
        .deleteBtn {
            position: absolute;
            bottom: 0;
            right: 1rem;
            -webkit-transform: translate(0, 50%);
            -moz-transform: translate(0, 50%);
            -ms-transform: translate(0, 50%);
            -o-transform: translate(0, 50%);
            transform: translate(0, 50%);
        }
    </style>
@endpush
@section('breadcrumb')
    <div class="container">
        {{ Breadcrumbs::render('dashboard.faculty.detail.newPublish', route('coordinator.dashboard'),
        route('coordinator.faculty'), route('coordinator.faculty.dashboard', [$facultySemester->faculty_id, $facultySemester->semester_id]),
        route('coordinator.faculty.article.publish', [$facultySemester->faculty_id, $facultySemester->semester_id, $article->id])) }}
    </div>
@endsection
@section('coordinator-content')
    <form class="container" enctype="multipart/form-data" method="post"
          action="{{route("coordinator.faculty.article.publishPost", [$facultySemester->faculty_id, $facultySemester->semester_id, $article->id])}}">
        @csrf
        <h1 class="heading-title">Publishing</h1>
        <h3>{{$article->student->first_name . " " . $article->student->last_name}}'s uploads</h3>
        <div class="row m-0 p-0">
            <div class="col row m-0 p-0">
                @foreach($article->article_file as $file)
                    <div class="col-12 col-md-4 p-2">
                        <a class="card text-black" href="#">
                            <div class="card-body row m-0 p-3">
                                <div class="col-auto d-flex align-items-center">
                                    <div
                                        class="icon icon-shape bg-default text-white rounded-circle shadow">
                                        @if ($file->type == 0)
                                            <i class="fas fa-file-word"></i>
                                        @else
                                            <i class="fas fa-file-image"></i>
                                        @endif
                                    </div>
                                </div>
                                <div class="col overflow-hidden d-flex align-items-center"><span
                                        class="font-weight-bold">{{\Illuminate\Support\Str::limit($file->title, 15)}}</span>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
            <div class="col-auto">
                123
            </div>
        </div>
        <div class="col-12">
            Last Updated at: {{\App\Helpers\DateTimeHelper::formatDateTime($article->updated_at)}}
        </div>
        <br>
        <hr>
        <h1>Writing Publishing</h1>
        <div class="col-12">
            @if (Session::has('action_response'))
                @if (Session::get('action_response')['status_ok'])
                    <div class="col-md-6 col-12 m-auto">
                        <div class="card bg-success text-white">
                            <div class="card-body" style="padding: 1rem;">
                                {{Session::get('action_response')['status_message']}}
                                Click <a href="{{route('admin.semester')}}" class="text-underline">here</a>
                                to go back to list semester.
                            </div>
                        </div>
                    </div>
                @else
                    <div class="col-md-6 col-12 m-auto">
                        <div class="card bg-danger text-white">
                            <div class="card-body" style="padding: 1rem;">
                                {{Session::get('action_response')['status_message']}}
                            </div>
                        </div>
                    </div>
                @endif
                <br>
            @endif
            <br>
        </div>
        <div class="form-group">
            <input type="text" class="form-control form-control-alternative" id="title" name="title"
                   placeholder="Publishing title">
        </div>
        <hr style="width: 50%; margin: auto;">
        <section id="formEntering">
            <div class="card cloning-section mb-5">
                <div class="card-body form-group">
                    <textarea class="form-control form-control-alternative" name="description[]" rows="5"
                              placeholder="Write a section text here..."></textarea>
                    <br>
                    <div class="card card-Placeholder">
                        <div class="card-body">
                            <div class="col-12 p-0 m-0">
                                <label class="image-holder">
                                    <i class="fas fa-image fa-4x"></i>
                                    <input type="file" name="image[]" accept="image/jpeg, image/png" hidden
                                           class="form-control">
                                </label>
                            </div>
                            <br>
                            <input type="text" placeholder="Description of the image" name="imageDescription[]"
                                   id="imageDescription" class="form-control form-control-alternative">
                        </div>
                    </div>
                </div>
                <div class="deleteBtn">
                    <button class="btn btn-danger" type="button" onclick="deleteSection(this)">Delete</button>
                </div>
            </div>
        </section>
        <button class="btn btn-block btn-default" type="button" onclick="cloneWriteSection()">Add more section</button>
        <hr>
        <div class="row m-0 p-0">
            <div class="col-auto pl-0">
                <button type="button" class="btn btn-danger">Cancel</button>
            </div>
            <div class="col pr-0">
                <button type="submit" class="btn btn-block btn-success">Submit</button>
            </div>
        </div>
    </form>
@endsection
@push("custom-js")
    <script>
        let formEntering = $("#formEntering");
        let formToClone = $(".cloning-section");
        function cloneWriteSection() {
            let checkDomSection = document.getElementsByClassName("cloning-section");
            if (checkDomSection.length > 5) {
                flashMessage("You can only have 5 sections of text!", true, 4000);
                return;
            }
            formToClone.clone().appendTo("#formEntering").find("input, textarea").val("").find("textarea").val("").end();
        }
        function deleteSection(dom) {
            let checkDomSection = document.getElementsByClassName("cloning-section");
            if (checkDomSection.length < 2) {
                flashMessage("You must have at lease 1 section in your publish", true, 4000);
                return;
            }
            Array.from(checkDomSection).forEach((domSection, index) => {
                if (domSection.contains(dom)) {
                    document.getElementById("formEntering").removeChild(domSection);
                }
            });
        }
    </script>
@endpush
