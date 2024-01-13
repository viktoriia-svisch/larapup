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
]);
define('ARTICLE_NEWS', [
    'NEW' => 0,
    'OLD' => 1
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
define("ACCOUNT_DEACTIVATED", 0);
define("ACCOUNT_ACTIVATED", 1);
define('COORDINATOR_STATUS', [
    'ACTIVE' => 1,
    'DEACTIVATE' => 0,
]);
define('GUEST_STATUS', [
    'ACTIVE' => 1,
    'DEACTIVATE' => 0,
]);
define('FILE_MIMES', [
    0 => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    1 => 'image/gif',
    2 => 'image/jpg',
    3 => 'image/jpeg',
    4 => 'image/png',
    5 => 'application/zip,application/octet-stream,application/x-zip-compressed,multipart/x-zip',
]);
define('FILE_EXT', [
    0 => 'docx',
    1 => 'gif',
    2 => 'jpg',
    3 => 'jpeg',
    4 => 'png',
    5 => 'zip'
]);
define('FILE_EXT_INDEX', [
    'docx'  => 0,
    'gif'   => 1,
    'jpg'   => 2,
    'jpeg'  => 3,
    'png'   => 4,
]);
define('FILE_MAXSIZE', '10485760'); 
define('PER_PAGE', 20);
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
