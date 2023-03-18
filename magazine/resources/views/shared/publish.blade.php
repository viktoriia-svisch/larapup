@extends("layout.Shared.shared-layout")
@section("shared-content")
    <section class="w-100 d-flex flex-column position-fixed top-0 left-0 right-0"
             style="padding-top: 0.5rem; z-index: 1030; background-color: #f8f9fe;">
        <div class="container-fluid">
            {{ Breadcrumbs::render('publishes.publication',
            route('shared.listPublishes', [$faculty->id, $semester_id]),
            route("shared.publish", [$faculty->id, $semester_id, $publication->id]),
            $publication)}}
        </div>
    </section>
    <section class="list-publish" style="padding-top: 100px">
        <div class="container">
            @if (sizeof($publishes) == 0)
                <h2 class="text-muted font-weight-thin text-center">
                    No article found :D
                </h2>
            @endif
            @foreach($publishes as $published)
                <div class="card mb-5">
                    <div class="row no-gutters">
                        @if (sizeof($published->publish_image) > 0)
                            <a href="{{route('shared.publish', [
                                $published->article->faculty_semester->faculty_id,
                                $published->article->faculty_semester->semester_id,
                                $published->id])}}" class="col-md-8 col-sm-12">
                                <img
                                    src="{{asset('storage/'. \App\Helpers\StorageHelper::getPublishFilePath($published->article->faculty_semester->id, $published->id, $published->publish_image[0]->image_path, true))}}"
                                    class="card-img img-fluid"
                                    style="width: 100%; height: 100%; max-height: 450px; object-fit: cover; object-position: center"
                                    alt="...">
                            </a>
                        @endif
                        <div class="col-md-4 col-sm-12">
                            <div class="card-body">
                                <h1 class="card-title mb-2">{{$published->title}}</h1>
                                <p class="card-text">
                                    <small class="text-muted">
                                        Uploaded at
                                        {{\App\Helpers\DateTimeHelper::formatDateTime($published->created_at)}}
                                        @if ($published->updated_at && $published->updated_at !== $published->created_at)
                                            <br>
                                            Last updated at
                                            {{\App\Helpers\DateTimeHelper::formatDateTime($published->updated_at)}}
                                        @endif
                                    </small>
                                    <br>
                                    {{\Illuminate\Support\Str::limit($published->content, 100)}}
                                </p>
                            </div>
                            <div class="card-footer">
                                <a href="{{route('shared.publish', [
                                $published->article->faculty_semester->faculty_id,
                                $published->article->faculty_semester->semester_id,
                                $published->id])}}" class="btn btn-primary">
                                    Read now
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endsection
@section('title', 'Publishes')
@push("custom-css")
    <style>
    </style>
@endpush
@push("custom-js")
    <script>
    </script>
@endpush
