<?php

declare(strict_types=1);

namespace App\Operations;

use App\Models\Game;
use App\Models\GameResult;
use App\Models\Team;
use Illuminate\Support\Facades\DB;

class GameOperation
{
    /**
     * @param Team $teamA
     * @param Team $teamB
     * @return Game
     */
    public function playGame(Team $teamA, Team $teamB): Game
    {
        ['teamAScore' => $teamAScore, 'teamBScore' => $teamBScore] = $this->getGameScore($teamA, $teamB);
        $game = new Game();

        DB::transaction(
            function () use ($teamA, $teamB, $teamAScore, $teamBScore, $game) {
                $game->save();

                $game->gameResults()->create([
                    'team_id' => $teamA->id,
                    'score' => $teamAScore
                ]);

                $game->gameResults()->create([
                    'team_id' => $teamB->id,
                    'score' => $teamBScore
                ]);
            });

        return $game;
    }

    /**
     * @param Game $game
     */
    public function playOverTime(Game $game): void
    {
        [$teamA, $teamB] = $game->gameResults->map(function (GameResult $gameResult) {
            return ['game_result_id' => $gameResult->id, 'team' => $gameResult->team];
        });

        ['teamAScore' => $teamAScore, 'teamBScore' => $teamBScore] = $this->getGameScore($teamA['team'], $teamB['team']);

        while ($teamAScore == $teamBScore) {
            ['teamAScore' => $teamAScore, 'teamBScore' => $teamBScore] = $this->getGameScore($teamA['team'], $teamB['team']);
        }

        $teamAGameResult = GameResult::find($teamA['game_result_id']);
        $teamAGameResult->score = $teamAGameResult->score + $teamAScore;

        $teamBGameResult = GameResult::find($teamB['game_result_id']);
        $teamBGameResult->score = $teamBGameResult->score + $teamBScore;

        DB::transaction(
            function () use ($teamAGameResult, $teamBGameResult) {
                $teamAGameResult->save();
                $teamBGameResult->save();
            });
    }

    /**
     * @param Game $game
     * @return Team|null
     */
    public function getGameWinner(Game $game): ?Team
    {
        $gameResults = $game->gameResults;
        $maxScore = $gameResults->max('score');
        $gameResultsWithBestScore = $gameResults->filter(function (GameResult $gameResults) use ($maxScore) {
            return $gameResults->score >= $maxScore;
        });

        if ($gameResultsWithBestScore->count() == 1) {
            return $gameResultsWithBestScore->first()->team;
        }

        return null;
    }

    /**
     * @param Game $game
     * @return bool
     */
    public function isDraw(Game $game): bool
    {
        [$scoreTeamA, $scoreTeamB] = $game->gameResults->map(function (GameResult $gameResult) {
            return $gameResult->score;
        });

        return $scoreTeamA == $scoreTeamB;
    }

    /**
     * todo add game scoring depending on the configuration of each team
     * @param Team $teamA
     * @param Team $teamB
     * @return array
     */
    protected function getGameScore(Team $teamA, Team $teamB): array
    {
        return ['teamAScore' => rand(0, 5), 'teamBScore' => rand(0, 5)];
    }
}
