<?php

namespace xtcy\bn\utils;

use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\utils\TextFormat as C;
use xtcy\bn\Loader;

class Notes
{

    public static function getNoteType(string $identifier, int $amount = 1): ?Item {
        $config = yaml_parse(file_get_contents(Loader::getInstance()->getDataFolder() . "config.yml"));
        $noteData = $config['notes'][$identifier];
        $item = StringToItemParser::getInstance()->parse($noteData['material'])->setCount($amount);
        $item->setCustomName(C::colorize($noteData['name']));

        if (isset($noteData['lore']) && is_array($noteData['lore'])) {
            $lore = [];
            foreach ($noteData['lore'] as $line) {
                $color = C::colorize($line);
                $lore[] = $color;
            }
            $item->setLore($lore);
        }

        $item->getNamedTag()->setString("notes", $identifier);

        return $item;
    }
}
