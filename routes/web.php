<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/clear-cache', function () {
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('config:clear');
    Artisan::call('view:clear');
    return "Cache cleared successfully!";
});



Route::get('/storage-link', function () {
    Artisan::call('storage:link');
    return 'Storage link created successfully!';
});

Route::get('/test-class', function () {
    if (class_exists(\Milon\Barcode\Facades\DNS1DFacade::class)) {
        return "Class Found!";
    } else {
        return "Class Not Found!";
    }
});

Route::get('/composer-update', function () {
    $output = shell_exec('composer update 2>&1');
    return "<pre>$output</pre>";
});
