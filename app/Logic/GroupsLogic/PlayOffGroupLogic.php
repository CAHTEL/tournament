<?php
declare(strict_types=1);


namespace App\Logic\GroupsLogic;

use App\Models\Game;
use App\Models\Group;
use App\Models\GroupGame;
use App\Models\GroupTeam;
use App\Models\Team;
use Illuminate\Support\Collection;

class PlayOffGroupLogic extends AbstractGroupLogic
{
    /**
     * @param Group $group
     * @param Team|null $team
     * @return Team|null
     */
    public function getNextOpponent(Group $group, ?Team $team = null): ?Team
    {
        $groupTeams = $group->groupTeams;

        $groupsWithoutLose = $groupTeams->filter(function (GroupTeam $groupTeam) use ($team) {
            return $this->getGamesStatus($groupTeam->group, $groupTeam->team)['lose'] == 0
                && (is_null($team) || $groupTeam->team_id != $team->id);
        });

        $teamsGamesCount = $groupsWithoutLose->map(function (GroupTeam $groupTeam) {
            $count = $this->groupQueries->getAllGamesInGroupForTeam($groupTeam->group, $groupTeam->team)->count();
            return ['team' => $groupTeam->team, 'count' => $count];
        });

        if ($teamsGamesCount->isEmpty()) {
            return null;
        }

        return $teamsGamesCount->sortBy('count')->first()['team'];
    }

    /**
     * @param Group $group
     * @param Team $team
     * @return array
     */
    public function getGamesStatus(Group $group, Team $team): array
    {
        $allGames = $this->groupQueries->getAllGamesInGroupForTeam($group, $team)->pluck('id');
        $resultsStatus = ['win' => 0, 'lose' => 0, 'draw' => 0];

        foreach ($allGames as $gameID) {
            $results = $this->getResultForTeam(Game::find($gameID), $team);
            if ($results['team_score'] > $results['opponent_score']) {
                $resultsStatus['win']++;
            } else if ($results['team_score'] == $results['opponent_score']) {
                $resultsStatus['draw']++;
            } else {
                $resultsStatus['lose']++;
            }
        }

        return $resultsStatus;
    }

    /**
     * @param Group $group
     * @return bool
     */
    public function isGroupGamesCompleted(Group $group): bool
    {
        $team = $this->getNextOpponent($group);
        return is_null($this->getNextOpponent($group, $team));
    }

    /**
     * @param Group $group
     * @return Collection
     */
    public function getTeamsToNextGroup(Group $group): Collection
    {
        return $this->groupScoring($group)->take(static::COUNT_TO_NEXT_GROUP_DEFAULT);
    }

    /**
     * @param Group $group
     * @return Collection
     */
    public function groupScoring(Group $group): Collection
    {
        $result = $group->groupTeams->map(function (GroupTeam $groupTeam) use ($group) {
            $count = $this->groupQueries->getAllGamesInGroupForTeam($groupTeam->group, $groupTeam->team)->count();
            return ['team_id' => $groupTeam->team_id, 'game_count' => $count];
        });

        return $result->sortBy('game_count');
    }

    /**
     * @param Group $group
     * @return Collection
     */
    public function groupScoringTest(Group $group): Collection
    {
        $result = $group->groupTeams->map(function (GroupTeam $groupTeam) use ($group) {
            $allGames = $this->groupQueries->getAllGamesInGroupForTeam($groupTeam->group, $groupTeam->team)->first();
            return ['team_id' => $groupTeam->team_id, 'opponent_id' => $allGames];
        });


        return $result->sortBy('game_count');
    }

    /**
     * @param Group $group
     * @param Team $teamA
     * @param Team $teamB
     * @return void
     */
    public function playGameInGroup(Group $group, Team $teamA, Team $teamB): void
    {
        /** @var GroupGame $groupGame */
        $groupGame = $group->groupGames()->make();
        $game = $this->gameOperation->playGame($teamA, $teamB);

        if ($this->gameOperation->isDraw($game)) {
            $this->gameOperation->playOverTime($game);
        }

        $groupGame->game_id = $game->id;
        $groupGame->save();
    }
}
