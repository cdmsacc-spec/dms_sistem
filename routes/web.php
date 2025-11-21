<?php

use App\Http\Controllers\GenerateTemplateController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('generate/interview', [GenerateTemplateController::class, 'generateFormInterview'])
    ->name('generate.interview');


Route::get('generate/signon', [GenerateTemplateController::class, 'generateFormSignon'])
    ->name('generate.signon');

Route::get('generate/signoff', [GenerateTemplateController::class, 'generateFormSignoff'])
    ->name('generate.signoff');

Route::get('generate/promosi', [GenerateTemplateController::class, 'generateFormPromosi'])
    ->name('generate.promosi');
