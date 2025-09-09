<?php

namespace wockkinmycup\utilitycore\addons\pets;

use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\utils\TextFormat as C;

use wockkinmycup\utilitycore\Loader as UtilityLoader;
use wockkinmycup\utilitycore\utils\Utils;

class BlessPet {

    public int $amount = 1;

    public static function give($amount): ?Item {
        $config = Utils::getConfiguration(UtilityLoader::getInstance(), "pets.yml");
        $item = StringToItemParser::getInstance()->parse($config->getNested("pets.bless.item"))->setCount($amount);

        $name = $config->getNested("pets.bless.name");
        $item->setCustomName(C::RESET . C::colorize($name));

        $lore = [];
        foreach ($config->getNested("pets.bless.lore") as $line) {
            $color = C::RESET . C::colorize($line);
            $lore[] = $color;
        }
        $item->setLore($lore);

        $item->getNamedTag()->setString("pets", "bless");
        return $item;
    }
}