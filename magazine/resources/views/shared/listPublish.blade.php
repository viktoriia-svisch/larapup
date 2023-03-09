@extends("layout.Shared.shared-layout")
@section("shared-content")
    <section class="w-100 d-flex flex-column position-fixed top-0 left-0 right-0"
             style="padding-top: 0.5rem; z-index: 1030; background-color: #f8f9fe;">
        <form class="form-searching col-12 m-0 p-0 m-auto">
            <div class="form-group">
                <div class="input-group input-group-alternative">
                    <div class="input-group-prepend">
                    <span class="input-group-text">
                        <i class="fas fa-search"></i>
                    </span>
                    </div>
                    <input class="form-control form-control-alternative" name="search"
                           placeholder="Search title or student" type="text">
                </div>
                <small class="text-muted m-auto">Press enter to start searching</small>
            </div>
        </form>
    </section>
    <section class="list-publish" style="padding-top: 100px">
        <div class="container">
            <div class="card mb-5">
                <div class="row no-gutters">
                    <a href="{{route('shared.publish', [1])}}" class="col-md-8 col-sm-12">
                        <img
                            src="https://www.tapeciarnia.pl/tapety/normalne/255710_dziewczyna_wstazki_bukiet_manga_anime.jpg"
                            class="card-img img-fluid"
                            style="width: 100%; height: 100%; object-fit: cover; object-position: center" alt="...">
                    </a>
                    <div class="col-md-4 col-sm-12">
                        <div class="card-body">
                            <h1 class="card-title mb-2">Card title</h1>
                            <p class="card-text">
                                <small class="text-muted">Uploaded at 3 mins ago</small>
                                <br>
                                Some quick example text to build on the card title and make up the bulk of the card's
                                content.
                            </p>
                        </div>
                        <div class="card-footer">
                            <a href="{{route('shared.publish', [1])}}" class="btn btn-primary">Read now</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@section('title', 'Publishes')
@push("custom-css")
    <style>
        .form-searching {
            max-width: 450px;
        }
        @media only screen and (max-width: 580px) {
            .form-searching {
                max-width: none;
                padding-left: calc(40px + 1.5rem) !important;
                padding-right: 0.5rem !important;
            }
        }
    </style>
@endpush
@push("custom-js")
    <script>
    </script>
@endpush
