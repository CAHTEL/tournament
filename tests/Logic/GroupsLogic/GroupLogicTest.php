<?php

namespace Tests\Logic\GroupsLogic;

use App\Logic\GroupsLogic\BestByScoreGroupLogic;
use App\Logic\GroupsLogic\GroupLogic;
use App\Logic\GroupsLogic\PlayOffGroupLogic;
use App\Logic\TournamentLogic;
use App\Models\Group;
use App\Models\Team;
use App\Models\Tournament;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use ReflectionMethod;
use Tests\TestCase;

class GroupLogicTest extends TestCase
{
    use DatabaseTransactions;

    /** @var GroupLogic $groupLogic */
    protected $groupLogic;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->groupLogic = resolve(GroupLogic::class);
    }

    public function testGetLogic()
    {
        $tournament = Tournament::factory()->create();
        $group = Group::factory()->create(['tournament_id' => $tournament->id]);
        $method = new ReflectionMethod($this->groupLogic, "getLogic");
        $method->setAccessible(true);
        $this->assertInstanceOf(PlayOffGroupLogic::class, $method->invoke($this->groupLogic, $group));
        $bestByScoreGroup = Group::factory()->create([
            'tournament_id' => $tournament->id,
            'type_id' => 2,
        ]);
        $this->assertInstanceOf(BestByScoreGroupLogic::class, $method->invoke($this->groupLogic, $bestByScoreGroup));
    }

    public function testGetNextOpponent()
    {
        $tournamentLogic = resolve(TournamentLogic::class);
        $teams = Team::factory()->count(16)->create();
        $tournament = $tournamentLogic->startTournament(...$teams);
        $groups = $tournament->groups->filter(fn (Group $group) =>
            $group->priority == 2 && !$this->groupLogic->isGroupGamesCompleted($group)
        );
        $teamA = $this->groupLogic->getNextOpponent($groups->first());
        $this->assertInstanceOf(Team::class, $teamA);
        $teamB = $this->groupLogic->getNextOpponent($groups->first(), $teamA);
        $this->assertInstanceOf(Team::class, $teamB);
        $this->assertNotEquals($teamA, $teamB);
        foreach ($groups as $group) {
            $this->groupLogic->playAllGamesInGroup($group);
        }
        $teamA = $this->groupLogic->getNextOpponent($groups->first());
        $this->assertNull($teamA);
    }
}
