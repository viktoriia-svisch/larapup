@extends("student.faculty.faculty-detail")
@section('title', 'Faculty '.$facultySemester->faculty->name.' - Article')
@push("custom-css")
    <style>
        .modal-dialog{
            width: 95vw;
            max-width: 650px;
        }
    </style>
@endpush
@section('faculty-detail')
    <h1>Article Submission</h1>
    <div class="col-12 m-0 p-0 mt-2 mb-2">
        @if ($article)
            <div class="col-12">
                <button class="btn btn-secondary btn-block">Edit</button>
            </div>
        @else
            <div class="col-12">
                <button class="btn btn-default btn-block" onclick="modalOpen(false)">Upload</button>
            </div>
        @endif
    </div>
    <div class="col-12 row p-0 m-0">
        @if ($article)
            @if ($article->title)
                <h3 class="text-center">{{$article->title}}</h3>
            @else
                <span class="text-muted text-center">Not set title</span>
            @endif
            @if ($article->cover)
                <h3 class="text-center">{{$article->cover}}</h3>
            @else
                <span class="text-muted text-center">Not set title</span>
            @endif
            @if ($article->description)
                <h3 class="text-center">{{$article->description}}</h3>
            @else
                <span class="text-muted text-center">Not set description</span>
            @endif
        @else
            <h3 class="text-muted text-center">Haven's submitted any</h3>
        @endif
    </div>
@endsection
@section('modal')
    <div class="modal fade" id="articleModal" tabindex="-1" role="dialog" aria-labelledby="articleModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <form method="post" action="{{route('student.faculty.article_post', [$facultySemester->faculty, $facultySemester->semester_id])}}" enctype="multipart/form-data" class="modal-content">
                {{csrf_field()}}
                <div class="modal-header">
                    <h2 class="modal-title" id="exampleModalLabel">Upload Article</h2>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h4>Preview Section</h4>
                    <small class="text-muted">Can be empty</small>
                    <hr class="mt-3 mb-3">
                    <div class="form-group">
                        <input type="text" name="title" placeholder="Preview article title" class="form-control form-control-alternative" />
                    </div>
                    <div class="form-group">
                        <textarea name="description" class="form-control form-control-alternative" rows="3" maxlength="250" placeholder="Preview description"></textarea>
                        <small class="text-muted float-right">Character(s) left: <span id="countDescription">250</span></small>
                    </div>
                    <hr>
                    <h4>Preview Cover</h4>
                    <div class="col-12 row m-0 p-0">
                        <div class="col form-group">
                            <label class="btn btn-block btn-secondary">
                                Upload Cover
                                <input type="file" hidden name="cover" id="cover">
                            </label>
                        </div>
                        <div class="col-auto form-group">
                            <button class="btn btn-danger">&times;</button>
                        </div>
                    </div>
                    <div class="col-12">
                        <img src="https://i.ytimg.com/vi/YxC0qXPaOq0/maxresdefault.jpg" alt="attachment image" class="img-fluid img-center rounded">
                    </div>
                    <hr class="mt-1 mb-3">
                    <h4>Full article word file (max 3 files, 10mb each)</h4>
                    <div class="form-group">
                        <label class="btn btn-block btn-dribbble">
                            Upload file
                            <input type="file" onchange="listenChangesWord(event.target)" hidden name="wordDocument" id="wordDocument" class="form-control">
                        </label>
                    </div>
                    <div class="text-danger mt-1 mb-3" id="errorWord">
                    </div>
                    <div id="previewSection">
                        <div class="card mt-2">
                            <div class="card-body row">
                                <div class="col">
                                    <h5 class="card-title text-uppercase text-muted mb-0">SIZE: 5mb</h5>
                                    <span class="h2 font-weight-bold mb-0">File article 1</span>
                                </div>
                                <div class="col-auto">
                                    <div class="icon icon-shape bg-danger text-white rounded-circle shadow">
                                        <i class="fas fa-file-word"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <small class="col-12">at 22/22/2222 22:22:22</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
@endsection
@push("custom-js")
    <script>
        let articleModal = $('#articleModal');
        let errorSection = $('#errorWord');
        function modalOpen(isError = false) {
            if (isError) {}
            articleModal.modal('show');
        }
        function generateElement(className, type = "div") {
            let ele = document.createElement(type);
            ele.className = className;
        }
        function displayError(errorMessage, displaySection = errorSection) {
            displaySection.html(errorMessage);
        }
        function renderPreviewFiles(fileName, fileSize) {
            let cardContainer = generateElement("card mt-2");
            let cardBody = generateElement("card-body row");
            let cardFiles = generateElement("col");
            let cardIcon = generateElement("col-auto");
            let h5Name = generateElement("card-title text-uppercase text-muted mb-0", "h5");
            h5Name.innerText = "SIZE: " + fileSize + "MB";
            let spanName = generateElement("h2 font-weight-bold mb-0", "span");
            spanName.innerText = fileName;
            cardFiles.appendChild(h5Name);
            cardFiles.appendChild(spanName);
            let iconContainer = generateElement("icon icon-shape bg-danger text-white rounded-circle shadow");
            let icon = generateElement("fas fa-file-word", "i");
            iconContainer.appendChild(icon);
            cardIcon.appendChild(iconContainer);
            cardBody.appendChild(cardFiles);
            cardBody.appendChild(cardIcon);
            cardContainer.appendChild(cardBody);
            return cardContainer;
        }
        function listenChangesWord(target) {
            console.log(event);
        }
    </script>
@endpush
