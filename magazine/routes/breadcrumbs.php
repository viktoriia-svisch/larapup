<?php
use App\Models\Publish;
use DaveJamesMiller\Breadcrumbs\Facades\Breadcrumbs;
use Illuminate\Support\Str;
Breadcrumbs::for('dashboard', function ($trail, $routeActive) {
    $trail->push('Dashboard', $routeActive);
});
Breadcrumbs::for('setting', function ($trail, $routeActive) {
    $trail->push('Setting', $routeActive);
});
Breadcrumbs::for('dashboard.student', function ($trail, $parentRoute, $routeActive) {
    $trail->parent('dashboard', $parentRoute);
    $trail->push("Student", $routeActive);
});
Breadcrumbs::for('dashboard.student.create', function ($trail, $parentRoute, $parent2Route, $routeActive) {
    $trail->parent('dashboard.student', $parentRoute, $parent2Route);
    $trail->push('Create new', $routeActive);
});
Breadcrumbs::for('dashboard.student.info', function ($trail, $parentRoute, $parent2Route, $student, $routeActive) {
    $trail->parent('dashboard.student', $parentRoute, $parent2Route);
    $trail->push($student->first_name . ' ' . $student->last_name, $routeActive);
});
Breadcrumbs::for('dashboard.coordinator', function ($trail, $parentRoute, $routeActive) {
    $trail->parent('dashboard', $parentRoute);
    $trail->push("Coordinator", $routeActive);
});
Breadcrumbs::for('dashboard.coordinator.create', function ($trail, $parentRoute, $parent2Route, $routeActive) {
    $trail->parent('dashboard.coordinator', $parentRoute, $parent2Route);
    $trail->push("Create new", $routeActive);
});
Breadcrumbs::for('dashboard.coordinator.info', function ($trail, $parentRoute, $parent2Route, $coordinator, $routeActive) {
    $trail->parent('dashboard.coordinator', $parentRoute, $parent2Route);
    $trail->push($coordinator->first_name . ' ' . $coordinator->last_name, $routeActive);
});
Breadcrumbs::for('dashboard.semester', function ($trail, $parentRoute, $routeActive) {
    $trail->parent('dashboard', $parentRoute);
    $trail->push("Semester", $routeActive);
});
Breadcrumbs::for('dashboard.semester.create', function ($trail, $parentRoute, $parent2Route, $routeActive) {
    $trail->parent('dashboard.semester', $parentRoute, $parent2Route);
    $trail->push('Create new', $routeActive);
});
Breadcrumbs::for('dashboard.semester.info', function ($trail, $parentRoute, $parent2Route, $semester, $routeActive) {
    $trail->parent('dashboard.semester', $parentRoute, $parent2Route);
    $trail->push($semester->name, $routeActive);
});
Breadcrumbs::for('dashboard.faculty', function ($trail, $parentRoute, $routeActive) {
    $trail->parent('dashboard', $parentRoute);
    $trail->push("Faculty", $routeActive);
});
Breadcrumbs::for('dashboard.faculty.detail', function ($trail, $parentRoute, $parentRoute2, $routeActive) {
    $trail->parent('dashboard.faculty', $parentRoute, $parentRoute2);
    $trail->push("Detail", $routeActive);
});
Breadcrumbs::for('dashboard.faculty.detail.newPublish', function ($trail, $parentRoute, $parentRoute2, $parentRoute3, $routeActive) {
    $trail->parent('dashboard.faculty.detail', $parentRoute, $parentRoute2, $parentRoute3);
    $trail->push("New Publish", $routeActive);
});
Breadcrumbs::for('dashboard.faculty.detail.discussion', function ($trail, $parentRoute, $parentRoute2, $parentRoute3, $article, $routeActive) {
    $trail->parent('dashboard.faculty.detail', $parentRoute, $parentRoute2, $parentRoute3);
    $trail->push("Discussion - " . $article->student->first_name . ' ' . $article->student->last_name, $routeActive);
});
Breadcrumbs::for('dashboard.faculty.create', function ($trail, $parentRoute, $parent2Route, $routeActive) {
    $trail->parent('dashboard.faculty', $parentRoute, $parent2Route);
    $trail->push("Create new", $routeActive);
});
Breadcrumbs::for('dashboard.profile', function ($trail, $parentRoute, $routeActive) {
    $trail->parent('dashboard', $parentRoute);
    $trail->push('Profile', $routeActive);
});
Breadcrumbs::for('publishes', function ($trail, $routeActive) {
    $trail->push('Publications', $routeActive);
});
Breadcrumbs::for('publishes.publication', function ($trail, $parentRoute, $routeActive, Publish $publish) {
    $trail->parent('publishes', $parentRoute);
    $trail->push(Str::limit($publish->title, 50), $routeActive);
});
