@extends("coordinator.faculty.faculty-detail")
@section('title', 'Faculty '.$facultySemester->faculty->name.' - Articles')
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
@section('faculty-detail')
    <div class="col-12">
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
        <br>
        <hr>
        @if (sizeof($articles) == 0)
            <h1 class="text-center text-muted">
                No article was found
            </h1>
        @endif
        @foreach($articles as $article)
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row m-0 p-0">
                        <div class="col-auto d-flex justify-content-center align-items-center">
                            <img src="{{$article->student->avatar_path ?? 'http://getdrawings.com/images/anime-girls-drawing-34.jpg'}}" class="img-center img-fluid rounded-circle"
                                 style="width: 70px; height: 70px; overflow: hidden;"
                                 alt="{{$article->student->first_name. " " . $article->student->last_name}}">
                        </div>
                        <div class="col row ml-0">
                            <div class="col">
                                <h2>{{$article->student->first_name. " " . $article->student->last_name}}</h2>
                                <hr class="mt-2 mb-0">
                                <div class="col-12 row m-0 p-0 pb-3">
                                    <div class="col p-0">
                                        Submitted at: {{\App\Helpers\DateTimeHelper::formatDateTime($article->created_at)}}
                                    </div>
                                    <div class="col p-0">
                                        Last Update:
                                        {{\App\Helpers\DateTimeHelper::formatDateTime($article->updated_at)}}
                                    </div>
                                </div>
                                <div class="col-12 row m-0 p-0">
                                    @foreach($article->article_file as $file)
                                        <div class="col-12 col-md-6 p-2">
                                            <a class="card text-black" href="{{route('coordinator.faculty.listArticle.download', [
                                            $facultySemester->faculty_id, $facultySemester->semester_id, $file->id])}}">
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
                                                    <div class="col overflow-hidden d-flex align-items-center">
                                                        <span class="font-weight-bold">
                                                            {{\Illuminate\Support\Str::limit($file->title, 15)}}
                                                        </span>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="col-auto d-flex flex-column justify-content-around p-0 m-0">
                                <h2 class="text-center">Grade</h2>
                                <h1 class="text-center m-0 @if($article->grade > 7) text-success @elseif ($article->grade > 4)
                                    text-warning @else text-danger @endif">
                                    {{$article->grade}}
                                </h1>
                                <div class="d-flex flex-column justify-content-around p-0 m-0">
                                    <a href="{{route('coordinator.faculty.article', [$facultySemester->faculty_id, $facultySemester->semester_id, $article->id])}}"
                                       class="btn btn-primary m-0 mb-1">
                                        Discuss
                                    </a>
                                    <a href="{{route("coordinator.faculty.article.publish", [$facultySemester->faculty_id, $facultySemester->semester_id, $article->id])}}"
                                       class="btn btn-success mt-1">
                                        Publish
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
@push("custom-js")
@endpush
