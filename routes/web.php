<?php

use App\Http\Controllers\GenerateFormController;
use App\Http\Controllers\LogoutController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('generate/logouts', [LogoutController::class, 'destroy'])
    ->name('generate.logouts');


Route::get('generate/interview', [GenerateFormController::class, 'generateFormInterview'])
    ->name('generate.interview');

Route::get('generate/signon', [GenerateFormController::class, 'generateFormSignon'])
    ->name('generate.signon');

Route::get('generate/signoff', [GenerateFormController::class, 'generateFormSignoff'])
    ->name('generate.signoff');

Route::get('generate/promosi', [GenerateFormController::class, 'generateFormPromosi'])
    ->name('generate.promosi');
