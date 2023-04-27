<?php
return array(
    'filename'  => '_ide_helper',
    'format'    => 'php',
    'meta_filename' => '.phpstorm.meta.php',
    'include_fluent' => false,
    'write_model_magic_where' => true,
    'write_eloquent_model_mixins' => false,
    'include_helpers' => false,
    'helper_files' => array(
        base_path().'/vendor/laravel/framework/src/Illuminate/Support/helpers.php',
    ),
    'model_locations' => array(
        'app',
    ),
    'extra' => array(
        'Eloquent' => array('Illuminate\Database\Eloquent\Builder', 'Illuminate\Database\Query\Builder'),
        'Session' => array('Illuminate\Session\Store'),
    ),
    'magic' => array(
        'Log' => array(
            'debug'     => 'Monolog\Logger::addDebug',
            'info'      => 'Monolog\Logger::addInfo',
            'notice'    => 'Monolog\Logger::addNotice',
            'warning'   => 'Monolog\Logger::addWarning',
            'error'     => 'Monolog\Logger::addError',
            'critical'  => 'Monolog\Logger::addCritical',
            'alert'     => 'Monolog\Logger::addAlert',
            'emergency' => 'Monolog\Logger::addEmergency',
        )
    ),
    'interfaces' => array(
    ),
    'custom_db_types' => array(
    ),
    'model_camel_case_properties' => false,
   'type_overrides' => array(
        'integer' => 'int',
        'boolean' => 'bool',
   ),
    'include_class_docblocks' => false,
);
