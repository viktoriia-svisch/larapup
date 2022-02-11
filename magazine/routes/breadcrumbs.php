<?php
use DaveJamesMiller\Breadcrumbs\Facades\Breadcrumbs;
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
Breadcrumbs::for('dashboard.faculty.create', function ($trail, $parentRoute, $routeActive) {
    $trail->parent('dashboard.faculty', $parentRoute);
    $trail->push("Create new", $routeActive);
});
