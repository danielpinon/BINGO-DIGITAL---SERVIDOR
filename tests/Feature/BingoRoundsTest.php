<?php

namespace Tests\Feature;

use App\Livewire\BingoDraw;
use App\Models\Bingo;
use App\Models\BingoPrizePattern;
use App\Models\BingoRound;
use App\Models\Card;
use App\Models\CardNumber;
use App\Models\DrawnNumber;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class BingoRoundsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_bingo_can_be_created_with_three_to_five_rounds_and_generates_cards(): void
    {
        $user = User::factory()->create();

        foreach ([3, 5] as $roundQuantity) {
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

    public function test_card_generation_redirects_to_printable_pdf_and_keeps_one_card_set_for_all_rounds(): void
    {
        $user = User::factory()->create();
        $bingo = $this->createBingoWithRounds($user, 3);

        $response = $this->actingAs($user)->post(route('cards.generate'), [
            'bingo_id' => $bingo->id,
            'quantity' => 4,
        ]);

        $response->assertRedirect(route('cards.export', ['bingo_id' => $bingo->id, 'print' => 1]));
        $this->assertSame(4, $bingo->cards()->count());
        $this->assertSame(3, $bingo->rounds()->count());

        $pdfResponse = $this->actingAs($user)->get(route('cards.export', ['bingo_id' => $bingo->id, 'print' => 1]));
        $pdfResponse->assertOk();
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

    private function createWinningLineCard(Bingo $bingo): Card
    {
        $card = Card::create([
            'bingo_id' => $bingo->id,
            'responsible_id' => null,
            'card_number' => '001',
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
