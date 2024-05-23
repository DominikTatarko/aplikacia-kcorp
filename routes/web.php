<?php

use App\Http\Controllers\CatchAllController;
use Illuminate\Support\Facades\Route;

Route::any('{gateway}/{any}',[CatchAllController::class,'handleRequest'])->where('any','.*');
Route::any('{gateway}',[CatchAllController::class,'handleRequest']);
