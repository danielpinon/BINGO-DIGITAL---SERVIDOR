<?php

namespace Tests\Feature;

use App\Livewire\BingoDraw;
use App\Models\Bingo;
use App\Models\BingoPrizePattern;
use App\Models\BingoRound;
use App\Models\Card;
use App\Models\CardNumber;
use App\Models\DrawnNumber;
use App\Models\Responsible;
use App\Models\User;
use App\Services\WinnerDetectionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class BingoRoundsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_bingo_can_be_created_with_up_to_five_rounds_and_generates_cards(): void
    {
        $user = User::factory()->create();

        foreach ([1, 3, 5] as $roundQuantity) {
            $response = $this->actingAs($user)->post(route('bingos.store'), [
                'name' => 'Bingo ' . $roundQuantity,
                'description' => null,
                'event_date' => now()->format('Y-m-d'),
                'event_time' => '19:00',
                'number_range_start' => 1,
                'number_range_end' => 75,
                'card_quantity' => 10,
                'numbers_per_card' => 25,
                'round_quantity' => $roundQuantity,
                'cards_per_page' => 2,
                'prize_patterns' => ['line'],
            ]);

            $response->assertRedirect(route('bingos.index'));

            $bingo = Bingo::where('name', 'Bingo ' . $roundQuantity)->firstOrFail();
            $this->assertSame($roundQuantity, (int) $bingo->round_quantity);
            $this->assertSame(2, (int) $bingo->cards_per_page);
            $this->assertSame($roundQuantity, $bingo->rounds()->count());
            $this->assertSame(10, $bingo->cards()->count());
        }
    }

    public function test_card_generation_prepares_pdf_and_keeps_one_card_set_for_all_rounds(): void
    {
        $user = User::factory()->create();
        $bingo = $this->createBingoWithRounds($user, 3);

        $response = $this->actingAs($user)->post(route('cards.generate'), [
            'bingo_id' => $bingo->id,
            'quantity' => 4,
        ]);

        $response->assertRedirect(route('cards.index', ['bingo_id' => $bingo->id]));
        $this->assertSame(4, $bingo->cards()->count());
        $this->assertSame(3, $bingo->rounds()->count());

        $bingo->refresh()->forceFill([
            'cards_pdf_path' => null,
            'cards_pdf_status' => 'processing',
            'cards_pdf_generated_at' => null,
        ])->save();

        $pdfResponse = $this->actingAs($user)->get(route('cards.export', ['bingo_id' => $bingo->id, 'print' => 1]));
        $pdfResponse->assertOk();
        $this->assertSame('ready', $bingo->refresh()->cards_pdf_status);
        $this->assertNotNull($bingo->cards_pdf_path);
    }

    public function test_cron_command_generates_pending_cards_pdf(): void
    {
        $user = User::factory()->create();
        $bingo = $this->createBingoWithRounds($user, 1);
        $generator = app(\App\Services\CardGeneratorService::class);

        $this->assertSame(2, $generator->generate($bingo, 2));

        $bingo->forceFill([
            'cards_pdf_path' => null,
            'cards_pdf_status' => 'processing',
            'cards_pdf_generated_at' => null,
        ])->save();

        $this->artisan('bingos:generate-card-pdfs --limit=1 --bingo=' . $bingo->id)
            ->expectsOutputToContain('PDF gerado')
            ->assertExitCode(0);

        $bingo->refresh();
        $this->assertSame('ready', $bingo->cards_pdf_status);
        $this->assertNotNull($bingo->cards_pdf_path);
        $this->assertTrue(Storage::disk('local')->exists($bingo->cards_pdf_path));

        Storage::disk('local')->delete($bingo->cards_pdf_path);
    }

    public function test_card_generation_uses_next_available_number_after_existing_gap(): void
    {
        $user = User::factory()->create();
        $bingo = $this->createBingoWithRounds($user, 1);
        $generator = app(\App\Services\CardGeneratorService::class);

        $this->assertSame(3, $generator->generate($bingo, 3));

        $bingo->cards()->where('card_number', '002')->firstOrFail()->delete();

        $this->assertSame(1, $generator->generate($bingo->refresh(), 1));
        $this->assertTrue($bingo->cards()->where('card_number', '004')->exists());
    }

    public function test_confirming_last_prize_advances_to_next_round_with_empty_drawn_numbers(): void
    {
        $user = User::factory()->create();
        $bingo = $this->createBingoWithRounds($user, 2);
        $pattern = $bingo->prizePatterns()->first();
        $roundOne = $bingo->rounds()->where('round_number', 1)->first();

        $bingo->update([
            'status' => 'ongoing',
            'current_prize_pattern_id' => $pattern->id,
        ]);

        $roundOne->update([
            'status' => 'ongoing',
            'current_prize_pattern_id' => $pattern->id,
            'started_at' => now(),
        ]);

        $card = $this->createWinningLineCard($bingo);
        foreach ([1, 2, 3, 4, 5] as $number) {
            DrawnNumber::create([
                'bingo_id' => $bingo->id,
                'bingo_round_id' => $roundOne->id,
                'number' => $number,
                'drawn_at' => now(),
            ]);
        }

        $this->actingAs($user);

        Livewire::test(BingoDraw::class, ['bingoId' => $bingo->id])
            ->call('confirmWinner', $card->id);

        $roundOne->refresh();
        $roundTwo = $bingo->rounds()->where('round_number', 2)->first();

        $this->assertSame('finished', $roundOne->status);
        $this->assertSame('ongoing', $roundTwo->status);
        $this->assertSame(5, DrawnNumber::where('bingo_round_id', $roundOne->id)->count());
        $this->assertSame(0, DrawnNumber::where('bingo_round_id', $roundTwo->id)->count());
    }

    public function test_confirming_prize_advances_to_next_pattern_with_empty_drawn_numbers(): void
    {
        $user = User::factory()->create();
        $bingo = $this->createBingoWithRounds($user, 1);
        $firstPattern = $bingo->prizePatterns()->first();
        $nextPattern = BingoPrizePattern::create([
            'bingo_id' => $bingo->id,
            'name' => 'Cruz',
            'pattern_type' => 'cross',
            'pattern_order' => 2,
        ]);
        $round = $bingo->rounds()->first();

        $bingo->update([
            'status' => 'ongoing',
            'current_prize_pattern_id' => $firstPattern->id,
        ]);

        $round->update([
            'status' => 'ongoing',
            'current_prize_pattern_id' => $firstPattern->id,
            'started_at' => now(),
        ]);

        $card = $this->createWinningLineCard($bingo);
        foreach ([1, 2, 3, 4, 5] as $number) {
            DrawnNumber::create([
                'bingo_id' => $bingo->id,
                'bingo_round_id' => $round->id,
                'number' => $number,
                'drawn_at' => now(),
            ]);
        }

        $this->actingAs($user);

        Livewire::test(BingoDraw::class, ['bingoId' => $bingo->id])
            ->call('confirmWinner', $card->id)
            ->assertSet('drawnNumbers', [])
            ->assertSet('lastNumber', null);

        $this->assertSame('ongoing', $round->refresh()->status);
        $this->assertSame($nextPattern->id, $round->current_prize_pattern_id);
        $this->assertSame($nextPattern->id, $bingo->refresh()->current_prize_pattern_id);
        $this->assertSame(0, DrawnNumber::where('bingo_round_id', $round->id)->count());
    }

    public function test_public_screen_hides_card_and_responsible_data_for_close_cards(): void
    {
        $user = User::factory()->create();
        $bingo = $this->createBingoWithRounds($user, 1);
        $pattern = $bingo->prizePatterns()->first();
        $round = $bingo->rounds()->first();
        $card = $this->createWinningLineCard($bingo);

        $bingo->update([
            'status' => 'ongoing',
            'current_prize_pattern_id' => $pattern->id,
        ]);

        $round->update([
            'status' => 'ongoing',
            'current_prize_pattern_id' => $pattern->id,
            'started_at' => now(),
        ]);

        foreach ([1, 2, 3, 4] as $number) {
            DrawnNumber::create([
                'bingo_id' => $bingo->id,
                'bingo_round_id' => $round->id,
                'number' => $number,
                'drawn_at' => now(),
            ]);
        }

        $response = $this->get(route('public.screen', $bingo));

        $response->assertOk();
        $response->assertSee('Tem cartela perto de bater');
        $response->assertSee('05');
        $response->assertDontSee('Cartela ' . $card->card_number);
        $response->assertDontSee('Responsável');
    }

    public function test_public_screen_state_returns_latest_drawn_numbers_for_realtime_updates(): void
    {
        $user = User::factory()->create();
        $bingo = $this->createBingoWithRounds($user, 1);
        $pattern = $bingo->prizePatterns()->first();
        $round = $bingo->rounds()->first();

        $bingo->update([
            'status' => 'ongoing',
            'current_prize_pattern_id' => $pattern->id,
        ]);

        $round->update([
            'status' => 'ongoing',
            'current_prize_pattern_id' => $pattern->id,
            'started_at' => now(),
        ]);

        foreach ([3, 21] as $number) {
            DrawnNumber::create([
                'bingo_id' => $bingo->id,
                'bingo_round_id' => $round->id,
                'number' => $number,
                'drawn_at' => now()->addSeconds($number),
            ]);
        }

        $response = $this->getJson(route('public.screen.state', $bingo));

        $response->assertOk()
            ->assertJsonPath('round.id', $round->id)
            ->assertJsonPath('round.number', 1)
            ->assertJsonPath('drawn_numbers', [3, 21])
            ->assertJsonPath('last_drawn.number', 21);
    }

    public function test_only_linked_cards_option_ignores_unassigned_cards_for_winners(): void
    {
        $user = User::factory()->create();
        $bingo = $this->createBingoWithRounds($user, 1);
        $pattern = $bingo->prizePatterns()->first();
        $round = $bingo->rounds()->first();

        $bingo->update([
            'status' => 'ongoing',
            'current_prize_pattern_id' => $pattern->id,
            'only_linked_cards' => true,
        ]);

        $round->update([
            'status' => 'ongoing',
            'current_prize_pattern_id' => $pattern->id,
            'started_at' => now(),
        ]);

        $unassignedCard = $this->createWinningLineCard($bingo, '001');
        $linkedCard = $this->createWinningLineCard($bingo, '002');
        $responsible = Responsible::create([
            'name' => 'Responsavel Teste',
            'status' => 'active',
        ]);
        $linkedCard->update([
            'responsible_id' => $responsible->id,
            'status' => 'distributed',
        ]);

        $drawnNumbers = [1, 2, 3, 4, 5];
        foreach ($drawnNumbers as $number) {
            DrawnNumber::create([
                'bingo_id' => $bingo->id,
                'bingo_round_id' => $round->id,
                'number' => $number,
                'drawn_at' => now(),
            ]);
        }

        $detector = new WinnerDetectionService();
        $possibleWinners = $detector->getPossibleWinners($bingo->refresh(), $drawnNumbers, $round);

        $this->assertCount(1, $possibleWinners);
        $this->assertSame($linkedCard->id, $possibleWinners[0]['card']->id);
        $this->assertFalse($detector->verifyWinner($bingo->refresh(), $unassignedCard->id, $drawnNumbers, $round));
        $this->assertTrue($detector->verifyWinner($bingo->refresh(), $linkedCard->id, $drawnNumbers, $round));
    }

    public function test_cards_index_ignores_cards_from_deleted_bingos(): void
    {
        $user = User::factory()->create();
        $bingo = $this->createBingoWithRounds($user, 1);
        $card = $this->createWinningLineCard($bingo, '999');

        $bingo->delete();

        $response = $this->actingAs($user)->get(route('cards.index'));

        $response->assertOk();
        $response->assertDontSee($card->card_number);
    }

    public function test_card_assignment_can_create_responsible_from_search_field(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $bingo = $this->createBingoWithRounds($user, 1);
        $card = $this->createWinningLineCard($bingo, '321');

        $response = $this->actingAs($user)->post(route('cards.assign', $card), [
            'responsible_name' => 'Novo Responsavel',
        ]);

        $responsible = Responsible::where('name', 'Novo Responsavel')->firstOrFail();

        $response->assertRedirect();
        $this->assertSame($responsible->id, $card->fresh()->responsible_id);
        $this->assertSame('distributed', $card->fresh()->status);
        $this->assertSame('pending', $bingo->fresh()->cards_pdf_status);
    }

    public function test_card_assignment_uses_existing_responsible_from_search_field(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $bingo = $this->createBingoWithRounds($user, 1);
        $card = $this->createWinningLineCard($bingo, '322');
        $responsible = Responsible::create([
            'name' => 'Responsavel Existente',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->post(route('cards.assign', $card), [
            'responsible_id' => $responsible->id,
            'responsible_name' => $responsible->name,
        ]);

        $response->assertRedirect();
        $this->assertSame($responsible->id, $card->fresh()->responsible_id);
        $this->assertSame(1, Responsible::where('name', 'Responsavel Existente')->count());
    }

    private function createBingoWithRounds(User $user, int $roundQuantity): Bingo
    {
        $bingo = Bingo::create([
            'name' => 'Bingo Rodadas ' . uniqid(),
            'description' => null,
            'event_date' => now()->format('Y-m-d'),
            'event_time' => '19:00',
            'number_range_start' => 1,
            'number_range_end' => 75,
            'card_quantity' => 0,
            'numbers_per_card' => 25,
            'round_quantity' => $roundQuantity,
            'status' => 'preparation',
            'only_linked_cards' => false,
            'created_by' => $user->id,
        ]);

        BingoPrizePattern::create([
            'bingo_id' => $bingo->id,
            'name' => 'Linha',
            'pattern_type' => 'line',
            'pattern_order' => 1,
        ]);

        for ($roundNumber = 1; $roundNumber <= $roundQuantity; $roundNumber++) {
            BingoRound::create([
                'bingo_id' => $bingo->id,
                'round_number' => $roundNumber,
                'status' => 'pending',
            ]);
        }

        return $bingo;
    }

    private function createWinningLineCard(Bingo $bingo, string $cardNumber = '001'): Card
    {
        $card = Card::create([
            'bingo_id' => $bingo->id,
            'responsible_id' => null,
            'card_number' => $cardNumber,
            'status' => 'available',
        ]);

        $number = 1;
        for ($row = 0; $row < 5; $row++) {
            for ($col = 0; $col < 5; $col++) {
                CardNumber::create([
                    'card_id' => $card->id,
                    'row' => $row,
                    'col' => $col,
                    'number' => $number,
                ]);
                $number++;
            }
        }

        return $card;
    }
}
