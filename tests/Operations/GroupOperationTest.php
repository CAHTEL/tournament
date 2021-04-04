<?php

namespace Tests\Operations;

use App\Models\GameResult;
use App\Models\Group;
use App\Models\GroupTeam;
use App\Models\Team;
use App\Models\Tournament;
use App\Operations\GroupOperation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GroupOperationTest extends TestCase
{
    use DatabaseTransactions;

    /** @var GroupOperation $groupOperation */
    protected $groupOperation;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->groupOperation = resolve(GroupOperation::class);
    }

    public function testHasGroupTeam()
    {
        $tournament = Tournament::factory()->create();
        $group = Group::factory()->create([
            'tournament_id' => $tournament->id,
            'priority' => 1,
            'type_id' => 1,
        ]);
        $team = Team::factory()->create();
        $this->assertFalse($this->groupOperation->hasGroupTeam($group, $team));

        GroupTeam::factory()->create([
            'group_id' => $group->id,
            'team_id' => $team->id,
        ]);
        $this->assertTrue($this->groupOperation->hasGroupTeam($group, $team));
    }

    public function testAddTeamToGroup()
    {
        $tournament = Tournament::factory()->create();
        /** @var Group $group */
        $group = Group::factory()->create([
            'tournament_id' => $tournament->id,
            'priority' => 1,
            'type_id' => 1,
        ]);
        $team = Team::factory()->create();
        $this->assertFalse($this->groupOperation->hasGroupTeam($group, $team));

        $this->groupOperation->addTeamToGroup($group, $team);
        $this->assertTrue($this->groupOperation->hasGroupTeam($group, $team));
        $this->groupOperation->addTeamToGroup($group, $team);
        $group->groupTeams()->where('team_id', $team->id)->count();
        $this->assertEquals(
            1,
            $group->groupTeams()->where('team_id', $team->id)->count()
        );
    }
}
