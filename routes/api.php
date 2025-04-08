<?php

use App\Http\Controllers\KubernetesController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileUploadController;

// routes/api.php

//Route::get('/nodes', [KubernetesController::class, 'getNodes']);
//Route::get('/pods', [KubernetesController::class, 'getPods']);
//Route::get('/services', [KubernetesController::class, 'getServices']);

Route::get('/{config}/nodes', [KubernetesController::class, 'getNodes']);
Route::get('/{config}/pods', [KubernetesController::class, 'getPods']);
Route::get('/{config}/services', [KubernetesController::class, 'getServices']);

