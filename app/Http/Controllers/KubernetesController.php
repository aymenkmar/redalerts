<?php


namespace App\Http\Controllers;

use App\Services\KubernetesService;
use Illuminate\Http\Request;

class KubernetesController extends Controller
{
    protected function getServiceByConfigName($configName)
    {
        $kubeconfigPath = env('KUBECONFIG_PATH') . '/' . $configName;
        return new KubernetesService($kubeconfigPath);
    }

    public function getNodes($configName)
    {
        try {
            $service = $this->getServiceByConfigName($configName);
            $nodes = $service->getNodes();
            return response()->json($nodes);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getPods($configName)
    {
        try {
            $service = $this->getServiceByConfigName($configName);
            $pods = $service->getPods();
            return response()->json($pods);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getServices($configName)
    {
        try {
            $service = $this->getServiceByConfigName($configName);
            $services = $service->getServices();
            return response()->json($services);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

