<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Maintenance;
use Illuminate\Http\JsonResponse;

class MaintenanceController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $maintenances = (new Maintenance)->orderByStartAt()->get();

        return $this->success($maintenances);
    }
}
