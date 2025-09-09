<?php

namespace wockkinmycup\advancedbounty;

use cooldogedev\BedrockEconomy\BedrockEconomy;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use wockkinmycup\advancedbounty\Utils\BountyManager;
use wockkinmycup\advancedbounty\Utils\Utils;

class BountyListener implements Listener {

    public function onCollectBounty(PlayerDeathEvent $e) {
        $victim = $e->getPlayer();
        $cause = $victim->getLastDamageCause();
        $bountyManager = new BountyManager(Utils::getConfigurations("bounty"));
        if ($cause instanceof EntityDamageByEntityEvent) {
            $killer = $cause->getDamager();
            if ($killer instanceof Player) {
                $victimName = $victim->getName();
                $victimBounty = $bountyManager->getBounty($victim->getName());
                if ($victimBounty > 0) {
                    $claimedAmount = $bountyManager->claimBounty($killer, $victimName);
                    $symbol = BedrockEconomy::getInstance()->getCurrencyManager()->getSymbol();
                    $message = Utils::getConfigurations("messages")->get("broadcasts.claimed-bounty", "§r§a{killer} §7claimed a bounty of §a§l{currency_symbol}{amount} §r§7from §a{victim}!");
                    $message = str_replace(["{killer}", "{currency_symbol}", "{amount}", "{victim}"], [$killer->getName(), $symbol, $claimedAmount, $victimName], $message);
                    $killer->getServer()->broadcastMessage(TextFormat::colorize($message));
                }
            }
        }
    }
}