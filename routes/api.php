<?php

use App\Http\Controllers\KubernetesController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileUploadController;

// routes/api.php

//Route::get('/nodes', [KubernetesController::class, 'getNodes']);
//Route::get('/pods', [KubernetesController::class, 'getPods']);
//Route::get('/services', [KubernetesController::class, 'getServices']);
Route::get('/clusters', [KubernetesController::class, 'listClusters']);

Route::get('/{config}/nodes', [KubernetesController::class, 'getNodes']);
Route::get('/{config}/pods', [KubernetesController::class, 'getPods']);
Route::get('/{config}/services', [KubernetesController::class, 'getServices']);
Route::get('/{config}/namespaces', [KubernetesController::class, 'getNamespaces']);
Route::get('/{config}/endpoints', [KubernetesController::class, 'getEndpoints']);
Route::get('/{config}/secrets', [KubernetesController::class, 'getSecrets']);

Route::get('/{config}/configmaps', [KubernetesController::class, 'getConfigMaps']);
Route::get('/{config}/persistentvolumes', [KubernetesController::class, 'getPersistentVolumes']);
Route::get('/{config}/persistentvolumeclaims', [KubernetesController::class, 'getPersistentVolumeClaims']);
Route::get('/{config}/events', [KubernetesController::class, 'getEvents']);
Route::get('/{config}/serviceaccounts', [KubernetesController::class, 'getServiceAccounts']);
Route::get('/{config}/replicationcontrollers', [KubernetesController::class, 'getReplicationControllers']);

Route::get('/{config}/deployments', [KubernetesController::class, 'getDeployments']);
Route::get('/{config}/replicasets', [KubernetesController::class, 'getReplicaSets']);
Route::get('/{config}/daemonsets', [KubernetesController::class, 'getDaemonSets']);
Route::get('/{config}/statefulsets', [KubernetesController::class, 'getStatefulSets']);

Route::get('/{config}/jobs', [KubernetesController::class, 'getJobs']);
Route::get('/{config}/cronjobs', [KubernetesController::class, 'getCronJobs']);

Route::get('/{config}/ingresses', [KubernetesController::class, 'getIngresses']);
Route::get('/{config}/networkpolicies', [KubernetesController::class, 'getNetworkPolicies']);


