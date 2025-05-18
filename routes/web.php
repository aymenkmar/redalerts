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

    Route::get('/auth-test', function() {
        return view('api-test');
    });

    // API Management page (placeholder for now)
    Route::get('/api-management', function() {
        return view('dashboard'); // Using dashboard view as placeholder
    });
});
