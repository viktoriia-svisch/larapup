@if (Session::has('action_response') || sizeof($errors->all()) > 0)
    @if (Session::get('action_response')['status_ok'])
        <div class="col-12 m-0 p-0">
            <div class="card bg-success text-white">
                <div class="card-body" style="padding: 1rem;">
                    {{Session::get('action_response')['status_message']}}
                </div>
            </div>
        </div>
    @else
        @if ($errors->first())
            <div class="col-12 m-0 p-0">
                <div class="card bg-danger text-white">
                    <div class="card-body" style="padding: 1rem;">
                        {{$errors->first()}}
                    </div>
                </div>
            </div>
        @else
            <div class="col-12 m-0 p-0">
                <div class="card bg-danger text-white">
                    <div class="card-body" style="padding: 1rem;">
                        {{Session::get('action_response')['status_message']}}
                    </div>
                </div>
            </div>
        @endif
    @endif
    <br>
@endif
