<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/run-migrations', function () {
    try {
        Artisan::call('migrate', ['--force' => true]);
        return "✅ تمت إضافة الجداول بنجاح فـ TiDB! <br><br> Output: <pre>" . Artisan::output() . "</pre>";
    } catch (\Exception $e) {
        return "❌ وقع مشكل فالميݣراسيون: " . $e->getMessage();
    }
});
