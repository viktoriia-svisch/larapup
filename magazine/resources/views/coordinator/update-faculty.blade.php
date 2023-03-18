@extends("layout.Coordinator.coordinator-layout")
@section('title', 'Update Information Student')
@push("custom-css")
@endpush
@section("coordinator-content")
    <div class="container row col-md-12" style="margin-top: -2.2vw">
        <div class="row col-12 m-auto">
            <h1>Update</h1>
            <form >
                <label style="color: #0b1011">First Name</label>
                <div class="card bg-danger text-white rounded-0">
                     <div class="card-body p-1 rounded-0">
                         <label>Name of Faculty</label>
                         <input class="form-control" type="text" placeholder="name" name="name" id="" required>
                     </div>
                </div>
        </div>
    </div>
@endsection
@push("custom-js")
    <script>
        $(document).ready(function () {
            $('.datepicker').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                // endDate: "today",
                // endDate: "today",
            });
        })
    </script>
@endpush
