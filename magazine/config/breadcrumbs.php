  <?php
return [
    'view' => 'breadcrumbs::bootstrap4',
    'files' => base_path('routes/breadcrumbs.php'),
    'unnamed-route-exception' => true,
    'missing-route-bound-breadcrumb-exception' => true,
    'invalid-named-breadcrumb-exception' => true,
    'manager-class' => DaveJamesMiller\Breadcrumbs\BreadcrumbsManager::class,
    'generator-class' => DaveJamesMiller\Breadcrumbs\BreadcrumbsGenerator::class,
];
