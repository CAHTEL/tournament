<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\Group;
use App\Models\Team;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GroupQueries
{
    /**
     * @param Group $group
     * @param Team $team
     * @return Collection
     */
    public function getAllOpponents(Group $group, Team $team): Collection
    {
        $gamesIds = $this->getAllGamesInGroupForTeam($group, $team)->pluck('id');

        return DB::table('games')
            ->join('game_results', 'games.id', 'game_results.game_id')
            ->whereIn('games.id', $gamesIds)
            ->where('game_results.team_id', '!=', $team->id)
            ->get('team_id');
    }

    /**
     * @param Group $group
     * @param Team $team
     * @return Collection
     */
    public function getAllGamesInGroupForTeam(Group $group, Team $team): Collection
    {
        return DB::table('games')
            ->join('group_games', function (JoinClause $join) use ($group) {
                $join->on(
                    'games.id',
                    '=',
                    'group_games.game_id'
                )->where('group_games.group_id', $group->id);
            })
            ->join('game_results', 'games.id', 'game_results.game_id')
            ->where('team_id', $team->id)
            ->get('games.id');
    }
}
