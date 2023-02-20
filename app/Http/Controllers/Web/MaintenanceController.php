<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Maintenance;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class MaintenanceController extends Controller
{
    public function __invoke(): View
    {
        $maintenances = (new Maintenance)->orderByStartAt()->get();

        return view('maintenances', compact('maintenances'));
    }
}
