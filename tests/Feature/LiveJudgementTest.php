<?php

namespace Tests\Feature;

use App\Models\Contest;
use App\Models\Bird;
use App\Models\ContestParticipant;
use App\Models\GradeConfig;
use App\Models\Score;
use App\Livewire\LiveJudgement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LiveJudgementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed some grade config parameters
        GradeConfig::create([
            'freq_min' => 1000,
            'freq_max' => 2000,
            'grade_name' => 'Grade A',
            'hex_color' => '#10b981',
            'score' => 100
        ]);
    }

    /** @test */
    public function it_can_initialize_active_contest_and_participants()
    {
        $contest = Contest::create([
            'name' => 'Piala Indonesia',
            'date' => now()->toDateString(),
            'status' => 'active'
        ]);

        $bird = Bird::create([
            'name' => 'Si Kumbang',
            'ring_number' => 'RNG-001',
            'owner_name' => 'Joko',
            'bird_type' => 'Murai Batu'
        ]);

        $participant = ContestParticipant::create([
            'contest_id' => $contest->id,
            'bird_id' => $bird->id,
            'gantangan_number' => 1
        ]);

        Livewire::test(LiveJudgement::class)
            ->assertSet('activeContest.id', $contest->id)
            ->assertSet('activeParticipantId', $participant->id);
    }

    /** @test */
    public function it_can_log_score_point_and_add_to_cumulative_score()
    {
        $contest = Contest::create([
            'name' => 'Piala Indonesia',
            'date' => now()->toDateString(),
            'status' => 'active'
        ]);

        $bird = Bird::create([
            'name' => 'Si Kumbang',
            'ring_number' => 'RNG-001',
            'owner_name' => 'Joko',
            'bird_type' => 'Murai Batu'
        ]);

        $participant = ContestParticipant::create([
            'contest_id' => $contest->id,
            'bird_id' => $bird->id,
            'gantangan_number' => 1
        ]);

        // Call the recordScorePoint method inside the Livewire component
        // Duration: 2.5 seconds, Frequency: 1500 Hz, Grade Score: 100, Calculated Score: 250 (2.5 * 100)
        Livewire::test(LiveJudgement::class)
            ->call('selectParticipant', $participant->id)
            ->call('recordScorePoint', 2.50, 1500.00, 100, 250)
            ->assertDispatched('score-recorded');

        // Check if database contains the logged score
        $this->assertDatabaseHas('scores', [
            'contest_participant_id' => $participant->id,
            'duration_seconds' => 2.50,
            'frequency_hz' => 1500.00,
            'grade_score' => 100,
            'calculated_score' => 250,
        ]);

        // Assert that the total score has increased
        $this->assertEquals(250, $participant->fresh()->total_score);
    }

    /** @test */
    public function it_does_not_log_score_if_cctv_motion_is_inactive()
    {
        $contest = Contest::create([
            'name' => 'Piala Indonesia',
            'date' => now()->toDateString(),
            'status' => 'active'
        ]);

        $bird = Bird::create([
            'name' => 'Si Kumbang',
            'ring_number' => 'RNG-001',
            'owner_name' => 'Joko',
            'bird_type' => 'Murai Batu'
        ]);

        $participant = ContestParticipant::create([
            'contest_id' => $contest->id,
            'bird_id' => $bird->id,
            'gantangan_number' => 1
        ]);

        // Toggle CCTV status to false (no motion detected)
        Livewire::test(LiveJudgement::class)
            ->call('selectParticipant', $participant->id)
            ->set('cctvStatus.' . $participant->id, false)
            ->call('recordScorePoint', 2.50, 1500.00, 100, 250);

        // Check if database recorded calculated_score as 0 due to inactive motion
        $this->assertDatabaseHas('scores', [
            'contest_participant_id' => $participant->id,
            'calculated_score' => 0,
            'is_active_tracking' => false
        ]);

        // Cumulative score should stay 0
        $this->assertEquals(0, $participant->fresh()->total_score);
    }
}
