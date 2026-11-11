<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MkiController;

Route::get('/', [MkiController::class, 'index'])->name('mki.dashboard');