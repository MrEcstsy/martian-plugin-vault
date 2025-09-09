<?php

namespace wockkinmycup\advancedbounty\Utils;

use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;
use cooldogedev\BedrockEconomy\api\legacy\ClosureContext;
use pocketmine\player\Player;
use pocketmine\utils\Config;

class BountyManager {

    protected Config $bounties;

    public function __construct(Config $bountiesConfig) {
        $this->bounties = $bountiesConfig;
    }

    /**
     * @throws \JsonException
     */
    public function setBounty(Player $sender, Player $targetPlayer, int $amount): void
    {
        $playerName = strtolower($targetPlayer->getName());
        $currentBounty = $this->bounties->get($playerName, 0);
        $newBounty = $currentBounty + $amount;
        if ($newBounty < 0) {
            $newBounty = 0;
        }

        BedrockEconomyAPI::legacy()->subtractFromPlayerBalance(
            $sender->getName(),
            $amount,
            ClosureContext::create(
                function (bool $wasUpdated) use($playerName, $newBounty): void {
                    $this->bounties->set($playerName, $newBounty);
                    $this->bounties->save();
                },
            )
        );
    }

    public function getBounty(string $playerName) {
        $playerName = strtolower($playerName);
        return $this->bounties->get($playerName, 0);
    }

    /**
     * @throws \JsonException
     */
    public function removeBounty(string $playerName): void
    {
        $playerName = strtolower($playerName);
        if ($this->bounties->exists($playerName)) {
            $this->bounties->remove($playerName);
            $this->bounties->save();
        }
    }

    public function claimBounty(Player $killer, string $targetPlayerName) {
        $targetPlayerName = strtolower($targetPlayerName);
        $bountyAmount = $this->getBounty($targetPlayerName);

        if ($bountyAmount > 0) {
            $taxPercentage = Utils::getConfigurations()->get("tax")["bounty"];
            $taxAmount = ($taxPercentage / 100) * $bountyAmount;
            $finalAmount = $bountyAmount - $taxAmount;

            BedrockEconomyAPI::legacy()->addToPlayerBalance(
                $killer->getName(),
                $finalAmount,
                ClosureContext::create(
                    function (bool $wasUpdated) use ($targetPlayerName): void {
                        $this->removeBounty($targetPlayerName);
                    },
                )
            );

            return $finalAmount;
        } else {
            return 0;
        }
    }
}