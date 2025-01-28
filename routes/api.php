<?php

use App\Http\Controllers\BotUserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::any('/webhhok', [BotUserController::class,'webhook']);