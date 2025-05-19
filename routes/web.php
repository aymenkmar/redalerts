<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Landing page
Route::get('/', \App\Livewire\LandingPage::class);

// Authentication routes
Route::get('/login', \App\Livewire\LoginPage::class)->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware('auth')->group(function () {
    // Main Dashboard (similar to frontend)
    Route::get('/main-dashboard', \App\Livewire\MainDashboard::class)->name('main-dashboard');

    // Admin Dashboard (original Laravel dashboard)
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');

    // Kubernetes Dashboard
    Route::get('/dashboard-kubernetes', \App\Livewire\KubernetesDashboard::class)->name('dashboard-kubernetes');

    // Kubernetes Nodes
    Route::get('/kubernetes/nodes', \App\Livewire\Kubernetes\NodeList::class)->name('kubernetes.nodes');

    // Kubernetes Namespaces
    Route::get('/kubernetes/namespaces', \App\Livewire\Kubernetes\NamespaceList::class)->name('kubernetes.namespaces');

    // Kubernetes Events
    Route::get('/kubernetes/events', \App\Livewire\Kubernetes\EventList::class)->name('kubernetes.events');

    // Kubernetes Access Control
    Route::get('/kubernetes/serviceaccounts', \App\Livewire\Kubernetes\AccessControl\ServiceAccountList::class)->name('kubernetes.serviceaccounts');
    Route::get('/kubernetes/clusterroles', \App\Livewire\Kubernetes\AccessControl\ClusterRoleList::class)->name('kubernetes.clusterroles');
    Route::get('/kubernetes/roles', \App\Livewire\Kubernetes\AccessControl\RoleList::class)->name('kubernetes.roles');
    Route::get('/kubernetes/clusterrolebindings', \App\Livewire\Kubernetes\AccessControl\ClusterRoleBindingList::class)->name('kubernetes.clusterrolebindings');
    Route::get('/kubernetes/rolebindings', \App\Livewire\Kubernetes\AccessControl\RoleBindingList::class)->name('kubernetes.rolebindings');

    // Kubernetes Custom Resources
    Route::get('/kubernetes/definitions', \App\Livewire\Kubernetes\CustomResources\DefinitionList::class)->name('kubernetes.definitions');

    // ACME Resources
    Route::get('/kubernetes/challenges', \App\Livewire\Kubernetes\CustomResources\ACME\ChallengeList::class)->name('kubernetes.challenges');
    Route::get('/kubernetes/orders', \App\Livewire\Kubernetes\CustomResources\ACME\OrderList::class)->name('kubernetes.orders');

    // Cert Manager Resources
    Route::get('/kubernetes/certificates', \App\Livewire\Kubernetes\CustomResources\CertManager\CertificateList::class)->name('kubernetes.certificates');
    Route::get('/kubernetes/certificaterequests', \App\Livewire\Kubernetes\CustomResources\CertManager\CertificateRequestList::class)->name('kubernetes.certificaterequests');
    Route::get('/kubernetes/issuers', \App\Livewire\Kubernetes\CustomResources\CertManager\IssuerList::class)->name('kubernetes.issuers');
    Route::get('/kubernetes/clusterissuers', \App\Livewire\Kubernetes\CustomResources\CertManager\ClusterIssuerList::class)->name('kubernetes.clusterissuers');

    // Kubernetes Workloads
    Route::prefix('kubernetes/workloads')->name('kubernetes.workloads.')->group(function () {
        Route::get('/pods', \App\Livewire\Kubernetes\Workloads\PodList::class)->name('pods');
        Route::get('/deployments', \App\Livewire\Kubernetes\Workloads\DeploymentList::class)->name('deployments');
        Route::get('/daemonsets', \App\Livewire\Kubernetes\Workloads\DaemonSetList::class)->name('daemonsets');
        Route::get('/statefulsets', \App\Livewire\Kubernetes\Workloads\StatefulSetList::class)->name('statefulsets');
        Route::get('/replicasets', \App\Livewire\Kubernetes\Workloads\ReplicaSetList::class)->name('replicasets');
        Route::get('/replicationcontrollers', \App\Livewire\Kubernetes\Workloads\ReplicationControllerList::class)->name('replicationcontrollers');
        Route::get('/jobs', \App\Livewire\Kubernetes\Workloads\JobList::class)->name('jobs');
        Route::get('/cronjobs', \App\Livewire\Kubernetes\Workloads\CronJobList::class)->name('cronjobs');
    });

    // Kubernetes Config
    Route::prefix('kubernetes/config')->name('kubernetes.config.')->group(function () {
        Route::get('/configmaps', \App\Livewire\Kubernetes\Config\ConfigMapList::class)->name('configmaps');
        Route::get('/secrets', \App\Livewire\Kubernetes\Config\SecretList::class)->name('secrets');
        Route::get('/resourcequotas', \App\Livewire\Kubernetes\Config\ResourceQuotaList::class)->name('resourcequotas');
        Route::get('/limitranges', \App\Livewire\Kubernetes\Config\LimitRangeList::class)->name('limitranges');
        Route::get('/horizontalpodautoscalers', \App\Livewire\Kubernetes\Config\HorizontalPodAutoscalerList::class)->name('horizontalpodautoscalers');
    });

    // Kubernetes Network
    Route::prefix('kubernetes/network')->name('kubernetes.network.')->group(function () {
        Route::get('/services', \App\Livewire\Kubernetes\Network\ServiceList::class)->name('services');
        Route::get('/endpoints', \App\Livewire\Kubernetes\Network\EndpointList::class)->name('endpoints');
        Route::get('/ingresses', \App\Livewire\Kubernetes\Network\IngressList::class)->name('ingresses');
        Route::get('/ingressclasses', \App\Livewire\Kubernetes\Network\IngressClassList::class)->name('ingressclasses');
        Route::get('/networkpolicies', \App\Livewire\Kubernetes\Network\NetworkPolicyList::class)->name('networkpolicies');
    });

    // Kubernetes Storage
    Route::prefix('kubernetes/storage')->name('kubernetes.storage.')->group(function () {
        Route::get('/persistentvolumeclaims', \App\Livewire\Kubernetes\Storage\PersistentVolumeClaimList::class)->name('persistentvolumeclaims');
        Route::get('/persistentvolumes', \App\Livewire\Kubernetes\Storage\PersistentVolumeList::class)->name('persistentvolumes');
        Route::get('/storageclasses', \App\Livewire\Kubernetes\Storage\StorageClassList::class)->name('storageclasses');
    });

    Route::get('/auth-test', function() {
        return view('api-test');
    });

    // API Management page (placeholder for now)
    Route::get('/api-management', function() {
        return view('dashboard'); // Using dashboard view as placeholder
    });
});
