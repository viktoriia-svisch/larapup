@extends("layout.Coordinator.coordinator-layout")
@section('title', 'Dashboard')
@push("custom-css")
@endpush
@section("coordinator-content")
    <div class="container row col-12">
        <div class="col-md-6 col-sm-12 m-auto" style="border-right: 1px solid; border-left: 1px solid;">
            <h1 style="text-align: center;">Articles</h1>
            <hr>
            <h3 class="text-primary p-0 pt-2"><span class="text-black-50">Title: </span> Name of Articles</h3>
            <small class="text-black-50">
                Written by: <span class="text-blue">Vu Duy Hoang</span>
                <span class="float-right">Created on: 20/10/2018 10:22:22</span>
            </small>
            <div class="col-12" style="margin-top: 2vw">
                <h4 >Introduction</h4>
                <p>hello Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ab accusantium at aut commodi consectetur dolor,
                    doloribus ducimus illum laborum minima nulla, obcaecati officia pariatur provident reiciendis sapiente sequi suscipit
                    temporibus veniam vero. Aliquid beatae blanditiis consectetur,
                    dicta eligendi esse facere fugiat id in molestiae mollitia nisi pariatur, placeat, rem similique?</p>
                <p><a href="">More Information, click here</a></p>
            </div>
            <hr>
            <h3 class="text-primary p-0 pt-2"><span class="text-black-50">Title: </span> Name of Articles</h3>
            <small class="text-black-50">
                Written by: <span class="text-blue">Vu Duy Hoang</span>
                <span class="float-right">Created on: 20/10/2018 10:22:22</span>
            </small>
            <div class="col-12" style="margin-top: 2vw">
                <h4 >Introduction</h4>
                <p>hello Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ab accusantium at aut commodi consectetur dolor,
                    doloribus ducimus illum laborum minima nulla, obcaecati officia pariatur provident reiciendis sapiente sequi suscipit
                    temporibus veniam vero. Aliquid beatae blanditiis consectetur,
                    dicta eligendi esse facere fugiat id in molestiae mollitia nisi pariatur, placeat, rem similique?</p>
                <p><a href="">More Information, click here</a></p>
            </div>
            <hr>
            <nav class="col-12" aria-label="Page navigation example">
                <ul class="pagination">
                    <li class="page-item">
                        <a class="page-link" href="#" aria-label="Previous">
                            <i class="fa fa-angle-left"></i>
                            <span class="sr-only">Previous</span>
                        </a>
                    </li>
                    <li class="page-item"><a class="page-link" href="#">1</a></li>
                    <li class="page-item active">
                        <a class="page-link" href="#">2</a>
                    </li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item">
                        <a class="page-link" href="#" aria-label="Next">
                            <i class="fa fa-angle-right"></i>
                            <span class="sr-only">Next</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
@endsection
@push("custom-js")
@endpush
