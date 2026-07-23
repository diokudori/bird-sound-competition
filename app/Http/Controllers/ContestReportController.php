<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Contest;
use App\Models\ContestParticipant;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ContestReportController extends Controller
{
    /**
     * Export contest results to PDF.
     */
    public function exportPdf($contestId)
    {
        $contest = Contest::findOrFail($contestId);

        // Fetch participants with their bird info and preloaded scores
        $participants = ContestParticipant::with(['bird', 'scores'])
            ->where('contest_id', $contestId)
            ->get();

        // Map and sort participants by cumulative score
        $rankedParticipants = $participants->map(function ($part) {
            return [
                'name' => $part->bird->name,
                'ring_number' => $part->bird->ring_number,
                'owner_name' => $part->bird->owner_name,
                'bird_type' => $part->bird->bird_type,
                'gantangan' => $part->gantangan_number,
                'total_score' => $part->total_score,
            ];
        })->sortByDesc('total_score')->values()->toArray();

        // Local Server Digital Timestamp
        // Format: d-m-Y H:i:s T (e.g. 16-07-2026 19:56:57 WIB)
        $timestamp = Carbon::now()->setTimezone('Asia/Jakarta')->format('d-m-Y H:i:s') . ' WIB';

        $data = [
            'contest' => $contest,
            'participants' => $rankedParticipants,
            'timestamp' => $timestamp,
        ];

        // Load HTML template and generate PDF
        // Using dompdf options for landscape/portrait configuration
        $pdf = Pdf::loadView('reports.contest-report-pdf', $data)
                  ->setPaper('a4', 'portrait');

        // Set filename (lowercase and snakecase)
        $fileName = 'hasil_kontes_' . strtolower(str_replace(' ', '_', $contest->name)) . '_' . date('Ymd_His') . '.pdf';

        return $pdf->stream($fileName);
    }
}
