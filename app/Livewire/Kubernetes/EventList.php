<?php

namespace App\Livewire\Kubernetes;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Services\KubernetesService;
use Carbon\Carbon;

class EventList extends Component
{
    public $events = [];
    public $loading = true;
    public $error = null;
    public $selectedCluster = null;
    public $searchTerm = '';
    public $selectedNamespaces = ['all'];
    public $namespaces = [];
    public $showNamespaceFilter = false;

    // Pagination properties
    public $perPage = 10;
    public $currentPage = 1;
    public $totalItems = 0;

    protected $listeners = ['clusterSelected' => 'handleClusterSelected'];

    public function mount()
    {
        // Get the selected cluster from session
        $this->selectedCluster = session('selectedCluster');

        if ($this->selectedCluster) {
            $this->loadNamespaces();
            $this->loadEvents();
        }
    }

    public function updatedSelectedCluster()
    {
        // Save the selected cluster to session
        session(['selectedCluster' => $this->selectedCluster]);

        // Load events for the selected cluster
        $this->loadNamespaces();
        $this->loadEvents();
    }

    public function loadNamespaces()
    {
        try {
            $kubeconfigPath = env('KUBECONFIG_PATH', storage_path('app/kubeconfigs')) . '/' . $this->selectedCluster;
            $service = new KubernetesService($kubeconfigPath);
            $response = $service->getNamespaces();

            if (isset($response['items'])) {
                $this->namespaces = collect($response['items'])
                    ->map(function ($namespace) {
                        return $namespace['metadata']['name'];
                    })
                    ->toArray();
            } else {
                $this->namespaces = [];
            }
        } catch (\Exception $e) {
            $this->error = 'Failed to load namespaces: ' . $e->getMessage();
        }
    }

    public function loadEvents()
    {
        $this->loading = true;
        $this->error = null;

        try {
            $kubeconfigPath = env('KUBECONFIG_PATH', storage_path('app/kubeconfigs')) . '/' . $this->selectedCluster;
            $service = new KubernetesService($kubeconfigPath);
            $response = $service->getEvents();

            if (isset($response['items'])) {
                $this->events = $response['items'];
            } else {
                $this->events = [];
            }
        } catch (\Exception $e) {
            $this->error = 'Failed to load events: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    public function toggleNamespaceFilter()
    {
        $this->showNamespaceFilter = !$this->showNamespaceFilter;
    }

    public function toggleNamespace($namespace)
    {
        if ($namespace === 'all') {
            $this->selectedNamespaces = ['all'];
        } else {
            // Remove 'all' if it's in the array
            $this->selectedNamespaces = array_filter($this->selectedNamespaces, function ($ns) {
                return $ns !== 'all';
            });

            // Toggle the selected namespace
            if (in_array($namespace, $this->selectedNamespaces)) {
                $this->selectedNamespaces = array_filter($this->selectedNamespaces, function ($ns) use ($namespace) {
                    return $ns !== $namespace;
                });

                // If no namespaces are selected, select 'all'
                if (empty($this->selectedNamespaces)) {
                    $this->selectedNamespaces = ['all'];
                }
            } else {
                $this->selectedNamespaces[] = $namespace;
            }
        }
    }

    public function getFilteredEventsProperty()
    {
        if (empty($this->events)) {
            return [];
        }

        $events = collect($this->events);

        // Filter by namespace
        if (!in_array('all', $this->selectedNamespaces)) {
            $events = $events->filter(function ($event) {
                return in_array($event['metadata']['namespace'] ?? 'default', $this->selectedNamespaces);
            });
        }

        // Filter by search term
        if (!empty($this->searchTerm)) {
            $searchTerm = strtolower($this->searchTerm);
            $events = $events->filter(function ($event) use ($searchTerm) {
                $type = strtolower($event['type'] ?? '');
                $message = strtolower($event['message'] ?? '');
                $namespace = strtolower($event['metadata']['namespace'] ?? 'default');
                $involvedObject = strtolower($event['involvedObject']['kind'] ?? '') . '/' . strtolower($event['involvedObject']['name'] ?? '');
                $source = strtolower($event['source']['component'] ?? '') . (isset($event['source']['host']) ? '/' . strtolower($event['source']['host']) : '');

                return str_contains($type, $searchTerm) ||
                       str_contains($message, $searchTerm) ||
                       str_contains($namespace, $searchTerm) ||
                       str_contains($involvedObject, $searchTerm) ||
                       str_contains($source, $searchTerm);
            });
        }

        // Calculate total for pagination
        $this->totalItems = $events->count();

        // Reset current page if it's out of bounds
        $maxPage = max(1, ceil($this->totalItems / $this->perPage));
        if ($this->currentPage > $maxPage) {
            $this->currentPage = 1;
        }

        // Apply pagination
        $paginatedEvents = $events->forPage($this->currentPage, $this->perPage);

        return $paginatedEvents->values()->all();
    }

    public function formatTimeAgo($timestamp)
    {
        if (!$timestamp) {
            return 'N/A';
        }

        $creationTime = Carbon::parse($timestamp);
        $now = Carbon::now();
        $diffInDays = $creationTime->diffInDays($now);

        if ($diffInDays > 0) {
            return $diffInDays . 'd';
        }

        $diffInHours = $creationTime->diffInHours($now);
        if ($diffInHours > 0) {
            return $diffInHours . 'h';
        }

        $diffInMinutes = $creationTime->diffInMinutes($now);
        if ($diffInMinutes > 0) {
            return $diffInMinutes . 'm';
        }

        return $creationTime->diffInSeconds($now) . 's';
    }

    public function getInvolvedObject($event)
    {
        if (!isset($event['involvedObject'])) {
            return 'N/A';
        }

        $kind = $event['involvedObject']['kind'] ?? '';
        $name = $event['involvedObject']['name'] ?? '';

        return $kind . '/' . $name;
    }

    public function getSource($event)
    {
        if (!isset($event['source'])) {
            return 'N/A';
        }

        $component = $event['source']['component'] ?? '';
        $host = $event['source']['host'] ?? '';

        return $component . ($host ? ' (' . $host . ')' : '');
    }

    public function previousPage()
    {
        if ($this->currentPage > 1) {
            $this->currentPage--;
        }
    }

    public function nextPage()
    {
        $maxPage = max(1, ceil($this->totalItems / $this->perPage));
        if ($this->currentPage < $maxPage) {
            $this->currentPage++;
        }
    }

    public function goToPage($page)
    {
        // Validate the page number to ensure it's within valid range
        $maxPage = max(1, ceil($this->totalItems / $this->perPage));
        $page = max(1, min($maxPage, (int)$page));

        $this->currentPage = $page;
    }

    public function handleClusterSelected($clusterName)
    {
        $this->selectedCluster = $clusterName;
        $this->loadNamespaces();
        $this->loadEvents();
    }

    public function render()
    {
        try {
            return view('livewire.kubernetes.event-list', [
                'filteredEvents' => $this->filteredEvents,
            ])->layout('layouts.kubernetes');
        } catch (\Exception $e) {
            // Log the error
            \Illuminate\Support\Facades\Log::error('Error rendering Events page: ' . $e->getMessage());

            // Reset pagination to first page
            $this->currentPage = 1;

            // Return the view with an error message
            return view('livewire.kubernetes.event-list', [
                'filteredEvents' => [],
                'error' => 'An error occurred while loading events. Please try again.'
            ])->layout('layouts.kubernetes');
        }
    }
}
