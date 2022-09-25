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
define('COORDINATOR_STATUS', [
    'ACTIVE' => 1,
    'DEACTIVATE' => 0,
]);
define('FILE_MIMES', [
    0 => 'application/pdf',
    1 => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    2 => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    3 => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    4 => 'image/gif',
    5 => 'image/jpg',
    6 => 'image/jpeg',
    7 => 'image/png',
    8 => 'application/zip,application/octet-stream,application/x-zip-compressed,multipart/x-zip',
    9 => 'application/lzh, application/x-lzh, application/x-lha, application/x-compress, application/x-compressed, application/x-lzh-archive, zz-application/zz-winassoc-lzh, application/maclha, application/octet-stream',
    10 => '*',
]);
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
