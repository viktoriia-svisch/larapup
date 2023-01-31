@extends("layout.Coordinator.coordinator-layout")
@section('title', 'Faculty '.$facultySemester->faculty->name.' - Publishing')
@push("custom-css")
    <style>
        .card-Placeholder::before {
            display: block;
            content: 'Attached Image';
            color: silver;
            position: absolute;
            top: 0;
            left: 2rem;
            -webkit-transform: translate(0, -100%);
            -moz-transform: translate(0, -100%);
            -ms-transform: translate(0, -100%);
            -o-transform: translate(0, -100%);
            transform: translate(0, -100%);
            background: transparent;
            padding: 0 0.5rem;
        }
        .cloning-section {
            position: relative;
        }
        .btn-img-preview {
            position: absolute;
            left: 50%;
            bottom: 0;
            display: none;
            -webkit-transform: translate(-50%, 50%);
            -moz-transform: translate(-50%, 50%);
            -ms-transform: translate(-50%, 50%);
            -o-transform: translate(-50%, 50%);
            transform: translate(-50%, 50%);
        }
        .img-preview:hover .btn-img-preview {
            display: inline-block;
        }
        .btn-img-preview:hover {
            -webkit-transform: translate(-50%, calc(-1px + 50%));
            -moz-transform: translate(-50%, calc(-1px + 50%));
            -ms-transform: translate(-50%, calc(-1px + 50%));
            -o-transform: translate(-50%, calc(-1px + 50%));
            transform: translate(-50%, calc(-1px + 50%));
        }
        .img-prev-tag {
            max-width: 250px;
            max-height: 100px;
            overflow: hidden;
            min-width: 150px;
            object-position: center;
            object-fit: cover
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
        @if (Session::has('action_response') || sizeof($errors->all()) > 0)
            @if (Session::get('action_response')['status_ok'])
                <div class="col-12 m-0 p-0">
                    <div class="card bg-success text-white">
                        <div class="card-body" style="padding: 1rem;">
                            {{Session::get('action_response')['status_message']}}
                        </div>
                    </div>
                </div>
            @else
                @if ($errors->first())
                    <div class="col-12 m-0 p-0">
                        <div class="card bg-danger text-white">
                            <div class="card-body" style="padding: 1rem;">
                                {{$errors->first()}}
                            </div>
                        </div>
                    </div>
                @else
                    <div class="col-12 m-0 p-0">
                        <div class="card bg-danger text-white">
                            <div class="card-body" style="padding: 1rem;">
                                {{Session::get('action_response')['status_message']}}
                            </div>
                        </div>
                    </div>
                @endif
            @endif
            <br>
        @endif
        <br>
        <div class="form-group">
            <label for="title">Title of the published</label>
            <input type="text" class="form-control form-control-alternative" id="title" name="title"
                   value="{{$published->title}}" placeholder="Publishing title">
            <small class="form-text">Not exceed 170 characters and is required at least 3 characters.</small>
        </div>
        <hr style="width: 50%; margin: auto;">
        <br>
        <section id="formEntering">
            <div class="card cloning-section mb-5">
                <div class="card-body form-group">
                    <label for="description">Publish content</label>
                    <textarea class="form-control form-control-alternative" id="description" name="description"
                              rows="10"
                              placeholder="Write a section text here...">{{$published->content}}</textarea>
                    <small class="form-text">
                        Not exceed 1500 characters and is required at least 3 characters.
                    </small>
                    <br>
                </div>
            </div>
            <div class="card card-Placeholder">
                <div class="card-body row m-0 d-flex flex-wrap align-items-center" id="imagePreviewSection">
                    @if ($published && sizeof($published->publish_image) < 11)
                        <label class="m-0 btn btn-primary d-flex justify-content-center align-items-center"
                               style="height: 100px;" id="addImageBtn">
                            <i class="fa fa-plus"></i>
                            Add image
                            <input type="file" name="image[]" accept="image/jpeg, image/png" hidden
                                   class="form-control">
                        </label>
                    @endif
                    <div class="img-preview position-relative">
                        <button class="btn btn-danger btn-img-preview" type="button">
                            <i class="fa fa-trash"></i>
                        </button>
                        <a href="" target="_blank">
                            <img alt="" class="img-thumbnail rounded ml-1 mr-1"
                                 src="https://www.rightstufanime.com/images/productImages/850527003226_anime-Bakemonogatari-Blu-ray-Complete-Set-S-Limited-Edition-primary.jpg"
                                 style="max-width: 250px; max-height: 100px; overflow: hidden; min-width: 150px; object-position: center; object-fit: cover">
                        </a>
                    </div>
                    @if ($published && sizeof($published->publish_image) > 0)
                        @foreach($published->publish_image  as $image)
                            <div class="img-preview position-relative">
                                <button class="btn btn-danger btn-img-preview" type="button">
                                    <i class="fa fa-trash"></i>
                                </button>
                                <img alt="" class="img-thumbnail rounded ml-1 mr-1 img-prev-tag"
                                     src="{{route("resources.publishes", [$facultySemester->id, $published->id, $image->image_path])}}">
                                <input type="hidden" name="old_image[]" value="{{$image->image_path}}">
                            </div>
                        @endforeach
                    @endif
                    <div class="d-flex justify-content-center align-items-center" style="width: 200px; height: 100px;">
                        <span class="text-muted">Maximum 10 images</span>
                    </div>
                </div>
            </div>
        </section>
        <hr>
        <div class="row m-0 p-0">
            <div class="col-auto pl-0">
                <button type="button" class="btn btn-danger">Cancel</button>
            </div>
            <div class="col pr-0">
                <button type="submit" class="btn btn-block btn-success">Publish</button>
            </div>
        </div>
    </form>
@endsection
@push("custom-js")
    <script>
        let SectionAppendImage = $("#imagePreviewSection");
        function createPreviewDom(blobImage, file) {
            let imgPreviewContainer = $("<div/>").addClass("position-relative");
            let button = $("<button/>").addClass("btn btn-danger btn-img-preview").attr("type", "button");
            let contentButton = $("<i/>").addClass("fa fa-trash");
            let img = $("<img/>").attr("alt", "prev-img").attr("src", blobImage).addClass("img-thumbnail rounded ml-1 mr-1 img-prev-tag");
            let imgInput = $("<input/>").attr("type", "file").attr("name", "old_image[]").attr("hidden").val(file);
            button.append(contentButton);
            imgPreviewContainer.append(button).append(img).append(imgInput);
            SectionAppendImage.append(imgPreviewContainer);
        }
        function cloneWriteSection() {
            let checkDomSection = document.getElementsByClassName("cloning-section");
            if (checkDomSection.length > 5) {
                flashMessage("You can only have 5 sections of text!", true, 4000);
                return;
            }
            formToClone.clone(true).appendTo("#formEntering").find("input, textarea").val("").find("textarea").val("").end();
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
