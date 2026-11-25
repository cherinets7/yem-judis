<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Support\OrganizationScope;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(): View
    {
        $logs = AuditLog::query()
            ->with(['user', 'organization'])
            ->whereIn('organization_id', OrganizationScope::ids())
            ->latest()
            ->paginate(20);

        return view('audit.index', [
            'logs' => $logs,
        ]);
    }
}
