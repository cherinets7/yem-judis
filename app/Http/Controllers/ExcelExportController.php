<?php

namespace App\Http\Controllers;

use App\Exports\ProsecutorWeeklyReportExport;
use App\Exports\RegisterExport;
use App\Exports\StaffProfileExport;
use App\Support\ExportRecords;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExcelExportController extends Controller
{
    public function download(Request $request, string $key): BinaryFileResponse
    {
        $definition = ExportRecords::definition($key);
        $records = ExportRecords::registerQuery($definition, $request)->get();

        if ($key === 'staff-profiles') {
            return Excel::download(new StaffProfileExport($records), 'staff.xlsx');
        }

        return Excel::download(
            new RegisterExport($definition, ExportRecords::columns($definition), $records),
            'register.xlsx',
        );
    }

    public function prosecutorWeekly(int $report): BinaryFileResponse
    {
        $record = ExportRecords::report($report);

        return Excel::download(
            new ProsecutorWeeklyReportExport([ExportRecords::payload($record)]),
            'prosecutor-weekly-'.$record->id.'.xlsx',
        );
    }

    public function prosecutorWeeklyCollection(Request $request): BinaryFileResponse
    {
        return Excel::download(
            new ProsecutorWeeklyReportExport(ExportRecords::prosecutorPayloads($request->string('type')->toString())),
            'prosecutor-weekly.xlsx',
        );
    }
}
