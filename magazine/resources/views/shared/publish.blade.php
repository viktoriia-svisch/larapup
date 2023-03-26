@extends("layout.Shared.shared-layout")
@section('shared-breadcrumb')
    {{ Breadcrumbs::render('publishes.publication',
    route('shared.listPublishes', [$viewFaculty->id, $semester_id]),
    route("shared.publish", [$viewFaculty->id, $semester_id, $publication->id]),
    $publication)}}
@endsection
@section("shared-content")
    <section class="list-publish" style="padding-top: 100px">
        <h1 class="text-black text-center font-weight-light pl-3 pr-3" style="font-size: 2.5rem">
            {{nl2br(htmlentities($publication->title))}}
        </h1>
        <hr class="w-50 text-center">
        @if (sizeof($publication->publish_image) > 0)
            <div class="container rounded overflow-hidden mb-4">
                <img
                    src="{{asset('storage/'. \App\Helpers\StorageHelper::getPublishFilePath($publication->article->faculty_semester->id, $publication->id, $publication->publish_image[0]->image_path, true))}}"
                    class="card-img img-fluid"
                    style="width: 100%; height: 100%; object-fit: cover; object-position: center"
                    alt="...">
            </div>
        @endif
        <p class="container">
            {!! nl2br(htmlentities($publication->content)) !!}
        </p>
        <hr class="w-50 text-center">
        @if (sizeof($publication->publish_image) > 0)
            <h1 class="text-black text-center font-weight-light" style="font-size: 1.5rem">
                Image within this post
            </h1>
            <div class="container text-center">
                @foreach($publication->publish_image as $image)
                    <img
                        src="{{asset('storage/'. \App\Helpers\StorageHelper::getPublishFilePath($publication->article->faculty_semester->id, $publication->id, $image->image_path, true))}}"
                        class="img-fluid rounded overflow-hidden mb-4"
                        style="min-width: 200px;"
                        alt="...">
                    <br>
                @endforeach
            </div>
        @endif
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
