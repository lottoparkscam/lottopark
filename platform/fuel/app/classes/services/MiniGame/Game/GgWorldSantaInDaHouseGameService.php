<?php

namespace Services\MiniGame\Game;

use Models\MiniGame;
use Models\WhitelabelUser;
use Services\MiniGame\AbstractMiniGameService;
use Services\MiniGame\Dto\GamePlayResult;
use Services\MiniGame\Interface\MiniGameServiceInterface;
use Services\MiniGame\ValueObject\MiniGameTransactionObject;

class GgWorldSantaInDaHouseGameService extends AbstractMiniGameService implements MiniGameServiceInterface
{
    public const GAME_SLUG = MiniGame::GG_WORLD_SANTA_IN_DA_HOUSE_SLUG;

    public function play(WhitelabelUser $user, string $slug, int $selectedNumber, float $betAmountInEur): GamePlayResult
    {
        $miniGame = $this->miniGameRepository->findOneBySlug($slug);
        if (!$miniGame) {
            return new GamePlayResult(false, self::GAME_NOT_FOUND_CODE);
        }

        $gameData = $this->prepareGamePlay($miniGame, $user, $betAmountInEur);
        if (!$gameData->isSelectionValid($selectedNumber) && !$gameData->isFreeSpin()) {
            return new GamePlayResult(false, self::PLAYER_DID_NOT_CHOOSE_NUMBER_CODE);
        }

        if (!$gameData->hasSufficientBalance() && !$gameData->isFreeSpin()) {
            return new GamePlayResult(false, self::BALANCE_IS_INSUFFICIENT_CODE);
        }

        $systemDrawnNumber = $this->getSystemDrawnNumber($miniGame);
        if ($systemDrawnNumber === null) {
            return new GamePlayResult(false, self::SYSTEM_ERROR_CODE);
        }

        $isWin = $this->determineWin($systemDrawnNumber, $selectedNumber);
        $prizeAmountInEur = $this->calculatePrize($gameData->getBetAmount(), $miniGame->multiplier, $isWin);
        $prizeAmountInUserCurrency = $this->getPrizeAmountInUserCurrency($user, $prizeAmountInEur);

        if (!$isWin && $systemDrawnNumber === $selectedNumber) {
            $systemDrawnNumber = $systemDrawnNumber === 0 ? 1 : 0;
        }

        $additionalData = ['jsonGrid' => $this->generateWinningGrid($systemDrawnNumber)];

        return $this->handleGameTransaction(
            new MiniGameTransactionObject(
                $miniGame,
                $user,
                $gameData->getBetAmount(),
                $prizeAmountInEur,
                $isWin,
                $selectedNumber,
                $systemDrawnNumber,
                $gameData->getPromoCodeUserId(),
                $gameData->getBetAmountInUserCurrency(),
                $prizeAmountInUserCurrency,
                $gameData->isFreeSpin(),
                $additionalData,
            )
        );
    }

    public function generateWinningGrid(int $winningSymbol): array
    {
        $winningLines = [
            [[0, 0], [0, 1], [0, 2]],
            [[1, 0], [1, 1], [1, 2]],
            [[2, 0], [2, 1], [2, 2]],
            [[0, 0], [1, 0], [2, 0]],
            [[0, 1], [1, 1], [2, 1]],
            [[0, 2], [1, 2], [2, 2]],
            [[0, 0], [1, 1], [2, 2]],
            [[0, 2], [1, 1], [2, 0]]
        ];

        $winningLine = $winningLines[array_rand($winningLines)];
        $losingSymbol = 1 - $winningSymbol;

        $grid = [
            [null, null, null],
            [null, null, null],
            [null, null, null]
        ];

        foreach ($winningLine as $position) {
            $row = $position[0];
            $col = $position[1];
            $grid[$row][$col] = $winningSymbol;
        }

        $emptyCells = [];
        for ($row = 0; $row < 3; $row++) {
            for ($col = 0; $col < 3; $col++) {
                if ($grid[$row][$col] === null) {
                    $emptyCells[] = [$row, $col];
                }
            }
        }

        $combinations = $this->getCombinations($emptyCells, 2);
        shuffle($combinations);

        $validGrid = null;
        foreach ($combinations as $combo) {
            $gridToCheck = [];
            foreach ($grid as $row) {
                $gridToCheck[] = $row;
            }

            foreach ($combo as $position) {
                $row = $position[0];
                $col = $position[1];
                $gridToCheck[$row][$col] = $winningSymbol;
            }

            $remainingCells = array_filter($emptyCells, function($cell) use ($combo) {
                foreach ($combo as $c) {
                    if ($cell[0] == $c[0] && $cell[1] == $c[1]) {
                        return false;
                    }
                }
                return true;
            });

            foreach ($remainingCells as $position) {
                $row = $position[0];
                $col = $position[1];
                $gridToCheck[$row][$col] = $losingSymbol;
            }

            $winningSymbolLines = $this->countWinningLines($gridToCheck, $winningSymbol, $winningLines);
            $losingSymbolLines = $this->countWinningLines($gridToCheck, $losingSymbol, $winningLines);

            if ($winningSymbolLines === 1 && $losingSymbolLines === 0) {
                $validGrid = $gridToCheck;
                break;
            }
        }

        if ($validGrid) {
            return $validGrid;
        }

        return [];
    }

    private function getCombinations($arr, $k): array
    {
        $result = [];

        $n = count($arr);
        if ($k > $n) {
            return [];
        }

        if ($k == 0) {
            return [[]];
        }

        for ($i = 0; $i <= $n - $k; $i++) {
            $head = [$arr[$i]];
            $tailCombs = $this->getCombinations(array_slice($arr, $i + 1), $k - 1);
            foreach ($tailCombs as $comb) {
                $result[] = array_merge($head, $comb);
            }
        }

        return $result;
    }

    private function countWinningLines($grid, $symbol, $winningLines): int
    {
        $count = 0;
        foreach ($winningLines as $line) {
            $win = true;
            foreach ($line as $position) {
                $row = $position[0];
                $col = $position[1];
                if ($grid[$row][$col] !== $symbol) {
                    $win = false;
                    break;
                }
            }
            if ($win) {
                $count++;
            }
        }
        return $count;
    }
}
