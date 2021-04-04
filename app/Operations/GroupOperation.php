<?php

declare(strict_types=1);

namespace App\Operations;

use App\Models\Group;
use App\Models\Team;

class GroupOperation
{
    protected $gameOperation;

    /**
     * GroupOperation constructor.
     * @param GameOperation $gameOperation
     */
    public function __construct(GameOperation $gameOperation)
    {
       $this->gameOperation = $gameOperation;
    }

    /**
     * @param Group $group
     * @param Team $team
     */
    public function addTeamToGroup(Group $group, Team $team): void
    {
        if ($this->hasGroupTeam($group, $team)) {
            return;
        }

        $groupTeam = $group->groupTeams()->make();
        $groupTeam->team_id = $team->id;
        $groupTeam->save();
    }

    /**
     * @param Group $group
     * @param Team $team
     * @return bool
     */
    public function hasGroupTeam(Group $group, Team $team): bool
    {
        return $group->groupTeams()->where('team_id', $team->id)->exists();
    }
}
