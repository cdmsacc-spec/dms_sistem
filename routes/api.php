<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CrewController;
use App\Http\Controllers\Api\DokumenController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('/user')->controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::post('/autologin', 'autologin');
});

Route::prefix('/dokumen')->controller(DokumenController::class)->group(function () {
    Route::post('/dashboard', 'dashboard');
    Route::post('/show', 'show');
    Route::post('/detail', 'detail');
});

Route::prefix('/crew')->controller(CrewController::class)->group(function () {
    Route::post('/dashboard', 'dashboard');
    Route::post('/show', 'show');
    Route::post('/detail', 'detail');
    Route::post('/history_kontrak', 'crewHistoryKontrak');
    Route::post('/history_sign_off', 'crewHistorySignOff');
});
