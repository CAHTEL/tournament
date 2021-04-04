<?php
declare(strict_types=1);


namespace App\Logic\GroupsLogic;

use App\Models\Game;
use App\Models\GameResult;
use App\Models\Group;
use App\Models\GroupGame;
use App\Models\GroupTeam;
use App\Models\Team;
use Illuminate\Support\Collection;

class BestByScoreGroupLogic extends AbstractGroupLogic
{
    /**
     * @param Group $group
     * @param Team|null $team
     * @return Team|null
     */
    public function getNextOpponent(Group $group, ?Team $team = null): ?Team
    {
        $groupTeams = $group->groupTeams;
        $maxGames = $groupTeams->count() - 1;

        $allOpponents = !is_null($team) ?
            $this->groupQueries->getAllOpponents($group, $team)->pluck('team_id')->push($team->id) :
            Collection::make([]);

        $teamsGamesCount = $groupTeams->map(function (GroupTeam $groupTeam) {
            $count = $this->groupQueries->getAllGamesInGroupForTeam($groupTeam->group, $groupTeam->team)->count();
            return ['team' => $groupTeam->team, 'count' => $count];
        });

        $teamsGamesCount = $teamsGamesCount->filter(function (array $teamGamesCount) use ($maxGames, $allOpponents) {
            return $teamGamesCount['count'] < $maxGames && !$allOpponents->contains($teamGamesCount['team']->id);
        });

        if ($teamsGamesCount->isEmpty()) {
            return null;
        }
        return $teamsGamesCount->sortBy('count')->first()['team'];
    }

    /**
     * @param Group $group
     * @return Collection
     */
    public function groupScoring(Group $group): Collection
    {
        $result = $group->groupTeams->map(function (GroupTeam $groupTeam) use ($group) {
            return ['team_id' => $groupTeam->team_id, 'score' => $this->teamScoring($group, $groupTeam->team)];
        });

        return $result->sortByDesc('score');
    }

    /**
     * @param Group $group
     * @param Team $team
     * @return int
     */
    public function teamScoring(Group $group, Team $team): int
    {
        $allGames = $this->groupQueries->getAllGamesInGroupForTeam($group, $team)->pluck('id');
        $resultScore = 0;

        foreach ($allGames as $gameID) {
            /** @var Game $game */
            $results = $this->getResultForTeam(Game::find($gameID), $team);
            if ($results['team_score'] > $results['opponent_score']) {
                $resultScore += 3;
            } else if ($results['team_score'] == $results['opponent_score']) {
                $resultScore++;
            }
        }
        return $resultScore;
    }

    /**
     * @param Group $group
     * @return bool
     */
    public function isGroupGamesCompleted(Group $group): bool
    {
        return is_null($this->getNextOpponent($group));
    }

    /**
     * @param Group $group
     * @return Collection
     */
    public function getTeamsToNextGroup(Group $group): Collection
    {
        return $this->groupScoring($group)->take(self::COUNT_TO_NEXT_GROUP_DEFAULT);
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
        $groupGame->game_id = $game->id;
        $groupGame->save();
    }
}
