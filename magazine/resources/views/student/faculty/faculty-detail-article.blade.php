@extends("student.faculty.faculty-detail")
@section('title', 'Faculty '.$facultySemester->faculty->name.' - Article')
@push("custom-css")
    <style>
        .modal-dialog {
            width: 95vw;
            max-width: 550px;
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
    @include("layout.response.responseMessage")
    <div class="container-fluid row m-0 p-0">
        <div class="col-12 col-md-4 m-0 p-0 pr-4 border-right d-flex justify-content-center align-items-center">
            @if ($article && count($article->publish) > 0)
                <div class="col-12 p-0 m-0 mb-5 mt-2 d-flex flex-column justify-content-center align-items-center">
                    <span class="text-muted">Grated</span>
                    <h1>{{$article->grade}}</h1>
                    <br>
                    <a href="{{route('shared.publish', [$facultySemester->faculty_id, $facultySemester->semester->id, $article->publish[0]->id])}}"
                       class="btn btn-success btn-icon">
                        <i class="fas fa-thumbs-up"></i>
                        This Article was published
                    </a>
                    <small class="text-muted mt-3">
                        By: {{$article->publish[0]->coordinator->first_name .' '.$article->publish[0]->coordinator->last_name}}
                        <br>
                        at {{\App\Helpers\DateTimeHelper::formatDateTime($article->publish[0]->created_at)}}
                    </small>
                </div>
            @endif
        </div>
        <div class="col-12 col-md-8 m-0 p-0 pl-4">
            <div class="col-12 row m-0 p-0 mb-4">
                <div class="h2 col p-0 m-0">
                    File Submission
                </div>
                <div class="col-auto d-flex align-item-center p-0">
                    @if (!\App\Helpers\DateTimeHelper::isNowPassedDate($facultySemester->second_deadline))
                        <button class="btn btn-icon"
                                @if (($article && count($article->article_file) > 3) || ($article && $article->publish == null))
                                disabled @else onclick="uploadFilePopup()" @endif>
                            <i class="fas fa-upload"></i>
                            Upload new
                        </button>
                    @endif
                </div>
            </div>
            @if ($article)
                @foreach($article->article_file as $file)
                    <div class="col-12 row m-0 p-0 mb-4">
                        <div class="col card">
                            <div class="card-body row">
                                <div class="col-auto d-flex align-items-center">
                                    <div class="icon icon-shape bg-default text-white rounded-circle shadow">
                                        @if ($file->type == 0)
                                            <i class="fas fa-file-word"></i>
                                        @else
                                            <i class="fas fa-file-image"></i>
                                        @endif
                                    </div>
                                </div>
                                <div class="col">
                                    <span class="h2 font-weight-bold mb-0">
                                        {{\Illuminate\Support\Str::limit($file->title, 15, '...' .FILE_EXT[$file->type])}}
                                    </span>
                                    <br>
                                    <small class="text-muted">
                                        Modified at:
                                        {{\App\Helpers\DateTimeHelper::formatDateTime($file->updated_at ?? $file->created_at)}}
                                    </small>
                                </div>
                                <div class="col-auto row m-0 p-0 text-white">
                                    <div class="col-auto d-flex align-items-center p-0 pl-1 pr-1">
                                        <a href="{{route("student.faculty.articleFiles_download", [$facultySemester->faculty_id, $facultySemester->semester_id, $file->id])}}"
                                           class="btn btn-primary p-0 d-flex align-items-center justify-content-center"
                                           style="width: 3rem; height: 3rem; font-size: 1.5rem">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </div>
                                    <div class="col-auto d-flex align-items-center p-0 pl-1 pr-1">
                                        <button @if ($article->publish == null)
                                                onclick="confirmDeletePopup({{$file->id}})"
                                                @else disabled @endif class="btn btn-danger p-0"
                                                style="width: 3rem; height: 3rem; font-size: 1.5rem">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <h3 class="text-muted text-center mt-3">
                    You haven't upload any file
                </h3>
            @endif
            <hr>
            <h3 class="text-muted text-center mt-3">
                Student can upload 3 files at maximum. Each cannot exceed 10MB and must be WORD document
                (.docx) or image (.png, .jpeg, .gif)
            </h3>
        </div>
    </div>
@endsection
@section('modal')
    @if (($article && sizeof($article->article_file) >= 0 && sizeof($article->article_file) < 4 && sizeof($article->publish) == 0) || !$article)
        <div class="modal fade" id="articleModal" tabindex="-1" role="dialog" aria-labelledby="articleModal"
             aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <form method="post"
                      action="{{route('student.faculty.articleFiles_post', [$facultySemester->faculty, $facultySemester->semester_id])}}"
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
                                       name="wordDocument[]" id="wordDocument" class="form-control">
                            </label>
                            <input type="hidden" name="semester_id" value="{{$facultySemester->semester_id}}">
                            <input type="hidden" name="faculty_semester_id" value="{{$facultySemester->id}}">
                        </div>
                        <div class="text-danger mt-1 mb-3" id="errorWord"></div>
                        <div id="previewSection"></div>
                        <hr>
                        <div class="custom-control custom-control-alternative custom-checkbox mb-3">
                            <input class="custom-control-input" id="terms" onchange="listenCheckboxTerms(event)"
                                   type="checkbox">
                            <label class="custom-control-label" for="terms">
                                I agree with the Terms and Conditions.
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" disabled id="submittedFileBtn">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
    @if ($article && count($article->article_file) > 0  && $article->publish == null)
        <div class="modal fade" id="confirmDelete" tabindex="-1" role="dialog" aria-labelledby="confirmDelete"
             aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <form
                    action="{{route("student.faculty.articleFiles_delete", [$facultySemester->faculty_id, $facultySemester->semester_id])}}"
                    class="modal-content" id="deleteForm" method="POST">
                    @csrf
                    <h2 class="modal-header">
                        Are you sure you want to delete this file?
                    </h2>
                    <div class="modal-body">
                        <p>This action cannot be undone and will be permanent.</p>
                        <input type="hidden" name="survey_file_id" id="survey_file_id">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-danger" id="submittedButton">Confirm Delete</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection
@push("custom-js")
    <script>
        let articleModal = $('#articleModal');
        let errorSection = $('#errorWord');
        let submitFilesButton = $('#submittedFileBtn');
        let previewSection = $('#previewSection');
        let inputFiles = $('#wordDocument');
        let isValidFileSubmissionUpload = false;
        $(function () {
            articleModal.on('hidden.bs.modal', function () {
                resetFileInput();
            })
        });
        function uploadFilePopup() {
            console.log(articleModal);
            @if($article && sizeof($article->publish) > 0)
            flashMessage("This article is already published, any modification is not allowed!", true, 6000);
            @else
            @if(!(($article && count($article->article_file) > 2) || !$article))
            articleModal.modal('show');
            @endif
            @endif
        }
        function listenCheckboxTerms(event) {
            if (isValidFileSubmissionUpload && event.target.checked) {
                submitFilesButton.attr("disabled", false);
            } else {
                submitFilesButton.attr("disabled", true);
            }
        }
        function confirmDeletePopup(idSurveyFile) {
            $("#survey_file_id").val(idSurveyFile);
            $("#confirmDelete").modal('show');
        }
        /**
         * Shorthand function create element with class.
         */
        function generateElement(className, type = "div") {
            let ele = document.createElement(type);
            ele.className = className;
            return ele;
        }
        /**
         * Generate error message on specific section
         */
        function displayError(errorMessage, displaySection = errorSection) {
            displaySection.html(errorMessage);
        }
        /**
         * Generate preview element
         */
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
            if (fileName.length > 28) {
                fileName = fileName.substr(0, 25) + '...';
            }
            spanName.innerText = fileName;
            cardFiles.appendChild(h5Name);
            cardFiles.appendChild(spanName);
            let iconContainer;
            let icon;
            switch (mimeType) {
                case "{{FILE_MIMES[0]}}":
                    iconContainer = generateElement("icon icon-shape bg-primary text-white rounded-circle shadow");
                    icon = generateElement("fas fa-file-word", "i");
                    break;
                case "{{FILE_MIMES[1]}}":
                case "{{FILE_MIMES[2]}}":
                case "{{FILE_MIMES[3]}}":
                case "{{FILE_MIMES[4]}}":
                    iconContainer = generateElement("icon icon-shape bg-primary text-white rounded-circle shadow");
                    icon = generateElement("fas fa-file-image", "i");
                    break;
                default:
                    iconContainer = generateElement("icon icon-shape bg-danger text-white rounded-circle shadow");
                    icon = generateElement("fas fa-file", "i");
                    displayError("There are a file that the format is not supported. Word files and Image files (PNG, JPEG, GIF) only.");
            }
            iconContainer.appendChild(icon);
            cardIcon.appendChild(iconContainer);
            cardBody.appendChild(cardFiles);
            cardBody.appendChild(cardIcon);
            cardContainer.appendChild(cardBody);
            return cardContainer;
        }
        /**
         * Listen uploaded word files and generate preview elements
         * @param target
         */
        function listenChangesWord(target) {
            previewSection.html(null);
            errorSection.html(null);
            @if($article)
            if (target.files.length > (3 - +'{{count($article->article_file)}}')) {
                displayError("You can only upload maximum " + (3 - +'{{count($article->article_file)}}') + " files");
                submitFilesButton.attr("disabled", true);
                isValidFileSubmissionUpload = false;
            } else {
                isValidFileSubmissionUpload = true;
                errorSection.html(null);
            }
            @else
            if (target.files.length > 3) {
                isValidFileSubmissionUpload = false;
                submitFilesButton.attr("disabled", true);
                displayError("You can only upload maximum 3 files per article. Accepted file extensions: DOCX, PNG, JPEG, GIF");
            } else {
                isValidFileSubmissionUpload = true;
                errorSection.html(null);
            }
            @endif
            Array.from(target.files).forEach(file => {
                let size = +file.size / (1024 * 1024); // to mb
                document.getElementById("previewSection").appendChild(
                    renderPreviewFiles(file.name, size, file.type)
                );
            });
        }
        /**
         * Reset file upload input state
         */
        function resetFileInput(fileJQUERY = inputFiles) {
            previewSection.html(null);
            errorSection.html(null);
            isValidFileSubmissionUpload = false;
            fileJQUERY.replaceWith(fileJQUERY.val('').clone(true));
        }
    </script>
@endpush
