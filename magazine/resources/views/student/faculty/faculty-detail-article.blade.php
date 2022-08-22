@extends("student.faculty.faculty-detail")
@section('title', 'Faculty '.$facultySemester->faculty->name.' - Article')
@push("custom-css")
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
                <button class="btn btn-default btn-block">Upload</button>
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
@push("custom-js")
@endpush
