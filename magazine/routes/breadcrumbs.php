<?php
use DaveJamesMiller\Breadcrumbs\Facades\Breadcrumbs;
Breadcrumbs::for('dashboard', function ($trail, $routeActive) {
    $trail->push('Dashboard', $routeActive);
});
Breadcrumbs::for('student', function ($trail, $parentRoute, $routeActive) {
    $trail->parent('dashboard', $parentRoute);
    $trail->push("Student", $routeActive);
});
Breadcrumbs::for('coordinator', function ($trail, $parentRoute, $routeActive) {
    $trail->parent('dashboard', $parentRoute);
    $trail->push("Coordinator", $routeActive);
});
Breadcrumbs::for('student_create', function ($trail, $parentRoute, $parent2Route, $routeActive) {
    $trail->parent('student', $parentRoute, $parent2Route);
    $trail->push('Create', $routeActive);
});
Breadcrumbs::for('student_info', function ($trail, $parentRoute, $parent2Route, $student, $routeActive) {
    $trail->parent('student', $parentRoute, $parent2Route);
    $trail->push($student->first_name .' '. $student->last_name, $routeActive);
});
Breadcrumbs::for('coordinator_info', function ($trail, $parentRoute, $parent2Route, $coordinator, $routeActive) {
    $trail->parent('coordinator', $parentRoute, $parent2Route);
    $trail->push($coordinator->first_name .' '. $coordinator->last_name, $routeActive);
});
Breadcrumbs::for('semester', function ($trail, $parentRoute, $routeActive) {
    $trail->parent('dashboard', $parentRoute);
    $trail->push("Semester", $routeActive);
});
Breadcrumbs::for('semester_info', function ($trail, $parentRoute, $parent2Route, $semester, $routeActive) {
    $trail->parent('semester', $parentRoute, $parent2Route);
    $trail->push($semester->name, $routeActive);
});
Breadcrumbs::for('semester_create', function ($trail, $parentRoute, $parent2Route, $routeActive) {
    $trail->parent('semester ', $parentRoute, $parent2Route);
    $trail->push('Create', $routeActive);
});
