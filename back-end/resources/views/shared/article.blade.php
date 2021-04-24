@extends("layout.Student.student-layout")
@section('title', 'Article')
@push("custom-css")
@endpush
@section("student-content")
    <div class="container-fluid row col-12">
        <div class="col-md-8 col-sm-12 height-fluid">
            <h1 class="text-primary p-0 pt-2"><span class="text-black-50">Title: </span> Name of title</h1>
            <small class="text-black-50">
                Written by: <span class="text-blue">Do Hoang Nam</span>
                <span class="float-right">Written on: 24/12/1212 22:22:22</span>
            </small>
            <hr>
            <div class="col-12">
                <img src="https://www.tapeciarnia.pl/tapety/normalne/255710_dziewczyna_wstazki_bukiet_manga_anime.jpg"
                     alt="article image" class="img-center" style="max-height: 350px; width: 100%; object-fit: cover; object-position: center;overflow: hidden;">
            </div>
            <p>
                Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ab accusantium at aut commodi consectetur
                dolor, doloribus ducimus illum laborum minima nulla, obcaecati officia pariatur provident reiciendis
                sapiente sequi suscipit temporibus veniam vero. Aliquid beatae blanditiis consectetur, dicta eligendi
                esse facere fugiat id in molestiae mollitia nisi pariatur, placeat, rem similique?
            </p>
            <br>
            <h3>File attachment</h3>
            <div class="row p-1">
                <a href="#" class="card col-12 col-md-5 m-1">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <h5 class="card-title text-uppercase text-muted mb-0">Uploaded: 22-02-2010 22:42:12</h5>
                                <span class="h2 font-weight-bold mb-0">File article 1</span>
                            </div>
                            <div class="col-auto">
                                <div class="icon icon-shape bg-danger text-white rounded-circle shadow">
                                    <i class="fas fa-file-word"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
                <a href="#" class="card col-12 col-md-5 m-1">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <h5 class="card-title text-uppercase text-muted mb-0">Uploaded: 22-02-2010 22:42:12</h5>
                                <span class="h2 font-weight-bold mb-0">File article 1</span>
                            </div>
                            <div class="col-auto">
                                <div class="icon icon-shape bg-danger text-white rounded-circle shadow">
                                    <i class="fas fa-file-word"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
                <a href="#" class="card col-12 col-md-5 m-1">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <h5 class="card-title text-uppercase text-muted mb-0">Uploaded: 22-02-2010 22:42:12</h5>
                                <span class="h2 font-weight-bold mb-0">File article 1</span>
                            </div>
                            <div class="col-auto">
                                <div class="icon icon-shape bg-danger text-white rounded-circle shadow">
                                    <i class="fas fa-file-word"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <hr>
        </div>
        <div class="col-md-4 col-sm-12 pb-5" style="border-left: 1px solid #b8b8b8;">
            <br>
            <div class="col-12">
                <small class="text-black-50">Published status:</small>
                <h2 class="text-success">Published
                    <small class="text-black-50">- at 22/12/2005 22:22:22</small>
                </h2>
            </div>
            <hr>
            <h2>Discussion</h2>
            <div class="col-12">
                <div class="text-primary pl-0 pb-2">Faculty teacher:</div>
                <div class="text-justify pl-3">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ab ad corporis, cumque delectus deserunt
                    distinctio eaque enim esse facilis fuga fugit harum hic id labore laborum libero magnam natus
                    pariatur qui quidem quis rerum saepe sapiente similique sint unde voluptates? Ipsam maxime
                    necessitatibus nihil odit possimus provident, rem soluta voluptate!
                </div>
                <br>
                <small class="text-black-50">Issued at: 22/02/2015 22:22:22</small>
            </div>
            <hr>
            <div class="col-12">
                <div class="text-success pl-0 pb-2">Do Hoang Nam:</div>
                <div class="text-justify pl-3">
                    Lorem ipsum dolor sit amet, consectetur adipisicing elit. Assumenda, voluptate.
                </div>
                <br>
                <small class="text-black-50">Issued at: 22/02/2015 22:22:22</small>
            </div>
            <hr>
            <div class="col-12">
                <div class="text-primary pl-0 pb-2">Faculty teacher:</div>
                <div class="text-justify pl-3">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ab ad corporis, cumque delectus deserunt
                    distinctio eaque enim esse facilis fuga fugit harum hic id labore laborum libero magnam natus
                    pariatur qui quidem quis rerum saepe sapiente similique sint unde voluptates? Ipsam maxime
                    necessitatibus nihil odit possimus provident, rem soluta voluptate!
                </div>
                <br>
                <small class="text-black-50">Issued at: 22/02/2015 22:22:22</small>
            </div>
            <hr>
            <div class="col-12">
                <div class="text-primary pl-0 pb-2">Faculty teacher:</div>
                <div class="text-justify pl-3">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ab ad corporis, cumque delectus deserunt
                    distinctio eaque enim esse facilis fuga fugit harum hic id labore laborum libero magnam natus
                    pariatur qui quidem quis rerum saepe sapiente similique sint unde voluptates? Ipsam maxime
                    necessitatibus nihil odit possimus provident, rem soluta voluptate!
                </div>
                <br>
                <small class="text-black-50">Issued at: 22/02/2015 22:22:22</small>
            </div>
            <hr>
            <form method="post" action="" class="col-12">
                <div class="form-group">
                    <label for="discussion">Comment on discussion</label>
                    <textarea class="form-control form-control-alternative" style="color: #161616; height: 8rem; resize: none;" placeholder="Write comment here" name="discussion"
                              id="discussion"></textarea>
                    <br>
                    <button class="btn btn-block btn-primary">Send</button>
                </div>
            </form>
        </div>
    </div>
@endsection
@push("custom-js")
@endpush
