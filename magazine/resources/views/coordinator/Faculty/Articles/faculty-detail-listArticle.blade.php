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
        @foreach($articles as $article)
            <div class="card">
                {{$article->student_id}}
                <a href="{{route("coordinator.faculty.article.publish", [$facultySemester->faculty_id, $facultySemester->semester_id, $article->id])}}"
                   class="btn">Publish this</a>
            </div>
        @endforeach
    </div>
@endsection
@push("custom-js")
@endpush
