<?php

namespace wockkinmycup\utilitycore\managers;

use pocketmine\player\Player;

class PlayerClassManager  {

    public static function setPlayerClass(Player $player, string $className): void
    {
        $dataFolder = $player->getServer()->getDataPath() . 'player_data/';
        if (!is_dir($dataFolder)) {
            @mkdir($dataFolder);
        }

        $playerDataFile = $dataFolder . strtolower($player->getName()) . '.json';
        file_put_contents($playerDataFile, json_encode(['class' => $className]));
    }

    public static function getPlayerClass(Player $player): string
    {
        $dataFolder = $player->getServer()->getDataPath() . 'player_data/';
        $playerDataFile = $dataFolder . strtolower($player->getName()) . '.json';

        if (file_exists($playerDataFile)) {
            $playerData = json_decode(file_get_contents($playerDataFile), true);
            return $playerData['class'] ?? 'default';
        }

        return 'default';
    }
}
