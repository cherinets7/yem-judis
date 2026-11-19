<?php

namespace App\Http\Controllers;

use App\Support\ExportRecords;
use App\Support\ProsecutorWeeklyMatrix;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class PdfExportController extends Controller
{
    public function download(Request $request, string $key): Response
    {
        $definition = ExportRecords::definition($key);

        if ($key === 'staff-profiles') {
            return $this->render('pdf.staff', [
                'profiles' => ExportRecords::registerQuery($definition, $request)->get(),
            ], 'የሰው-ሀብት.pdf');
        }

        return $this->render('pdf.register', [
            'definition' => $definition,
            'columns' => ExportRecords::columns($definition),
            'records' => ExportRecords::registerQuery($definition, $request)->get(),
        ], $definition['title'].'.pdf');
    }

    public function prosecutorWeekly(int $report): Response
    {
        $record = ExportRecords::report($report);

        return $this->render('pdf.prosecutor-weekly', [
            'reports' => [ExportRecords::payload($record)],
        ], 'የዐቃቤ-ህግ-ሳምንታዊ-'.$record->id.'.pdf');
    }

    public function prosecutorWeeklyCollection(Request $request): Response
    {
        $type = $request->string('type')->toString();

        return $this->render('pdf.prosecutor-weekly', [
            'reports' => ExportRecords::prosecutorPayloads($type),
            'emptyTitle' => array_key_exists($type, ProsecutorWeeklyMatrix::types())
                ? ProsecutorWeeklyMatrix::title($type)
                : 'የዐቃቤ ህግ ሳምንታዊ ሪፖርት',
        ], 'የዐቃቤ-ህግ-ሳምንታዊ.pdf');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function render(string $view, array $data, string $filename): Response
    {
        $directory = storage_path('app/mpdf');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $fontDirs = (new ConfigVariables)->getDefaults()['fontDir'];
        $fontData = (new FontVariables)->getDefaults()['fontdata'];
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'margin_left' => 8,
            'margin_right' => 8,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'default_font' => 'notosansethiopic',
            'autoScriptToLang' => false,
            'autoLangToFont' => false,
            'tempDir' => $directory,
            'fontDir' => array_merge($fontDirs, [storage_path('fonts')]),
            'fontdata' => $fontData + [
                'notosansethiopic' => [
                    'R' => 'NotoSansEthiopic-Regular.ttf',
                ],
            ],
        ]);
        $mpdf->WriteHTML(view($view, $data)->render());

        return response($mpdf->Output('', Destination::STRING_RETURN), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"export.pdf\"; filename*=UTF-8''".rawurlencode($filename),
        ]);
    }
}
