<?php
$config->setRuntimeDir(\sys_get_temp_dir() . '/psysh_test/withconfig/temp');
return [
    'useReadline'       => true,
    'usePcntl'          => false,
    'requireSemicolons' => false,
    'useUnicode'        => true,
    'errorLoggingLevel' => E_ALL & ~E_NOTICE,
];
