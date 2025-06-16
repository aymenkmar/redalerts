<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SSOController;

// Landing page
Route::get('/', \App\Livewire\LandingPage::class);

// Authentication routes
Route::get('/login', \App\Livewire\LoginPage::class)->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// SSO routes
Route::get('/auth/azure', [SSOController::class, 'redirectToAzure'])->name('azure.login');
Route::get('/login/callback', [SSOController::class, 'handleAzureCallback'])->name('azure.callback');
Route::post('/auth/check-sso', [SSOController::class, 'canUseSSO'])->name('check.sso');



// Protected routes
Route::middleware('auth')->group(function () {
    // Main Dashboard (similar to frontend)
    Route::get('/main-dashboard', \App\Livewire\MainDashboard::class)->name('main-dashboard');

    // Admin Dashboard (original Laravel dashboard)
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');

    // User Profile
    Route::get('/profile', \App\Livewire\UserProfile::class)->name('profile');

    // Kubernetes Dashboard
    Route::get('/dashboard-kubernetes', \App\Livewire\KubernetesDashboard::class)->name('dashboard-kubernetes');

    // Kubernetes Cluster Selection
    Route::post('/kubernetes/select-cluster', [\App\Http\Controllers\KubernetesController::class, 'selectCluster'])->name('kubernetes.select-cluster');

    // Kubernetes Upload Modal
    Route::post('/kubernetes/upload-modal', [\App\Http\Controllers\KubernetesController::class, 'showUploadModal'])->name('kubernetes.upload-modal');
    Route::post('/kubernetes/close-modal', [\App\Http\Controllers\KubernetesController::class, 'closeUploadModal'])->name('kubernetes.close-modal');

    // Kubernetes Cluster Management
    Route::post('/kubernetes/cluster/edit/{id}', [\App\Http\Controllers\KubernetesController::class, 'editCluster'])->name('kubernetes.cluster.edit');
    Route::post('/kubernetes/cluster/upload/{id}', [\App\Http\Controllers\KubernetesController::class, 'replaceKubeconfig'])->name('kubernetes.cluster.upload');
    Route::delete('/kubernetes/cluster/{id}', [\App\Http\Controllers\KubernetesController::class, 'deleteCluster'])->name('kubernetes.cluster.delete');

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

    // Pod Shell Routes
    Route::prefix('kubernetes/shell')->group(function () {
        Route::post('/start', [\App\Http\Controllers\PodShellController::class, 'startShell'])->name('kubernetes.shell.start');
        Route::post('/execute/{sessionId}', [\App\Http\Controllers\PodShellController::class, 'executeCommand'])->name('kubernetes.shell.execute');
        Route::get('/output/{sessionId}', [\App\Http\Controllers\PodShellController::class, 'getOutput'])->name('kubernetes.shell.output');
        Route::post('/complete/{sessionId}', [\App\Http\Controllers\PodShellController::class, 'tabComplete'])->name('kubernetes.shell.complete');
        Route::delete('/terminate/{sessionId}', [\App\Http\Controllers\PodShellController::class, 'terminateShell'])->name('kubernetes.shell.terminate');
        Route::get('/sessions', [\App\Http\Controllers\PodShellController::class, 'listSessions'])->name('kubernetes.shell.sessions');
        Route::post('/cleanup', [\App\Http\Controllers\PodShellController::class, 'cleanup'])->name('kubernetes.shell.cleanup');
    });

    // Test route for shell functionality
    Route::get('/test-shell', function () {
        return view('test-shell');
    })->name('test-shell');

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

    // Debug route for testing cluster connection
    Route::get('/debug-cluster', function() {
        try {
            $clusters = [];
            $kubeconfigPath = env('KUBECONFIG_PATH', storage_path('app/kubeconfigs'));

            if (is_dir($kubeconfigPath)) {
                $files = scandir($kubeconfigPath);
                foreach ($files as $file) {
                    if ($file !== '.' && $file !== '..' && is_file($kubeconfigPath . '/' . $file)) {
                        $clusters[] = $file;
                    }
                }
            }

            $selectedCluster = session('selectedCluster') ?? session('selected_cluster');
            $testResults = [];

            if ($selectedCluster && in_array($selectedCluster, $clusters)) {
                try {
                    $service = new \App\Services\KubernetesService($kubeconfigPath . '/' . $selectedCluster);
                    $podsResponse = $service->getPods();
                    $testResults['pods'] = [
                        'success' => true,
                        'count' => count($podsResponse['items'] ?? []),
                        'first_pod' => isset($podsResponse['items'][0]) ? $podsResponse['items'][0]['metadata']['name'] : 'none'
                    ];
                } catch (\Exception $e) {
                    $testResults['pods'] = [
                        'success' => false,
                        'error' => $e->getMessage()
                    ];
                }
            }

            return response()->json([
                'available_clusters' => $clusters,
                'selected_cluster' => $selectedCluster,
                'session_keys' => [
                    'selectedCluster' => session('selectedCluster'),
                    'selected_cluster' => session('selected_cluster')
                ],
                'test_results' => $testResults
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    });

    // API Management page (placeholder for now)
    Route::get('/api-management', function() {
        return view('dashboard'); // Using dashboard view as placeholder
    });

    // Website Monitoring Routes
    Route::get('/website-monitoring', \App\Livewire\WebsiteMonitoring\WebsiteList::class)->name('website-monitoring.list');
    Route::get('/website-monitoring/add', \App\Livewire\WebsiteMonitoring\AddWebsite::class)->name('website-monitoring.add');
    Route::get('/website-monitoring/{website}/edit', \App\Livewire\WebsiteMonitoring\EditWebsite::class)->name('website-monitoring.edit');
    Route::get('/website-monitoring/{website}/history', \App\Livewire\WebsiteMonitoring\WebsiteHistory::class)->name('website-monitoring.history');

    // OVH Monitoring Routes
    Route::prefix('ovh-monitoring')->name('ovh-monitoring.')->group(function () {
        Route::get('/', \App\Livewire\OvhMonitoring\OvhOverview::class)->name('overview');
        Route::get('/vps', \App\Livewire\OvhMonitoring\OvhVpsList::class)->name('vps');
        Route::get('/dedicated-servers', \App\Livewire\OvhMonitoring\OvhDedicatedServersList::class)->name('dedicated-servers');
        Route::get('/domains', \App\Livewire\OvhMonitoring\OvhDomainsList::class)->name('domains');
    });

    // Notifications Routes
    Route::get('/notifications', \App\Livewire\NotificationIndex::class)->name('notifications.index');

    // SSO Management Routes
    Route::get('/sso-management', \App\Livewire\SsoManagement::class)->name('sso-management');
});
