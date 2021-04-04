<?php


namespace App\Logic\GroupsLogic;


use App\Models\Group;
use App\Models\Team;
use App\Models\Type;
use Illuminate\Support\Collection;

class GroupLogic extends AbstractGroupLogic
{
    /**
     * @param Group $group
     * @param Team|null $team
     * @return Team|null
     */
    public function getNextOpponent(Group $group, ?Team $team = null): ?Team
    {
        return $this->getLogic($group)->getNextOpponent($group, $team);
    }

    /**
     * @param Group $group
     * @return Collection
     */
    public function groupScoring(Group $group): Collection
    {
        return $this->getLogic($group)->groupScoring($group);
    }

    /**
     * @param Group $group
     * @return bool
     */
    public function isGroupGamesCompleted(Group $group): bool
    {
        return $this->getLogic($group)->isGroupGamesCompleted($group);
    }

    /**
     * @param Group $group
     * @param Team $teamA
     * @param Team $teamB
     * @return void
     */
    public function playGameInGroup(Group $group, Team $teamA, Team $teamB): void
    {
        $this->getLogic($group)->playGameInGroup($group, $teamA, $teamB);
    }

    /**
     * @param Group $group
     * @return AbstractGroupLogic
     */
    protected function getLogic(Group $group): AbstractGroupLogic
    {
        switch ($group->type->name) {
            case Type::PLAY_OFF:
                return resolve(PlayOffGroupLogic::class);
            case Type::BEST_BY_SCORE:
                return resolve(BestByScoreGroupLogic::class);
        }
    }
}
