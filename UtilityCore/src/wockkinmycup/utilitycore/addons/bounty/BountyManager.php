<?php

namespace wockkinmycup\utilitycore\addons\bounty;

use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;
use cooldogedev\BedrockEconomy\api\legacy\ClosureContext;
use cooldogedev\BedrockEconomy\database\exception\InsufficientFundsException;
use cooldogedev\BedrockEconomy\database\exception\RecordNotFoundException;
use cooldogedev\BedrockEconomy\libs\cooldogedev\libSQL\exception\SQLException;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use wockkinmycup\utilitycore\utils\Utils;
use xtcy\ArcaneCore\Loader;

class BountyManager
{

    protected static Config $bounties;

    public function __construct(Config $bountiesConfig) {
        self::$bounties = $bountiesConfig;
    }

    /**
     * @throws \JsonException
     */
    public static function setBounty(Player $sender, Player $targetPlayer, int $amount): void
    {
        $playerName = strtolower($targetPlayer->getName());
        $currentBounty = self::$bounties->get($playerName, 0);
        $newBounty = $currentBounty + $amount;
        if ($newBounty < 0) {
            $newBounty = 0;
        }

        BedrockEconomyAPI::CLOSURE()->subtract(
            $sender->getXuid(),
            $sender->getName(),
            $amount,
            0,
            static function () use($playerName, $newBounty): void {
                self::$bounties->set($playerName, $newBounty);
                self::$bounties->save();
            },
            static function (SQLException $exception) use($sender): void {
                if ($exception instanceof RecordNotFoundException) {
                    $sender->sendMessage(TextFormat::colorize("&r&l&f<&6Bounty&f>&r&c Player not found or is not online."));
                    return;
                }

                if ($exception instanceof InsufficientFundsException) {
                    $sender->sendMessage(TextFormat::colorize("&r&l&f<&6Bounty&f>&r&c You do not have sufficient funds."));
                    return;
                }
                echo 'An error occurred while updating the balance.';
            }
        );
    }

    public static function getBounty(string $playerName) {
        $playerName = strtolower($playerName);
        return self::$bounties->get($playerName, 0);
    }

    /**
     * @throws \JsonException
     */
    public static function removeBounty(string $playerName): void
    {
        $playerName = strtolower($playerName);
        if (self::$bounties->exists($playerName)) {
            self::$bounties->remove($playerName);
            self::$bounties->save();
        }
    }

    public static function claimBounty(Player $killer, string $targetPlayerName) {
        $targetPlayerName = strtolower($targetPlayerName);
        $bountyAmount = self::getBounty($targetPlayerName);

        if ($bountyAmount > 0) {
            $taxPercentage = Utils::getConfiguration(Loader::getInstance(), "config.yml")->get("tax")["bounty"];
            $taxAmount = ($taxPercentage / 100) * $bountyAmount;
            $finalAmount = $bountyAmount - $taxAmount;

            BedrockEconomyAPI::CLOSURE()->add(
                xuid: $killer->getXuid(),
                username: $killer->getName(),
                amount: $finalAmount,
                decimals: 00,
                onSuccess: static function () use($targetPlayerName): void {
                    $this->removeBounty($targetPlayerName);

                },
                onError: static function (SQLException $exception): void {
                    if ($exception instanceof RecordNotFoundException) {
                        echo 'Account not found';
                        return;
                    }

                    echo 'An error occurred while updating the balance.';
                }
            );

            return $finalAmount;
        } else {
            return 0;
        }
    }
}