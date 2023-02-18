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
                @if ($published && sizeof($published->publish_image) > 0)
                    <div class="row m-0 p-2">
                        @foreach($published->publish_image  as $image)
                            <div class="m-1 img-preview position-relative">
                                <button class="btn btn-danger btn-img-preview" onclick="deleteExistedImage(this)"
                                        type="button">
                                    <i class="fa fa-trash"></i>
                                </button>
                                <img alt="" class="img-thumbnail rounded ml-1 mr-1 img-prev-tag"
                                     src="{{asset('storage/'. \App\Helpers\StorageHelper::getPublishFilePath($facultySemester->id, $published->id, $image->image_path, true))}}">
                                <input type="hidden" name="old_image[]" value="{{$image->image_path}}">
                            </div>
                        @endforeach
                    </div>
                    <hr class="mt-2 mb-2">
                @endif
                <div class="card-body row m-0 d-flex flex-wrap align-items-center" id="imagePreviewSection">
                    <label class="m-1 btn btn-primary d-flex justify-content-center align-items-center"
                           style="height: 100px;" id="addImageBtn">
                        <i class="fa fa-plus"></i>
                        Add image
                        <input type="file" name="image[]" multiple accept="image/jpeg, image/png" class="form-control"
                               @if (!($published && sizeof($published->publish_image) < 11)) disabled @endif hidden
                               onchange="listUploadImage(this)" id="uploadFileInput">
                    </label>
                </div>
            </div>
        </section>
        <span class="text-muted">Maximum 10 images and each must not larger than 5MB</span>
        <hr>
        <div class="row m-0 p-0">
            <div class="col-auto pl-0">
                <button type="button" class="btn btn-danger">Cancel</button>
            </div>
            <div class="col pr-0">
                <button type="submit" class="btn btn-block btn-success" id="submitFormBtn">Publish</button>
            </div>
        </div>
    </form>
@endsection
@push("custom-js")
    <script>
        let SectionAppendImage = $("#imagePreviewSection");
        let uploadFileInput = $("#uploadFileInput");
        let publishFormBtn = $("#submitFormBtn");
        function createPreviewDom(blobImage, file) {
            let imgPreviewContainer = $("<div/>").addClass("m-1 img-preview img-preview-blob position-relative");
            let img = $("<img/>").attr("alt", "prev-img").attr("src", blobImage).addClass("img-thumbnail rounded ml-1 mr-1 img-prev-tag");
            imgPreviewContainer.append(img);
            SectionAppendImage.append(imgPreviewContainer);
        }
        function listUploadImage(event) {
            let fileList = event.files;
            // Delete old BlobFiles
            SectionAppendImage.find(".img-preview-blob").remove();
            let fileLarger = false;
            if (fileList.length > 0) {
                Array.from(fileList).forEach(file => {
                    if (typeof file.name == 'string') {
                        let fileReader = new FileReader();
                        fileReader.onload = function (e) {
                            createPreviewDom(e.target.result, file);
                            if (file.size > {{FILE_MAXSIZE / 2}}) {
                                flashMessage("There was a file that larger than 5MB", true, 10000);
                                publishFormBtn.prop('disabled', true);
                                fileLarger = true;
                            } else {
                                publishFormBtn.prop('disabled', false);
                            }
                        };
                        fileReader.readAsDataURL(file);
                    }
                })
            }
            @if($published)
            if (fileList.length + +"{{sizeof($published->publish_image)}}" > 10) {
                flashMessage("Can only accept 10 images (included existed images) for one publishing", true, 5000);
                publishFormBtn.prop('disabled', true);
            } else if (!fileLarger) {
                publishFormBtn.prop('disabled', false);
            }
            @else
            if (fileList.length > 10) {
                flashMessage("Can only accept 10 images for one publishing", true, 5000);
                publishFormBtn.prop('disabled', true);
            } else if (!fileLarger) {
                publishFormBtn.prop('disabled', false);
            }
            @endif
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
        function deleteExistedImage(target) {
            let domParent = target.parentElement;
            domParent.parentElement.removeChild(domParent);
        }
    </script>
@endpush
