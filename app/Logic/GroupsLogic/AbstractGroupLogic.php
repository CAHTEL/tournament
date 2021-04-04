<?php
declare(strict_types=1);

namespace App\Logic\GroupsLogic;

use App\Models\Game;
use App\Models\GameResult;
use App\Models\Group;
use App\Models\Team;
use App\Operations\GameOperation;
use App\Queries\GroupQueries;
use Illuminate\Support\Collection;

abstract class AbstractGroupLogic
{
    protected $groupQueries;
    protected $gameOperation;

    public const COUNT_TO_NEXT_GROUP_DEFAULT = 4;

    /**
     * AbstractGroupLogic constructor.
     * @param GroupQueries $groupQueries
     * @param GameOperation $gameOperation
     */
    public function __construct(GroupQueries $groupQueries, GameOperation $gameOperation)
    {
        $this->groupQueries = $groupQueries;
        $this->gameOperation = $gameOperation;
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
     * @return void
     */
    public function playAllGamesInGroup(Group $group): void
    {
        while (!$this->isGroupGamesCompleted($group)) {
            $teamA = $this->getNextOpponent($group);
            $teamB = $this->getNextOpponent($group, $teamA);
            if (!is_null($teamB)) {
                $this->playGameInGroup($group, $teamA, $teamB);
            }
            $teamA->refresh();
        }
    }

    /**
     * @param Game $game
     * @param Team $team
     * @return array
     */
    public function getResultForTeam(Game $game, Team $team): array
    {
        $gameResults = $game->gameResults->map(function (GameResult $gameResult) use ($team) {
            $key = $gameResult->team_id == $team->id ? 'team_score' : 'opponent_score';
            return [$key => $gameResult->score];
        });

        return array_merge($gameResults->toArray()[0], $gameResults->toArray()[1]);
    }

    /**
     * @param Game $game
     * @param Team $team
     * @return string
     */
    public function getFormattedResultForTeam(Game $game, Team $team): string
    {
        $gameResults = $game->gameResults->map(function (GameResult $gameResult) use ($team) {
            return [$gameResult->team->name => $gameResult->score];
        });

        $array = array_merge($gameResults->toArray()[0], $gameResults->toArray()[1]);
        uksort($array, fn ($first, $second) => $first == $team->name ? -1 : 1);
        return implode(':', $array);
    }

    /**
     * @param Game $game
     * @param Team $team
     * @return int
     */
    public function getGameScoreForTeamTest(Game $game, Team $team): int
    {
        foreach ($game->gameResults as $gameResult) {
            if ($gameResult->team_id == $team->id) {
                return $gameResult->score;
            }
        }

        return -1;
    }

    /**
     * @param Group $group
     * @param Team|null $team
     * @return Team|null
     */
    abstract public function getNextOpponent(Group $group, ?Team $team = null): ?Team;

    /**
     * @param Group $group
     * @return bool
     */
    abstract public function isGroupGamesCompleted(Group $group): bool;

    /**
     * @param Group $group
     * @param Team $teamA
     * @param Team $teamB
     * @return void
     */
    abstract public function playGameInGroup(Group $group, Team $teamA, Team $teamB): void;
}
