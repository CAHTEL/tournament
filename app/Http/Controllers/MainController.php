<?php


namespace App\Http\Controllers;

use App\Logic\GroupsLogic\GroupLogic;
use App\Logic\TournamentLogic;
use App\Models\Game;
use App\Models\Group;
use App\Models\Team;
use App\Models\Tournament;
use App\Operations\GroupOperation;
use App\Queries\GroupQueries;
use Illuminate\Support\Collection;

class MainController extends Controller
{
    protected $groupQueries;
    protected $groupLogic;
    protected $groupOperation;

    public function __construct(GroupQueries $groupQueries, GroupLogic $groupLogic, GroupOperation $groupOperation)
    {
        $this->groupQueries = $groupQueries;
        $this->groupLogic = $groupLogic;
        $this->groupOperation = $groupOperation;
    }

    /**
     * @param GroupLogic $logic
     * @param GroupQueries $groupQueries
     */
    public function index(GroupLogic $logic, GroupQueries $groupQueries)
    {
        $tournament = Tournament::all()->last();
        if (is_null($tournament)) {
            return view('generator');
        }
        /** @var Group $group */
        $groupsData = [];
        foreach ($tournament->groups->where('type_id', 2) as $group) {
            $teamGames = $logic->groupScoring($group)->map(function (array $arr) use ($groupQueries, $group, $logic) {
                $team = Team::find($arr['team_id']);
                return $groupQueries
                    ->getAllGamesInGroupForTeam($group, $team)
                    ->map(fn ($obj) => [
                        'team_name' => $team->name,
                        'game_score' => $logic->getFormattedResultForTeam(Game::find($obj->id), $team),
                        'score' => $arr['score'],
                    ]);
            });

            $newTeamGames = Collection::make([]);

            for ($i = 0; $i < $teamGames->count(); $i++) {
                $arr = $teamGames->get($i)->toArray();
                $newArr = array_merge(
                    array_slice($arr, 0, $i),
                    [$i => ['team_name' => $arr[0]['team_name'], 'score' => $arr[0]['score'], 'game_score' => ' ']],
                    array_slice($arr, $i)
                );
                $newTeamGames->push($newArr);
            }

            $teamsNames = $newTeamGames->map(fn (array $arr) => $arr[0]['team_name']);
            $groupsData[] = ['teamsNames' => $teamsNames, 'teamsGames' => $newTeamGames];
        }

        $finalData = [];
        /** @var Group $final */
        $final = $tournament->groups->where('type_id', 1)->first();
        for ($i = 0; $i <= sqrt($final->groupTeams->count()); $i++) {
            $data = $logic->groupScoring($final)->map(function (array $arr) use ($final, $groupQueries, $logic, $i) {
                $team = Team::find($arr['team_id']);
                return $groupQueries->getAllGamesInGroupForTeam($final, $team)
                    ->map(fn ($obj) => [
                        'team_name' => $team->name,
                        'game_score' => $logic->getGameScoreForTeamTest(Game::find($obj->id), $team),
                        'game_id' => $obj->id,
                    ])->get($i);
            })->sortBy('game_id');

            $finalData[] = $data->filter(fn ($data) => !is_null($data));
        }

        $winner = $logic->getNextOpponent($final);
        return view('index', ['groupData' => $groupsData, 'finalData' => $finalData, 'winner' => $winner->name]);
    }

    /**
     * @param TournamentLogic $tournamentLogic
     */
    public function startNewTournament(TournamentLogic $tournamentLogic)
    {
        $teams = Team::factory()->count(16)->create();
        $tournamentLogic->startTournament(...$teams);
        /** @var Tournament $tournament */
        $tournament = Tournament::all()->last();

        $maxPriority = $tournament->groups()->max('priority');

        for ($i = $maxPriority; $i > 0; $i--) {
            $tournamentLogic->addTeamsToNextGroups($tournament, $i);
            $groups = $tournament->groups->filter(fn (Group $group) =>
                $group->priority == $i && !$this->groupLogic->isGroupGamesCompleted($group)
            );
            foreach ($groups as $group) {
                $this->groupLogic->playAllGamesInGroup($group);
            }
        }

        return redirect('/');
    }
}
