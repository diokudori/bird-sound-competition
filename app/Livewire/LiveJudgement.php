<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Contest;
use App\Models\Bird;
use App\Models\ContestParticipant;
use App\Models\GradeConfig;
use App\Models\Score;
use App\Imports\GradeConfigImport;
use Maatwebsite\Excel\Facades\Excel;

class LiveJudgement extends Component
{
    use WithFileUploads;

    public $activeContest;
    public $activeParticipantId;
    public $excelFile;
    public $gradesList = [];

    // CCTV Status for each gantangan (simulated or updated via API/Events)
    public $cctvStatus = [];

    protected $listeners = [
        'refreshLeaderboard' => '$refresh',
        'cctvMotionDetected' => 'handleCctvMotion',
    ];

    public function mount()
    {
        // Find or create an active contest for demo/test purposes
        $this->activeContest = Contest::where('status', 'active')->first();
        if (!$this->activeContest) {
            $this->activeContest = Contest::create([
                'name' => 'Kontes Kicau Nasional - Piala Walikota',
                'date' => now()->toDateString(),
                'status' => 'active',
            ]);
        }

        // Bootstrap some dummy participants if empty
        if ($this->activeContest->participants()->count() === 0) {
            $this->seedDummyParticipants();
        }

        // Set default active participant (gantangan 1 or first available)
        $firstParticipant = $this->activeContest->participants()->orderBy('gantangan_number')->first();
        $this->activeParticipantId = $firstParticipant ? $firstParticipant->id : null;

        // Initialize CCTV statuses
        foreach ($this->activeContest->participants as $part) {
            $this->cctvStatus[$part->id] = true; // Default to active (motion detected)
        }

        $this->loadGrades();
    }

    /**
     * Load grade configurations. If database is empty, seed standard frequency grades.
     */
    public function loadGrades()
    {
        $configs = GradeConfig::orderBy('freq_min')->get();
        
        if ($configs->isEmpty()) {
            // Seed default 10 grades if Excel hasn't been imported yet
            // Frequencies represent typical bird sounds (e.g., 1kHz - 8kHz)
            $defaultGrades = [
                ['freq_min' => 1000, 'freq_max' => 1500, 'grade_name' => 'Bass Low', 'hex_color' => '#3b82f6', 'score' => 50],
                ['freq_min' => 1501, 'freq_max' => 2500, 'grade_name' => 'Low Mid', 'hex_color' => '#10b981', 'score' => 60],
                ['freq_min' => 2501, 'freq_max' => 3500, 'grade_name' => 'Mid', 'hex_color' => '#f59e0b', 'score' => 70],
                ['freq_min' => 3501, 'freq_max' => 4500, 'grade_name' => 'High Mid', 'hex_color' => '#ef4444', 'score' => 80],
                ['freq_min' => 4501, 'freq_max' => 6000, 'grade_name' => 'Treble Clear', 'hex_color' => '#8b5cf6', 'score' => 90],
                ['freq_min' => 6001, 'freq_max' => 8000, 'grade_name' => 'Super Treble', 'hex_color' => '#ec4899', 'score' => 100],
            ];

            foreach ($defaultGrades as $dg) {
                GradeConfig::create($dg);
            }
            $configs = GradeConfig::orderBy('freq_min')->get();
        }

        $this->gradesList = $configs->toArray();
    }

    /**
     * Handle Excel grade configuration import.
     */
    public function importGrades()
    {
        $this->validate([
            'excelFile' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        try {
            // Truncate existing grades config before import to avoid overlap/duplication
            GradeConfig::truncate();

            Excel::import(new GradeConfigImport, $this->excelFile->path());

            $this->loadGrades();
            $this->dispatch('grades-updated', grades: $this->gradesList);
            session()->flash('message', 'Konfigurasi Grade berhasil di-import dari file Excel!');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengimpor Excel: ' . $e->getMessage());
        }
    }

    /**
     * Switch currently judged bird/participant.
     */
    public function selectParticipant($id)
    {
        $this->activeParticipantId = $id;
    }

    /**
     * Receive real-time score logs from client-side microphone analyzer.
     */
    public function recordScorePoint($duration, $frequency, $gradeScore, $calculatedScore)
    {
        if (!$this->activeParticipantId) return;

        // Verify if bird is active based on CCTV motion tracking
        $isActive = $this->cctvStatus[$this->activeParticipantId] ?? true;

        // If not active, reduce score or set calculated score to 0
        $finalCalculatedScore = $isActive ? $calculatedScore : 0;

        Score::create([
            'contest_participant_id' => $this->activeParticipantId,
            'duration_seconds' => floatval($duration),
            'frequency_hz' => floatval($frequency),
            'grade_score' => intval($gradeScore),
            'calculated_score' => intval($finalCalculatedScore),
            'is_active_tracking' => $isActive,
        ]);

        $this->dispatch('score-recorded');
    }

    /**
     * Handle motion tracking signals from CCTV feed.
     * Can be called by websocket or HTTP webhook when motion is detected for a participant's gantangan.
     */
    public function handleCctvMotion($participantId, $hasMotion)
    {
        $this->cctvStatus[$participantId] = (bool) $hasMotion;
    }

    /**
     * Toggle CCTV status manually on the dashboard for testing.
     */
    public function toggleCctv($participantId)
    {
        if (isset($this->cctvStatus[$participantId])) {
            $this->cctvStatus[$participantId] = !$this->cctvStatus[$participantId];
        }
    }

    /**
     * Reset scores for the active participant.
     */
    public function resetScores()
    {
        if (!$this->activeParticipantId) return;

        Score::where('contest_participant_id', $this->activeParticipantId)->delete();
        $this->dispatch('score-recorded');
    }

    /**
     * Seed initial dummy participants for testing.
     */
    private function seedDummyParticipants()
    {
        $dummyBirds = [
            ['name' => 'Guntur', 'ring_number' => 'RING-2026-001', 'owner_name' => 'Budi Santoso', 'bird_type' => 'Murai Batu'],
            ['name' => 'Raja Kicau', 'ring_number' => 'RING-2026-002', 'owner_name' => 'Andi Wijaya', 'bird_type' => 'Murai Batu'],
            ['name' => 'Petir', 'ring_number' => 'RING-2026-003', 'owner_name' => 'Hendra Setiawan', 'bird_type' => 'Kacer'],
            ['name' => 'Golden Boy', 'ring_number' => 'RING-2026-004', 'owner_name' => 'Surya Pratama', 'bird_type' => 'Kenari'],
            ['name' => 'Srikandi', 'ring_number' => 'RING-2026-005', 'owner_name' => 'Dewi Lestari', 'bird_type' => 'Lovebird'],
        ];

        foreach ($dummyBirds as $index => $db) {
            $bird = Bird::create($db);
            ContestParticipant::create([
                'contest_id' => $this->activeContest->id,
                'bird_id' => $bird->id,
                'gantangan_number' => $index + 1,
            ]);
        }
    }

    /**
     * Get active participant.
     */
    public function getActiveParticipantProperty()
    {
        return ContestParticipant::with('bird', 'scores')->find($this->activeParticipantId);
    }

    /**
     * Get active participant's real-time score points (for scatter chart).
     */
    public function getActiveParticipantScoresProperty()
    {
        if (!$this->activeParticipantId) return [];

        return Score::where('contest_participant_id', $this->activeParticipantId)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->reverse()
            ->values()
            ->map(function ($score) {
                return [
                    'x' => floatval($score->duration_seconds),
                    'y' => intval($score->grade_score),
                    'freq' => floatval($score->frequency_hz),
                    'active' => (bool)$score->is_active_tracking
                ];
            })
            ->toArray();
    }

    /**
     * Get leaderboard rankings.
     */
    public function getLeaderboardProperty()
    {
        return ContestParticipant::with('bird', 'scores')
            ->where('contest_id', $this->activeContest->id)
            ->get()
            ->map(function ($part) {
                return [
                    'id' => $part->id,
                    'name' => $part->bird->name,
                    'ring_number' => $part->bird->ring_number,
                    'bird_type' => $part->bird->bird_type,
                    'gantangan' => $part->gantangan_number,
                    'total_score' => $part->total_score,
                ];
            })
            ->sortByDesc('total_score')
            ->values()
            ->toArray();
    }

    public function render()
    {
        return view('livewire.live-judgement', [
            'activeParticipant' => $this->activeParticipant,
            'activeParticipantScores' => $this->activeParticipantScores,
            'leaderboard' => $this->leaderboard,
            'participants' => ContestParticipant::with('bird')
                ->where('contest_id', $this->activeContest->id)
                ->orderBy('gantangan_number')
                ->get(),
        ])->layout('components.layouts.app');
    }
}
