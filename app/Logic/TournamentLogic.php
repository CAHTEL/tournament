<?php

declare(strict_types=1);

namespace App\Logic;

use App\Logic\GroupsLogic\GroupLogic;
use App\Models\Group;
use App\Models\Team;
use App\Models\Tournament;
use App\Operations\GroupOperation;
use Illuminate\Support\Collection;

class TournamentLogic
{
    public const DEFAULT_CONFIG = [
        2 => ['count' => 2, 'type_id' => 2],
        1 => ['count' => 1, 'type_id' => 1],
    ];

    /** @var GroupOperation $groupOperation */
    protected $groupOperation;
    /** @var GroupLogic $groupLogic */
    protected $groupLogic;

    public function __construct(GroupOperation $groupOperation, GroupLogic $groupLogic)
    {
        $this->groupOperation = $groupOperation;
        $this->groupLogic = $groupLogic;
    }

    /**
     * @param mixed ...$teams
     * @return Tournament
     */
    public function startTournament(...$teams): Tournament
    {
        $tournament = new Tournament();
        $tournament->save();
        $this->addGroupsToTournament($tournament);
        $maxPriority = $tournament->groups()->max('priority');
        $groupsWithMaxPriority = $tournament->groups()->where('priority', $maxPriority)->get();
        $this->addTeamsToGroups($groupsWithMaxPriority, Collection::make($teams));
        return $tournament;
    }

    /**
     * @param Tournament $tournament
     */
    public function addGroupsToTournament(Tournament $tournament): void
    {
        foreach ($tournament->config as $priority => $list) {
            for ($i = 0; $i < $list['count']; $i++) {
                $tournament->groups()->create([
                    'priority' => $priority,
                    'type_id' => $list['type_id'],
                ]);
            }
        }
    }

    /**
     * @param Tournament $tournament
     * @param array $config
     */
    public function setConfig(Tournament $tournament, array $config): void
    {
        $newConfig = array_replace($tournament->config, $config);

        if ($newConfig[1]['count'] != 1) {
            return;
        }
        $tournament->config = $newConfig;
    }

    /**
     * @param Tournament $tournament
     * @param int $nextPriority
     */
    public function addTeamsToNextGroups(Tournament $tournament, int $nextPriority): void
    {
        $groups = $tournament->groups->filter(fn (Group $group) => $group->priority == $nextPriority + 1);
        $teams = Collection::make([]);


        foreach ($groups as $group) {
            $teams = $teams->merge($this->groupLogic->getTeamsToNextGroup($group)->map(function (array $arr) {
                return $arr['team_id'];
            }));
        }
        $teams = $teams->map(fn (int $teamId) => Team::find($teamId));
        $groups = $tournament->groups()->where('priority', $nextPriority)->get();
        $this->addTeamsToGroups($groups, $teams);
    }

    /**
     * @param Collection $groups
     * @param Collection $teams
     */
    public function addTeamsToGroups(Collection $groups, Collection $teams): void
    {
        $teamsOnOneGroup = ceil(count($teams) / count($groups));
        foreach ($groups as $key => $group) {
            for ($i = $key * $teamsOnOneGroup; $i < ($key + 1) * $teamsOnOneGroup; $i++) {
                isset($teams[$i]) ? $this->groupOperation->addTeamToGroup($group, $teams[$i]) : null;
            }
        }
    }
}
