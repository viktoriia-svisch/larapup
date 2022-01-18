<?php
define('STUDENT_GUARD', 'student');
define('COORDINATOR_GUARD', 'coordinator');
define('ADMIN_GUARD', 'admin');
define('GUEST_GUARD', 'guest');
define('GENDER', [
    'MALE' => 1,
    'FEMALE' => 2
]);
define('ARTICLE_STATUS', [
    'PENDING' => 0,
    'PUBLISHED' => 1,
    'DECLINED' => 2,
    'FINISHED' => 3
]);
define('COORDINATOR_LEVEL', [
    'NORMAL' => 0,
    'MASTER' => 1
]);
define('STUDENT_STATUS', [
    'ONGOING' => 1,
    'FINISHED' => 2,
    'LEFT' => 3,
    'STANDBY' => 0
]);
define('COORDINATOR_STATUS', [
    'ACTIVE' => 1,
    'DEACTIVATE' => 0,
]);
define('PER_PAGE', 1);
define('MESSAGE', 'message');
define('SEARCH', 'search');
define('EMAIL', 'email');
define('ALPHANUM', 'alphanum');
define('DESC', 'desc');
define('ASC', 'asc');
define('TABLE', 'table');
define('CREATED_AT', 'created_at');
define('UPDATED_AT', 'updated_at');
define('DELETED_AT', 'deleted_at');
define('UTC', 'UTC');
define('UNDELETE_FLAG', 0);
define('NOT_LOGIN', 0);
define('IS_LOGIN', 1);
define('TIME_OUT_MINUTE', 60);
define('ADMIN_ROLE_ADMINISTRATOR', 0);
define('ADMIN_ROLE_USER', 1);
define('DATE_FORMAT', 'Y-m-d');
define('DATE_TIME_FORMAT', 'Y-m-d H:i:s');
