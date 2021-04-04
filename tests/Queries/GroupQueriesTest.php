<?php

namespace Tests\Queries;

use App\Models\Game;
use App\Models\GameResult;
use App\Models\Group;
use App\Models\GroupGame;
use App\Models\Team;
use App\Models\Tournament;
use App\Operations\GroupOperation;
use App\Queries\GroupQueries;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Tests\TestCase;

class GroupQueriesTest extends TestCase
{
    use DatabaseTransactions;

    protected $groupQueries;
    protected $groupOperation;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->groupQueries = resolve(GroupQueries::class);
        $this->groupOperation = resolve(GroupOperation::class);
    }

    public function testGetAllGamesInGroupForTeam()
    {
        $tournament = Tournament::factory()->create();
        $group = Group::factory()->create([
            'tournament_id' => $tournament->id,
            'type_id' => 1,
            'priority' => 1,
        ]);
        $teamFirst = Team::factory()->create();
        $this->groupOperation->addTeamToGroup($group, $teamFirst);
        $gamesIds = Collection::make([]);
        for ($i = 0; $i < 3; $i++) {
            $team = Team::factory()->create();
            $this->groupOperation->addTeamToGroup($group, $team);
            $game = Game::factory()->create();
            GroupGame::factory()->create([
                'game_id' => $game->id,
                'group_id' => $group->id,
            ]);
            GameResult::factory()->create([
                'game_id' => $game->id,
                'team_id' => $teamFirst->id,
                'score' => 0,
            ]);
            GameResult::factory()->create([
                'game_id' => $game->id,
                'team_id' => $team->id,
                'score' => 0,
            ]);
            $gamesIds->push($game->id);
        }
        $this->assertEquals(
            $this->groupQueries->getAllGamesInGroupForTeam($group, $teamFirst)->pluck('id'),
            $gamesIds
        );

        $secondGroup = Group::factory()->create([
            'tournament_id' => $tournament->id,
            'type_id' => 1,
            'priority' => 1,
        ]);

        $team = Team::factory()->create();
        $this->groupOperation->addTeamToGroup($secondGroup, $teamFirst);
        $this->groupOperation->addTeamToGroup($secondGroup, $team);
        $game = Game::factory()->create();
        GroupGame::factory()->create([
            'game_id' => $game->id,
            'group_id' => $secondGroup->id,
        ]);
        GameResult::factory()->create([
            'game_id' => $game->id,
            'team_id' => $teamFirst->id,
            'score' => 0,
        ]);
        GameResult::factory()->create([
            'game_id' => $game->id,
            'team_id' => $team->id,
            'score' => 0,
        ]);
        $this->assertEquals(
            $this->groupQueries->getAllGamesInGroupForTeam($group, $teamFirst)->pluck('id'),
            $gamesIds
        );
    }

    public function testGetAllOpponents()
    {
        $tournament = Tournament::factory()->create();
        $group = Group::factory()->create([
            'tournament_id' => $tournament->id,
            'type_id' => 1,
            'priority' => 1,
        ]);
        $teamFirst = Team::factory()->create();
        $this->groupOperation->addTeamToGroup($group, $teamFirst);
        $opponentsIds = Collection::make([]);
        for ($i = 0; $i < 3; $i++) {
            $team = Team::factory()->create();
            $this->groupOperation->addTeamToGroup($group, $team);
            $game = Game::factory()->create();
            GroupGame::factory()->create([
                'game_id' => $game->id,
                'group_id' => $group->id,
            ]);
            GameResult::factory()->create([
                'game_id' => $game->id,
                'team_id' => $teamFirst->id,
                'score' => 0,
            ]);
            GameResult::factory()->create([
                'game_id' => $game->id,
                'team_id' => $team->id,
                'score' => 0,
            ]);
            $opponentsIds->push($team->id);
        }
        $this->assertEquals(
            $this->groupQueries->getAllOpponents($group, $teamFirst)->pluck('team_id'),
            $opponentsIds
        );
    }
}
