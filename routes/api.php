<?php

use App\Http\Controllers\Api\TenderApiController;
use Illuminate\Support\Facades\Route;

Route::get('/tenders', [TenderApiController::class, 'acceptedTenders']);

