<?php

namespace wockkinmycup\utilitycore\addons\classes;

use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\player\Player;
use wockkinmycup\utilitycore\managers\PlayerClassManager;

class Wizard extends PlayerClassManager
{
    public int $health = 80;
    public int $damage = 8;
    public string $ability = "Fireball";

    public function castAbility(Player $player, Entity $target = null): void
    {
        if ($target instanceof Living) {
            $fireballDamage = 20;
            $target->attack(new EntityDamageByEntityEvent($player, $target, EntityDamageEvent::CAUSE_MAGIC, $fireballDamage));
            $player->sendMessage("You cast Fireball!");
        } else {
            $player->sendMessage("No target in range for Fireball.");
        }
    }
}