<?php

namespace Tests\Operations;

use App\Models\Game;
use App\Models\GameResult;
use App\Models\Team;
use App\Operations\GameOperation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GameOperationTest extends TestCase
{
    use DatabaseTransactions;

    protected $gameOperation;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->gameOperation = resolve(GameOperation::class);
    }

    public function testPlayGame()
    {
        /** @var Team $teamOne */
        $teamOne = Team::factory()->create([
            'name' => 'First test team',
        ]);
        /** @var Team $teamSecond */
        $teamSecond = Team::factory()->create([
            'name' => 'First test team',
        ]);

        $game = $this->gameOperation->playGame($teamOne, $teamSecond);

        $this->assertInstanceOf(Game::class, $game);
        $this->assertDatabaseHas((new GameResult())->getTable(), [
            'team_id' => $teamOne->id,
        ]);

        $this->assertDatabaseHas((new GameResult())->getTable(), [
            'team_id' => $teamSecond->id,
        ]);
    }

    public function testGetGameWinnerWithWinner()
    {
        /** @var Team $teamOne */
        $teamOne = Team::factory()->create([
            'name' => 'First test team',
        ]);
        /** @var Team $teamSecond */
        $teamSecond = Team::factory()->create([
            'name' => 'First test team',
        ]);

        $game = Game::factory()->create();
        GameResult::factory()->create([
            'game_id' => $game->id,
            'team_id' => $teamOne->id,
            'score' => 1,
        ]);

        GameResult::factory()->create([
            'game_id' => $game->id,
            'team_id' => $teamSecond->id,
            'score' => 0,
        ]);

        $winnerTeam = $this->gameOperation->getGameWinner($game);
        $this->assertEquals($winnerTeam, Team::find($teamOne->id));
    }

    public function testGetGameWinnerWithDraw()
    {
        /** @var Team $teamOne */
        $teamOne = Team::factory()->create([
            'name' => 'First test team',
        ]);
        /** @var Team $teamSecond */
        $teamSecond = Team::factory()->create([
            'name' => 'First test team',
        ]);

        $game = Game::factory()->create();
        GameResult::factory()->create([
            'game_id' => $game->id,
            'team_id' => $teamOne->id,
            'score' => 0,
        ]);

        GameResult::factory()->create([
            'game_id' => $game->id,
            'team_id' => $teamSecond->id,
            'score' => 0,
        ]);

        $winnerTeam = $this->gameOperation->getGameWinner($game);
        $this->assertNull($winnerTeam);
    }

    public function testIsDraw()
    {
        $game = Game::factory()->create();
        [$teamA, $teamB] = Team::factory()->count(2)->create();
        GameResult::factory()->create([
            'game_id' => $game->id,
            'team_id' => $teamA->id,
            'score' => 1,
        ]);

        GameResult::factory()->create([
            'game_id' => $game->id,
            'team_id' => $teamB->id,
            'score' => 0,
        ]);

        $this->assertFalse($this->gameOperation->isDraw($game));

        $game = Game::factory()->create();
        GameResult::factory()->create([
            'game_id' => $game->id,
            'team_id' => $teamA->id,
            'score' => 1,
        ]);

        GameResult::factory()->create([
            'game_id' => $game->id,
            'team_id' => $teamB->id,
            'score' => 1,
        ]);

        $this->assertTrue($this->gameOperation->isDraw($game));
    }
}
