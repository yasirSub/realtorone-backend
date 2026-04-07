<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

/** Public legal pages (Play Store / App Store privacy policy URL, in-app links). */
Route::view('/privacy', 'legal.privacy');
Route::view('/terms', 'legal.terms');

Route::get('/delete-account', function () {
    return view('delete_account');
});

Route::post('/delete-account', function (Request $request) {
    // Basic validation
    $request->validate([
        'email' => 'required|email'
    ]);

    // For compliance, acknowledge receipt of request
    return view('delete_account', ['status' => 'success']);
});
