@extends("layout.Coordinator.coordinator-layout")
@section('title', 'Search Faculty')
@push("custom-css")
@endpush
@section('breadcrumb')
    <div class="container">
        {{ Breadcrumbs::render('dashboard', route('coordinator.coordinator')) }}
    </div>
@endsection
@section("coordinator-content")
    <div class="container-fluid">
        <div class="container">
            <div class="row p-3">
                <div class="card col-12 col-sm-5 col-md-4">
                    <div class="card-body">
                        <h1 class="card-title">Active Semester</h1>
                        <p class="card-text">
                            @if ($activeData !== null)
                                {{\Illuminate\Support\Str::limit($activeData->semester->name, 34)}}
                            @else
                                <span class="text-muted">
                                    Currently not in any
                                </span>
                            @endif
                        </p>
                        <span class="text-muted">
                            End date:
                            @if ($activeData !== null)
                                {{\App\Helpers\DateTimeHelper::formatDate($activeData->semester->end_date)}}
                            @else
                                <span class="text-muted">
                                    N/A
                                </span>
                            @endif
                        </span>
                    </div>
                </div>
                <div class="card col-12 col-sm-7 col-md-8">
                    <div class="card-body">
                        <h1 class="card-title">Current Faculty</h1>
                        <div class="card-text row m-0 p-0">
                            @if ($activeData)
                                <div class="col pl-0">
                                    <h2 class="col-12 p-0 mb-0 font-weight-bold">
                                        {{\Illuminate\Support\Str::limit($activeData->faculty->name, 24)}}
                                    </h2>
                                    <hr class="m-0 mb-2">
                                    <div class="col-12 pl-0">
                                        <span class="text-muted">Next due date: </span>
                                        <span class="text-warning">
                                            @if (
                                            \App\Helpers\DateTimeHelper::isNowPassedDate($activeData->first_deadline)
                                            && !\App\Helpers\DateTimeHelper::isNowPassedDate($activeData->second_deadline)
                                            )
                                                {{\App\Helpers\DateTimeHelper::formatDateTime($activeData->second_deadline)}}
                                            @elseif (!\App\Helpers\DateTimeHelper::isNowPassedDate($activeData->first_deadline))
                                                {{\App\Helpers\DateTimeHelper::formatDateTime($activeData->first_deadline)}}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <a href="{{route('coordinator.faculty.detail', [$activeData->faculty->id, $activeData->semester->id])}}"
                                       class="btn btn-info text-white">
                                        <span>Detail</span>
                                        <span class="badge badge-dark text-white">4</span>
                                    </a>
                                    <a href="{{route('coordinator.faculty')}}"
                                       class="btn btn-info text-white">
                                        <span>Searching</span>
                                        <span class="badge badge-dark text-white"></span>
                                    </a>
                                </div>
                            @else
                                <p class="col-12 p-0 mb-0 text-muted">
                                    Currently not in any
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <hr>
        </div>
        <div class="container">
        </div>
    </div>
@endsection
@push("custom-js")
    <script>
    </script>
@endpush
