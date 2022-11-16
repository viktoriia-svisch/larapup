'use strict';
var packageName = 'gromo:jquery.scrollbar'; 
var where = 'client'; 
Package.describe({
    name: packageName,
    version: '0.2.11',
    summary: 'Cross-browser CSS customizable scrollbar with advanced features.',
    git: 'git@github.com:gromo/jquery.scrollbar.git'
});
Package.onUse(function (api) {
    api.versionsFrom(['METEOR@0.9.0', 'METEOR@1.0']);
    api.use('jquery', where);
    api.addFiles(['jquery.scrollbar.js', 'jquery.scrollbar.css'], where);
});
Package.onTest(function (api) {
    api.use([packageName, 'sanjo:jasmine'], where);
    api.use(['webapp','tinytest'], where);
    api.addFiles('meteor/tests.js', where); 
});
