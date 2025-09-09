<?php

namespace wockkinmycup\LuckyPouches\utils;

use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\Server;
use pocketmine\utils\TextFormat as C;
use wockkinmycup\LuckyPouches\Loader;

class PouchItem {

    public static function getPouchType(string $identifier, int $amount = 1): ?Item {
        $config = yaml_parse(file_get_contents(Loader::getInstance()->getDataFolder() . "config.yml"));
        $pouchData = $config['pouches'][$identifier];
        $item = StringToItemParser::getInstance()->parse($pouchData['material'])->setCount($amount);
        $item->setCustomName(C::colorize($pouchData['name']));

        if (isset($pouchData['lore']) && is_array($pouchData['lore'])) {
            $lore = [];
            foreach ($pouchData['lore'] as $line) {
                $color = C::colorize($line);
                $lore[] = $color;
            }
            $item->setLore($lore);
        }

        $item->getNamedTag()->setString("luckypouches", $identifier);

        return $item;
    }
}