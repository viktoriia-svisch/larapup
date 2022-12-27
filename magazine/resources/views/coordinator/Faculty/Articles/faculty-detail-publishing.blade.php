@extends("layout.Coordinator.coordinator-layout")
@section('title', 'Faculty '.$facultySemester->faculty->name.' - Publishing')
@push("custom-css")
@endpush
@section('breadcrumb')
    <div class="container">
        {{ Breadcrumbs::render('dashboard.faculty.detail.newPublish', route('coordinator.dashboard'),
        route('coordinator.faculty'), route('coordinator.faculty.dashboard', [$facultySemester->faculty_id, $facultySemester->semester_id]),
        route('coordinator.faculty.article.publish', [$facultySemester->faculty_id, $facultySemester->semester_id, $article->id])) }}
    </div>
@endsection
@section('coordinator-content')
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
    </div>
@endsection
@push("custom-js")
@endpush
