<?php

namespace App\Http\Controllers\Reports;

use App\Domain\Reports\Report;
use App\Domain\Reports\Services\ReportService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReportController extends Controller
{
    /**
     * Display Sales Report.
     */
    public function sales(Request $request, ReportService $service): JsonResponse
    {
        Gate::authorize('viewAny', Report::class);

        $result = $service->salesReport($request->user(), $request->all());

        return response()->json($result, 501);
    }

    /**
     * Display Customer Report.
     */
    public function customers(Request $request, ReportService $service): JsonResponse
    {
        Gate::authorize('viewAny', Report::class);

        $result = $service->customerReport($request->user(), $request->all());

        return response()->json($result, 501);
    }

    /**
     * Display Renewal Report.
     */
    public function renewals(Request $request, ReportService $service): JsonResponse
    {
        Gate::authorize('viewAny', Report::class);

        $result = $service->renewalReport($request->user(), $request->all());

        return response()->json($result, 501);
    }

    /**
     * Export report data in Excel, CSV, or PDF format.
     */
    public function export(
        Request $request,
        ReportService $service,
        string $format,
        ?string $report = null
    ): JsonResponse {
        Gate::authorize('viewAny', Report::class);

        $knownReports = ['sales', 'customers', 'renewals'];
        $validFormats = ['excel', 'csv', 'pdf'];

        // Handle possible transposed positional arguments: export($request, $service, 'sales', 'csv')
        if (in_array(strtolower($format), $knownReports, true) && in_array(strtolower((string) $report), $validFormats, true)) {
            $actualReport = strtolower($format);
            $actualFormat = strtolower((string) $report);
        } else {
            $actualFormat = strtolower($format);
            $actualReport = strtolower((string) ($report ?? $request->segment(2)));
        }

        if (! in_array($actualFormat, $validFormats, true)) {
            return response()->json([
                'message' => 'Invalid export format. Supported formats are: excel, csv, pdf.',
                'errors' => [
                    'format' => ['The selected export format is invalid.'],
                ],
            ], 422);
        }

        if (! in_array($actualReport, $knownReports, true)) {
            return response()->json([
                'message' => 'Report not found.',
            ], 404);
        }

        return $service->export(
            $actualReport,
            $actualFormat,
            $request->user(),
            $request->all()
        );
    }
}
