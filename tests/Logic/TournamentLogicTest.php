<?php

namespace Tests\Logic;

use App\Logic\GroupsLogic\GroupLogic;
use App\Logic\TournamentLogic;
use App\Models\Group;
use App\Models\Team;
use App\Models\Tournament;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Tests\TestCase;

class TournamentLogicTest extends TestCase
{
    use DatabaseTransactions;

    /** @var TournamentLogic $tournamentLogic */
    protected $tournamentLogic;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->tournamentLogic = resolve(TournamentLogic::class);
    }

    public function testConfig()
    {
        $tournament = Tournament::factory()->create();
        $defaultConfig = TournamentLogic::DEFAULT_CONFIG;
        $this->assertEquals($defaultConfig, $tournament->config);
        $configWithNewGroup = [
            3 => ['count' => 4, 'type_id' => 2],
        ];

        $this->tournamentLogic->setConfig($tournament, $configWithNewGroup);
        $expected = array_replace($defaultConfig, $configWithNewGroup);
        $this->assertEquals($expected, $tournament->config);

        $secTournament = Tournament::factory()->create();
        $newConfig = [
            2 => ['count' => 4, 'type_id' => 2],
            1 => ['count' => 1, 'type_id' => 2],
        ];

        $this->tournamentLogic->setConfig($secTournament, $newConfig);
        $this->assertEquals($newConfig, $secTournament->config);
    }

    public function testAddTeamsToGroups()
    {
        $tournament = Tournament::factory()->create();
        $group = Group::factory()->create([
            'tournament_id' => $tournament->id
        ]);
        $teams = Team::factory()->count(5)->create();
        $this->tournamentLogic->addTeamsToGroups(Collection::make([$group]), $teams);
        $this->assertEquals(5, $group->groupTeams->count());

        $groupFirst = Group::factory()->create([
            'tournament_id' => $tournament->id
        ]);
        $groupSecond = Group::factory()->create([
            'tournament_id' => $tournament->id
        ]);;

        $teams = Team::factory()->count(16)->create();
        $this->tournamentLogic->addTeamsToGroups(Collection::make([$groupFirst, $groupSecond]), $teams);
        $this->assertEquals(8, $groupFirst->groupTeams->count());
        $this->assertEquals(8, $groupSecond->groupTeams->count());

        $groupFirst = Group::factory()->create([
            'tournament_id' => $tournament->id
        ]);
        $groupSecond = Group::factory()->create([
            'tournament_id' => $tournament->id
        ]);

        $teams = Team::factory()->count(17)->create();
        $this->tournamentLogic->addTeamsToGroups(Collection::make([$groupFirst, $groupSecond]), $teams);
        $this->assertEquals(9, $groupFirst->groupTeams->count());
        $this->assertEquals(8, $groupSecond->groupTeams->count());
    }

    public function testAddGroupsToTournament()
    {
        /** @var Tournament $tournament */
        $tournament = Tournament::factory()->create();
        $this->tournamentLogic->addGroupsToTournament($tournament);
        $groupsCount = $tournament->groups()->count();
        $this->assertEquals(3, $groupsCount);

        $secondTournament = Tournament::factory()->create();
        $newConfig = [
                2 => ['count' => 4, 'type_id' => 2],
                1 => ['count' => 1, 'type_id' => 2],
            ];
        $this->tournamentLogic->setConfig($secondTournament, $newConfig);
        $this->tournamentLogic->addGroupsToTournament($secondTournament);
        $groupsCount = $secondTournament->groups()->count();
        $this->assertEquals(5, $groupsCount);
    }

    public function testAddTeamsToNextGroups()
    {
        $groupLogic = resolve(GroupLogic::class);
        $teams = Team::factory()->count(16)->create();
        $tournament = $this->tournamentLogic->startTournament(...$teams);
        $groups = $tournament->groups->filter(fn (Group $group) => $group->priority == 2 && !$groupLogic->isGroupGamesCompleted($group));
        foreach ($groups as $group) {
            $groupLogic->playAllGamesInGroup($group);
        }
        $this->tournamentLogic->addTeamsToNextGroups($tournament, 1);
        $group = $tournament->groups->where('priority', 1)->last();
        $this->assertEquals(8, $group->groupTeams()->count());
    }
}
