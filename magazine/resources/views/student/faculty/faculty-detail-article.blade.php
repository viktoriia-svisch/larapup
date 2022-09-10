@extends("student.faculty.faculty-detail")
@section('title', 'Faculty '.$facultySemester->faculty->name.' - Article')
@push("custom-css")
    <style>
        .modal-dialog {
            width: 95vw;
            max-width: 650px;
        }
    </style>
@endpush
@section('faculty-detail')
    <h2 class="heading-title">Submission</h2>
    <div class="card">
        <div class="card-body">
            <div class="col-12 row m-0 p-0">
                <div class="col-12 col-sm-6 text-center">
                    Deadline for upload:
                    @if (\App\Helpers\DateTimeHelper::isNowPassedDate($facultySemester->first_deadline))
                        <span class="text-muted">
                                {{\App\Helpers\DateTimeHelper::formatDateTime($facultySemester->first_deadline)}}
                            </span>
                    @else
                        <span class="text-danger">
                                {{\App\Helpers\DateTimeHelper::formatDateTime($facultySemester->first_deadline)}}
                            </span>
                    @endif
                </div>
                <div class="col-12 col-sm-6 text-center">
                    Deadline for final:
                    @if (\App\Helpers\DateTimeHelper::isNowPassedDate($facultySemester->second_deadline))
                        <span class="text-muted">
                                {{\App\Helpers\DateTimeHelper::formatDateTime($facultySemester->second_deadline)}}
                            </span>
                    @else
                        <span class="text-danger">
                                {{\App\Helpers\DateTimeHelper::formatDateTime($facultySemester->second_deadline)}}
                            </span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <hr>
    <div class="col-12 p-0 m-0 mb-5 mt-2 d-flex flex-column justify-content-center align-items-center">
        <a href="" class="btn btn-success btn-icon">
            <i class="fas fa-thumbs-up"></i>
            This Article was published
        </a>
        <small class="text-muted mt-3">
            By: 22221dasd <br>
            at 22/22/2222 22:22:22
        </small>
    </div>
    <div class="container-fluid row m-0 p-0">
        <div class="col-12 col-md-6 m-0 p-0 pr-4 border-right">
            <h2 class="col-12 row m-0 p-0 mb-4">
                <div class="h2 col p-0 m-0">
                    File Submission
                </div>
                <div class="col-auto d-flex align-item-center p-0">
                    <button class="btn btn-default btn-icon"
                            onclick="uploadWords()">
                        <i class="fas fa-upload"></i>
                        Upload new
                    </button>
                </div>
            </h2>
            @if ($article)
                @foreach($article->article_file as $file)
                    <div class="col-12 row m-0 p-0 mb-4">
                        <div class="col card">
                            <div class="card-body row">
                                <div class="col-auto d-flex align-items-center">
                                    <div class="icon icon-shape bg-danger text-white rounded-circle shadow">
                                        <i class="fas fa-file-word"></i>
                                    </div>
                                </div>
                                <div class="col">
                                    <span class="h2 font-weight-bold mb-0">
                                        {{\Illuminate\Support\Str::limit($file->title, 20)}}
                                    </span>
                                    <br>
                                    <small class="text-muted">
                                        Modified at:
                                        {{\App\Helpers\DateTimeHelper::formatDateTime($file->updated_at ?? $file->created_at)}}
                                    </small>
                                </div>
                                <div class="col-auto row m-0 p-0">
                                    <div class="col-auto d-flex align-items-center p-0 pl-1 pr-1">
                                        <a class="btn btn-primary p-0"
                                           style="width: 3rem; height: 3rem; font-size: 1.5rem">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </div>
                                    <div class="col-auto d-flex align-items-center p-0 pl-1 pr-1">
                                        <a class="btn btn-danger p-0"
                                           style="width: 3rem; height: 3rem; font-size: 1.5rem">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
                <h3 class="text-muted text-center mt-3">
                    Student can upload 3 files at maximum. Each cannot exceed 10MB and must be WORD document
                    (.docx) or image (.png, .jpeg, .gif)
                </h3>
            @else
                <h3 class="text-muted text-center mt-3">
                    You haven't upload any file
                </h3>
            @endif
            <div class="col-12 row m-0 p-0 mb-4">
                <div class="col card">
                    <div class="card-body row">
                        <div class="col-auto d-flex align-items-center">
                            <div class="icon icon-shape bg-danger text-white rounded-circle shadow">
                                <i class="fas fa-file-word"></i>
                            </div>
                        </div>
                        <div class="col">
                            <h5 class="card-title text-uppercase text-muted mb-0">SIZE: 5mb</h5>
                            <span class="h2 font-weight-bold mb-0">File article 1</span>
                            <br>
                            <small class="text-muted">Uploaded at</small>
                        </div>
                        <div class="col-auto row m-0 p-0">
                            <div class="col-auto d-flex align-items-center p-0 pl-1 pr-1">
                                <button class="btn btn-primary p-0"
                                        style="width: 3rem; height: 3rem; font-size: 1.5rem">
                                    <i class="fas fa-download"></i>
                                </button>
                            </div>
                            <div class="col-auto d-flex align-items-center p-0 pl-1 pr-1">
                                <button class="btn btn-danger p-0" style="width: 3rem; height: 3rem; font-size: 1.5rem">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 row m-0 p-0 mb-4">
                <div class="col card">
                    <div class="card-body row">
                        <div class="col-auto d-flex align-items-center">
                            <div class="icon icon-shape bg-danger text-white rounded-circle shadow">
                                <i class="fas fa-file-word"></i>
                            </div>
                        </div>
                        <div class="col">
                            <h5 class="card-title text-uppercase text-muted mb-0">SIZE: 5mb</h5>
                            <span class="h2 font-weight-bold mb-0">File article 1</span>
                            <br>
                            <small class="text-muted">Uploaded at</small>
                        </div>
                        <div class="col-auto row m-0 p-0">
                            <div class="col-auto d-flex align-items-center p-0 pl-1 pr-1">
                                <button class="btn btn-primary p-0"
                                        style="width: 3rem; height: 3rem; font-size: 1.5rem">
                                    <i class="fas fa-download"></i>
                                </button>
                            </div>
                            <div class="col-auto d-flex align-items-center p-0 pl-1 pr-1">
                                <button class="btn btn-danger p-0" style="width: 3rem; height: 3rem; font-size: 1.5rem">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 row m-0 p-0 mb-4">
                <div class="col card">
                    <div class="card-body row">
                        <div class="col-auto d-flex align-items-center">
                            <div class="icon icon-shape bg-danger text-white rounded-circle shadow">
                                <i class="fas fa-file-word"></i>
                            </div>
                        </div>
                        <div class="col">
                            <h5 class="card-title text-uppercase text-muted mb-0">SIZE: 5mb</h5>
                            <span class="h2 font-weight-bold mb-0">File article 1</span>
                            <br>
                            <small class="text-muted">Uploaded at</small>
                        </div>
                        <div class="col-auto row m-0 p-0">
                            <div class="col-auto d-flex align-items-center p-0 pl-1 pr-1">
                                <button class="btn btn-primary p-0"
                                        style="width: 3rem; height: 3rem; font-size: 1.5rem">
                                    <i class="fas fa-download"></i>
                                </button>
                            </div>
                            <div class="col-auto d-flex align-items-center p-0 pl-1 pr-1">
                                <button class="btn btn-danger p-0" style="width: 3rem; height: 3rem; font-size: 1.5rem">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 m-0 p-0 pl-4">
            <h2 class="col-12 row m-0 p-0 mb-4">
                <div class="h2 col p-0 m-0">
                    Article Preview
                </div>
                <div class="col-auto d-flex align-item-center p-0">
                    <button class="btn btn-default btn-icon">
                        <i class="fas fa-edit"></i>
                        Edit
                    </button>
                </div>
                <h3 class="text-center">
                    @if ($article && $article->title)
                        {{$article->title}}
                    @else
                        <span class="text-muted">Empty title</span>
                    @endif
                </h3>
                <br>
                <div class="col-12 p-0 rounded card-lift--hover" style="max-height: 200px; overflow: hidden;">
                    <img src="https://i.ytimg.com/vi/YxC0qXPaOq0/maxresdefault.jpg" alt="attachment image"
                         class="img-fluid img-center rounded cursor"
                         onclick="previewFullScreen(this)">
                </div>
                <small class="float-right text-muted">
                    Click the image to view in image preview mode.
                </small>
                <br>
                <p class="text-justify">
                    @if ($article && $article->description)
                        {{nl2br($article->description)}}
                    @else
                        <span class="text-muted">Empty description</span>
                    @endif
                </p>
            </h2>
        </div>
    </div>
@endsection
@section('modal')
    <div class="modal fade" id="articleModal" tabindex="-1" role="dialog" aria-labelledby="articleModal"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <form method="post"
                  action="{{route('student.faculty.article_post', [$facultySemester->faculty, $facultySemester->semester_id])}}"
                  enctype="multipart/form-data" class="modal-content">
                {{csrf_field()}}
                <div class="modal-header">
                    <h2 class="modal-title" id="exampleModalLabel">Upload Article</h2>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h4>Upload Files</h4>
                    <div class="form-group">
                        <label class="btn btn-block btn-dribbble">
                            Upload file
                            <input type="file" multiple onchange="listenChangesWord(event.target)" hidden
                                   name="wordDocument"
                                   id="wordDocument" class="form-control">
                        </label>
                    </div>
                    <div class="text-danger mt-1 mb-3" id="errorWord">
                    </div>
                    <div id="previewSection">
                    </div>
                    <hr>
                    <div class="custom-control custom-control-alternative custom-checkbox mb-3">
                        <input class="custom-control-input" id="terms" type="checkbox">
                        <label class="custom-control-label" for="terms">
                            I agree with the Terms and Conditions.
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" disabled id="submittedButton">Upload</button>
                </div>
            </form>
        </div>
    </div>
@endsection
@push("custom-js")
    <script>
        let articleModal = $('#articleModal');
        let errorSection = $('#errorWord');
        let submitButton = $('#submittedButton');
        let previewSection = $('#previewSection');
        function uploadWords() {
            @if($article)
            if (+{{count($article->article_file)}} > 2) {
                return;
            }
            articleModal.modal('show');
            @endif
            articleModal.modal('show');
        }
        function generateElement(className, type = "div") {
            let ele = document.createElement(type);
            ele.className = className;
            return ele;
        }
        function displayError(errorMessage, displaySection = errorSection) {
            displaySection.html(errorMessage);
        }
        function renderPreviewFiles(fileName, fileSize, mimeType = "{{FILE_MIMES[1]}}") {
            let cardContainer = generateElement("card mt-2");
            let cardBody = generateElement("card-body row");
            let cardFiles = generateElement("col");
            let cardIcon = generateElement("col-auto");
            let h5Name = generateElement("card-title text-uppercase text-muted mb-0", "h5");
            if (fileSize < 0.1) fileSize = (fileSize * 1024).toFixed(2) + "KB";
            else fileSize = fileSize.toFixed(2) + "MB";
            h5Name.innerText = "SIZE: " + fileSize;
            let spanName = generateElement("h2 font-weight-bold mb-0", "span");
            spanName.innerText = fileName;
            cardFiles.appendChild(h5Name);
            cardFiles.appendChild(spanName);
            let iconContainer;
            let icon;
            switch (mimeType) {
                case "{{FILE_MIMES[1]}}":
                    iconContainer = generateElement("icon icon-shape bg-primary text-white rounded-circle shadow");
                    icon = generateElement("fas fa-file-word", "i");
                    break;
                case "{{FILE_MIMES[4]}}":
                case "{{FILE_MIMES[5]}}":
                case "{{FILE_MIMES[6]}}":
                case "{{FILE_MIMES[7]}}":
                    iconContainer = generateElement("icon icon-shape bg-primary text-white rounded-circle shadow");
                    icon = generateElement("fas fa-file-image", "i");
                    break;
                default:
                    iconContainer = generateElement("icon icon-shape bg-danger text-white rounded-circle shadow");
                    icon = generateElement("fas fa-file", "i");
                    displayError("There are a file that the format is not supported. Word files and Image files (PNG, JPEG, GIF) only.")
            }
            iconContainer.appendChild(icon);
            cardIcon.appendChild(iconContainer);
            cardBody.appendChild(cardFiles);
            cardBody.appendChild(cardIcon);
            cardContainer.appendChild(cardBody);
            return cardContainer;
        }
        function listenChangesWord(target) {
            previewSection.html(null);
            if (target.files.length > 3) {
                displayError("You can only upload maximum 3 files");
                submitButton.attr("disabled", true);
            }
            Array.from(target.files).forEach(file => {
                let size = +file.size / (1024 * 1024); // to mb
                document.getElementById("previewSection").appendChild(
                    renderPreviewFiles(file.name, size, file.type)
                );
            })
        }
        function previewFullScreen(target) {
            console.log(target);
            console.dir(target);
        }
    </script>
@endpush
