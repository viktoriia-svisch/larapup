@extends("layout.Coordinator.coordinator-layout")
@section('title', 'Faculty '.$facultySemester->faculty->name.' - Discussion')
@push("custom-css")
    <style>
        .time-section {
            position: absolute;
            top: calc(25px + 1rem);
            left: 0;
            -webkit-transform: translate(-50%, -50%);
            -moz-transform: translate(-50%, -50%);
            -ms-transform: translate(-50%, -50%);
            -o-transform: translate(-50%, -50%);
            transform: translate(-50%, -50%);
        }
        .message {
            display: none;
            position: absolute;
            left: 0;
            top: 50%;
            transform: translate(calc(-100% - 5px), -50%);
        }
        .time-section:hover .message {
            display: inline-block;
        }
    </style>
@endpush
@section('breadcrumb')
    <div class="container">
        {{
        Breadcrumbs::render('dashboard.faculty.detail.discussion', route('coordinator.dashboard'),
        route('coordinator.faculty'),
        route('coordinator.faculty.dashboard', [$facultySemester->faculty_id, $facultySemester->semester_id]),
        $article,
        route('coordinator.faculty.article.publish', [$facultySemester->faculty_id, $facultySemester->semester_id, $article->id]))
        }}
    </div>
@endsection
@section('coordinator-content')
    <div class="container">
        <div class="col-12">
            <form class="col-12 row m-0 p-0" method="post"
                  enctype="multipart/form-data"
                  action="{{route("student.faculty.comment_post", [$facultySemester->faculty_id, $facultySemester->semester_id])}}">
                {{csrf_field()}}
                <div class="col-auto">
                    <img alt=""
                         style="width: 50px; height: 50px; object-fit: cover; object-position: center; overflow: hidden;"
                         class="img-fluid rounded-circle">
                </div>
                <div class="col">
                    <textarea title="Comment section" class="form-control form-control-alternative" rows="3"
                              resize="none" placeholder="Write comment here" name="content"></textarea>
                    <br>
                    <label for="attachment">Attachment image</label>
                    <div class="form-control">
                        <input type="file" name="attachment" id="attachment">
                    </div>
                </div>
                <div class="col-12">
                    <br>
                    <button class="btn btn-primary float-right" type="submit">Comment</button>
                </div>
            </form>
        </div>
        <br>
        <section class="col-12">
            <h1 class="mb-0 pb-0">Discussion</h1>
            <br>
            <small class="text-muted">Time</small>
            @foreach($comments as $comment)
                <div class="col-12 row m-0 p-0 pl-3 pt-3 border-left position-relative"
                     style="margin-left: 0.8rem !important">
                    <div class="time-section">
                        <div class="dot-container">
                            <i class="fas fa-circle"></i>
                            <div
                                class="message text-muted badge badge-primary">{{\App\Helpers\DateTimeHelper::formatDateTime($comment->created_at)}}
                            </div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <img alt=""
                             style="width: 50px; height: 50px; object-fit: cover; object-position: center; overflow: hidden;"
                             class="img-fluid rounded-circle">
                    </div>
                    <div class="col card p-0">
                        <div class="card-body p-3">
                            <p class="text-primary font-weight-bold">
                                @if ($comment->student)
                                    {{$comment->student->first_name . ' ' . $comment->student->last_name}}
                                @else
                                    {{$comment->coordinator->first_name . ' ' . $comment->coordinator->last_name}}
                                @endif
                            </p>
                            {{$comment->content}}
                            <div class="col-12 pl-0">
                                <br>
                                @if ($comment->image_path)
                                    <small class="font-weight-bold">Attachment file</small>
                                    <br>
                                    <a class="btn-link"
                                       @if ($comment->student_id)
                                       href="{{route("student.faculty.comment_attachmentDownload",
                                   [$facultySemester->faculty_id, $facultySemester->semester_id, $comment->id, STUDENT_GUARD])}}"
                                       @else
                                       href="{{route("student.faculty.comment_attachmentDownload",
                                   [$facultySemester->faculty_id, $facultySemester->semester_id, $comment->id, COORDINATOR_GUARD])}}"
                                        @endif>
                                        {{$comment->image_path}}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <br>
            @endforeach
        </section>
    </div>
@endsection
@push("custom-js")
@endpush
