@if (\Illuminate\Support\Facades\Auth::guard(STUDENT_GUARD)->check())
    @extends("layout.Student.student-layout")@section("student-content")@endsection
@elseif (\Illuminate\Support\Facades\Auth::guard(COORDINATOR_GUARD)->check())
    @extends("layout.Coordinator.coordinator-layout")@section("coordinator-content")@endsection
@elseif (\Illuminate\Support\Facades\Auth::guard(ADMIN_GUARD)->check())
    @extends("layout.Admin.admin-layout")@section("admin-content")@endsection
@elseif (\Illuminate\Support\Facades\Auth::guard(GUEST_GUARD)->check())
    @extends("layout.Guest.guest-layout")@section("guest-content-content")@endsection
@endif
@section('title', 'Publish')
@push("custom-css")
@endpush
@push("custom-js")
@endpush
