@extends("layout.Admin.admin-layout")
@section('title', 'Create Faculty')
@push("custom-css")
@endpush
@section('breadcrumb')
    <div class="container">
        {{ Breadcrumbs::render('dashboard.faculty', route('admin.dashboard'), route('admin.faculty')) }}
    </div>
@endsection
@section("admin-content")
<div class="container row col-12" style="margin-bottom: 10vw">
    <div class="row col-md-8 m-auto">
        <div class="col-sm-6">
            <h4 style="text-align: center">List student this faculty</h4>
            <input style="margin-top: 1vw" class="form-control" type="text" placeholder="search">
            <hr>
            <div class="col-xl-12" style="background-color: lavender; height: 3vw; border-radius: 8px; margin-top: 2vw">
                <img  class="img-thumbnail col-xl-2" style="width: 53px"
                      src="https://i.pinimg.com/564x/a0/19/c0/a019c0dd116bd5a4a8627460c770ed62.jpg">
                <label class="col-xl-4">Hello</label>
                <button class=" btn-primary col-xl-5">Delete</button>
            </div>
            <div class="col-xl-12" style="background-color: lavender; height: 3vw; border-radius: 8px; margin-top: 2vw">
                <img  class="img-thumbnail col-xl-2" style="width: 53px"
                      src="https://i.pinimg.com/564x/a0/19/c0/a019c0dd116bd5a4a8627460c770ed62.jpg">
                <label class="col-xl-4">LMAO</label>
                <button class=" btn-primary col-xl-5">Delete</button>
            </div>
            <div class="col-xl-12" style="background-color: lavender; height: 3vw; border-radius: 8px; margin-top: 2vw">
                <img  class="img-thumbnail col-xl-2" style="width: 53px"
                      src="https://i.pinimg.com/564x/a0/19/c0/a019c0dd116bd5a4a8627460c770ed62.jpg">
                <label class="col-xl-4">SUPA</label>
                <button class=" btn-primary col-xl-5">Delete</button>
            </div>
        </div>
        <div class="col-sm-6">
            <h4 style="text-align: center">List student</h4>
            <input style="margin-top: 1vw" class="form-control" type="text" placeholder="search">
            <hr>
            <div class="col-xl-12" style="background-color: lavender; height: 3vw; border-radius: 8px; margin-top: 2vw">
                <img  class="img-thumbnail col-xl-2" style="width: 53px"
                      src="https://i.pinimg.com/564x/a0/19/c0/a019c0dd116bd5a4a8627460c770ed62.jpg">
                <label class="col-xl-4">Hello</label>
                <button class=" btn-primary col-xl-5">Add Student</button>
            </div>
            <div class="col-xl-12" style="background-color: lavender; height: 3vw; border-radius: 8px; margin-top: 2vw">
                <img  class="img-thumbnail col-xl-2" style="width: 53px"
                      src="https://i.pinimg.com/564x/a0/19/c0/a019c0dd116bd5a4a8627460c770ed62.jpg">
                <label class="col-xl-4">LMAO</label>
                <button class=" btn-primary col-xl-5">Add Student</button>
            </div>
            <div class="col-xl-12" style="background-color: lavender; height: 3vw; border-radius: 8px; margin-top: 2vw">
                <img  class="img-thumbnail col-xl-2" style="width: 53px"
                      src="https://i.pinimg.com/564x/a0/19/c0/a019c0dd116bd5a4a8627460c770ed62.jpg">
                <label class="col-xl-4">Triết</label>
                <button class=" btn-primary col-xl-5">Add Student</button>
            </div>
            <div class="col-xl-12" style="background-color: lavender; height: 3vw; border-radius: 8px; margin-top: 2vw">
                <img  class="img-thumbnail col-xl-2" style="width: 53px"
                      src="https://i.pinimg.com/564x/a0/19/c0/a019c0dd116bd5a4a8627460c770ed62.jpg">
                <label class="col-xl-4">Minh</label>
                <button class=" btn-primary col-xl-5">Add Student</button>
            </div>
            <div class="col-xl-12" style="background-color: lavender; height: 3vw; border-radius: 8px; margin-top: 2vw">
                <img  class="img-thumbnail col-xl-2" style="width: 53px"
                      src="https://i.pinimg.com/564x/a0/19/c0/a019c0dd116bd5a4a8627460c770ed62.jpg">
                <label class="col-xl-4">Hiếu</label>
                <button class=" btn-primary col-xl-5">Add Student</button>
            </div>
        </div>
    </div>
</div>
@endsection
@push("custom-js")
@endpush
