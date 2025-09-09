<?php

namespace wockkinmycup\DynamicCombat\items;

use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;
use wockkinmycup\DynamicCombat\Loader;
use wockkinmycup\utilitycore\utils\Utils;
use pocketmine\utils\TextFormat as C;

class Armory {

    public static function get(string $type, int $amount = 1): ?Item {
        $item = VanillaItems::AIR()->setCount($amount);
        $cfg = Utils::getConfiguration(Loader::getInstance(), "items.yml");

        switch ($type) {
            case "poison":
                $item = StringToItemParser::getInstance()->parse($cfg->getNested("weapons.poison.item"));

                $name = $cfg->getNested("weapons.poison.name");
                $item->setCustomName(C::colorize($name));

                $lorecfg = $cfg->getNested("weapons.poison.lore");
                $lore = [];
                foreach ($lorecfg as $line) {
                    $color = C::colorize($line);
                    $lore[] = $color;
                }
                $item->setLore($lore);

                $item->getNamedTag()->setString("dynamic_weapon", "poison");
                break;
            case "frostbite":
                $item = StringToItemParser::getInstance()->parse($cfg->getNested("weapons.frostbite.item"));

                $name = $cfg->getNested("weapons.frostbite.name");
                $item->setCustomName(C::colorize($name));

                $lorecfg = $cfg->getNested("weapons.frostbite.lore");
                $lore = [];
                foreach ($lorecfg as $line) {
                    $color = C::colorize($line);
                    $lore[] = $color;
                }
                $item->setLore($lore);

                $item->getNamedTag()->setString("dynamic_weapon", "frostbite");
                break;
        }
        return $item;
    }
}