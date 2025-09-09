<?php

namespace wockkinmycup\DynamicCombat\utils;

use pocketmine\player\Player;
use wockkinmycup\DynamicCombat\CombatListener;

class Utils {

    public static function hasAbilityActive(Player $player, string $abilityKey): bool
    {
        return isset (CombatListener::$activeAbilities[$player->getName()][$abilityKey]);
    }
}