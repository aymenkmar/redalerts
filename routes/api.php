<?php

use App\Http\Controllers\KubernetesController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\AuthController;

// Public routes - no authentication required
Route::match(['get', 'post'], '/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function () {
        return request()->user();
    });

    // Kubernetes routes
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
Route::get('/{config}/limitranges', [KubernetesController::class, 'getLimitRanges']);

Route::get('/{config}/namespaces/{namespace}/resourcequotas', [KubernetesController::class, 'getResourceQuotas']);

Route::get('/{config}/deployments', [KubernetesController::class, 'getDeployments']);
Route::get('/{config}/replicasets', [KubernetesController::class, 'getReplicaSets']);
Route::get('/{config}/daemonsets', [KubernetesController::class, 'getDaemonSets']);
Route::get('/{config}/statefulsets', [KubernetesController::class, 'getStatefulSets']);

Route::get('/{config}/jobs', [KubernetesController::class, 'getJobs']);
Route::get('/{config}/cronjobs', [KubernetesController::class, 'getCronJobs']);

Route::get('/{config}/ingresses', [KubernetesController::class, 'getIngresses']);
Route::get('/{config}/networkpolicies', [KubernetesController::class, 'getNetworkPolicies']);

// New API resources
Route::get('/{config}/applications', [KubernetesController::class, 'getApplications']);
Route::get('/{config}/horizontalpodautoscalers', [KubernetesController::class, 'getHorizontalPodAutoscalers']);
Route::get('/{config}/poddisruptionbudgets', [KubernetesController::class, 'getPodDisruptionBudgets']);
Route::get('/{config}/priorityclasses', [KubernetesController::class, 'getPriorityClasses']);
Route::get('/{config}/runtimeclasses', [KubernetesController::class, 'getRuntimeClasses']);
Route::get('/{config}/leases', [KubernetesController::class, 'getLeases']);
Route::get('/{config}/mutatingwebhookconfigurations', [KubernetesController::class, 'getMutatingWebhookConfigurations']);
Route::get('/{config}/validatingwebhookconfigurations', [KubernetesController::class, 'getValidatingWebhookConfigurations']);
Route::get('/{config}/ingressclasses', [KubernetesController::class, 'getIngressClasses']);
Route::get('/{config}/storageclasses', [KubernetesController::class, 'getStorageClasses']);
Route::get('/{config}/clusterroles', [KubernetesController::class, 'getClusterRoles']);
Route::get('/{config}/roles', [KubernetesController::class, 'getRoles']);
Route::get('/{config}/clusterrolebindings', [KubernetesController::class, 'getClusterRoleBindings']);
Route::get('/{config}/rolebindings', [KubernetesController::class, 'getRoleBindings']);

// Port forwarding (special case)
//Route::post('/{config}/namespaces/{namespace}/pods/{pod}/portforward', [KubernetesController::class, 'createPortForward']);
});
