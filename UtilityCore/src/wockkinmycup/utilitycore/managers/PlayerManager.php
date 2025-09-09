<?php

namespace wockkinmycup\utilitycore\managers;

use pocketmine\player\Player;

class PlayerManager {

    public static function setPlayerClass(Player $player, string $className): void
    {
        PlayerClassManager::setPlayerClass($player, $className);
    }

    public static function getPlayerClass(Player $player): string {
        return PlayerClassManager::getPlayerClass($player);
    }
}
