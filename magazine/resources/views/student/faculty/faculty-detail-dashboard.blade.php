@extends("student.faculty.faculty-detail")
@section('title', 'Faculty '.$facultySemester->faculty->name.' - Dashboard')
@push("custom-css")
    <style>
        .time-section{
            position: absolute;
            top: calc(25px + 1rem);
            left: 0;
            -webkit-transform: translate(-50%, -50%);
            -moz-transform: translate(-50%, -50%);
            -ms-transform: translate(-50%, -50%);
            -o-transform: translate(-50%, -50%);
            transform: translate(-50%, -50%);
        }
        .message{
            display: none;
            position: absolute;
            left: 0;
            top: 50%;
            transform: translate(calc(-100% - 5px), -50%);
        }
        .time-section:hover .message{
            display: inline-block;
        }
    </style>
@endpush
@section('faculty-detail')
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
        <div class="col-12">
            <form class="col-12 row m-0 p-0">
                <div class="col-auto">
                    <img alt=""
                         style="width: 50px; height: 50px; object-fit: cover; object-position: center; overflow: hidden;"
                         class="img-fluid rounded-circle">
                </div>
                <div class="col">
                    <textarea title="Comment section" class="form-control form-control-alternative" rows="3"
                              resize="none" placeholder="Write comment here"></textarea>
                    <br>
                    <label for="attachment_input">Attachment image</label>
                    <div class="form-control">
                        <input type="file" name="attachment" id="attachment_input">
                    </div>
                </div>
                <div class="col-12">
                    <br>
                    <button class="btn btn-primary float-right">Comment</button>
                </div>
            </form>
        </div>
        <br>
        <section class="col-12">
            <h1 class="mb-0 pb-0">Discussion</h1>
            <br>
            <small class="text-muted">Time</small>
            <div class="col-12 row m-0 p-0 pl-3 pt-3 border-left position-relative" style="margin-left: 0.8rem !important">
                <div class="time-section">
                    <div class="dot-container">
                        <i class="fas fa-circle"></i>
                        <div class="message text-muted badge badge-primary">22/22/2222 44:44:44</div>
                    </div>
                </div>
                <div class="col-auto">
                    <img alt=""
                         style="width: 50px; height: 50px; object-fit: cover; object-position: center; overflow: hidden;"
                         class="img-fluid rounded-circle">
                </div>
                <div class="col card p-0">
                    <div class="card-body p-3">
                        <p class="text-primary font-weight-bold">123 (Coordinator)</p>
                        content Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusantium aperiam architecto
                        atque earum enim fuga, modi optio ut vel voluptatum!
                    </div>
                </div>
            </div>
            <div class="col-12 row m-0 p-0 pl-3 pt-3 border-left position-relative" style="margin-left: 0.8rem !important">
                <div class="time-section">
                    <div class="dot-container">
                        <i class="fas fa-circle"></i>
                        <div class="message text-muted badge badge-primary">22/22/2222 44:44:44</div>
                    </div>
                </div>
                <div class="col-auto">
                    <img alt=""
                         style="width: 50px; height: 50px; object-fit: cover; object-position: center; overflow: hidden;"
                         class="img-fluid rounded-circle">
                </div>
                <div class="col card p-0 pb-3">
                    <div class="card-body p-3">
                        <p class="text-primary font-weight-bold">123 (Coordinator)</p>
                        content Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusantium aperiam architecto
                        atque earum enim fuga, modi optio ut vel voluptatum!
                    </div>
                    <div class="col-12">
                        <img src="https://i.ytimg.com/vi/YxC0qXPaOq0/maxresdefault.jpg" alt="attachment image" class="img-fluid img-center rounded">
                    </div>
                </div>
            </div>
            <div class="col-12 row m-0 p-0 pl-3 pt-3 border-left position-relative" style="margin-left: 0.8rem !important">
                <div class="time-section">
                    <div class="dot-container">
                        <i class="fas fa-circle"></i>
                        <div class="message text-muted badge badge-primary">22/22/2222 44:44:44</div>
                    </div>
                </div>
                <div class="col-auto">
                    <img alt=""
                         style="width: 50px; height: 50px; object-fit: cover; object-position: center; overflow: hidden;"
                         class="img-fluid rounded-circle">
                </div>
                <div class="col card p-0">
                    <div class="card-body p-3">
                        <p class="text-primary font-weight-bold">123 (Coordinator)</p>
                        content Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusantium aperiam architecto
                        atque earum enim fuga, modi optio ut vel voluptatum!
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
@push("custom-js")
@endpush
