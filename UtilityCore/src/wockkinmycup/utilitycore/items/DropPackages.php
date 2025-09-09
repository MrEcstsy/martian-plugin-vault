<?php

namespace wockkinmycup\utilitycore\items;

use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\utils\TextFormat;
use wockkinmycup\utilitycore\Loader;
use wockkinmycup\utilitycore\utils\Utils;

class DropPackages
{

    public static function give(string $type, int $amount = 1): ?Item {
        $config = Utils::getConfiguration(Loader::getInstance(), "config.yml");
        $item = VanillaItems::AIR()->setCount($amount);
        switch (strtolower($type)) {
            case "simple":
                $item = VanillaBlocks::CHEST()->asItem()->setCount($amount);

                $name = "&r&l&fSimple " . $config->get("server-name") . " Chest";
                $item->setCustomName(TextFormat::colorize($name));

                $item->setLore([
                    TextFormat::colorize('&r&7A cache of equipment packaged by'),
                    TextFormat::colorize('&r&7the Astral Ethereal Base.')
                ]);

                $item->getNamedTag()->setString("drop_package", "simple");
                break;
            case "unique":
                $item = VanillaBlocks::CHEST()->asItem()->setCount($amount);

                $name = "&r&l&aUnique " . $config->get("server-name") . " Chest";
                $item->setCustomName(TextFormat::colorize($name));

                $item->setLore([
                    TextFormat::colorize('&r&7A cache of equipment packaged by'),
                    TextFormat::colorize('&r&7the Astral Ethereal Base.')
                ]);

                $item->getNamedTag()->setString("drop_package", "unique");
                break;
            case "elite":
                $item = VanillaBlocks::CHEST()->asItem()->setCount($amount);

                $name = "&r&l&bElite " . $config->get("server-name") . " Chest";
                $item->setCustomName(TextFormat::colorize($name));

                $item->setLore([
                    TextFormat::colorize('&r&7A cache of equipment packaged by'),
                    TextFormat::colorize('&r&7the Astral Ethereal Base.')
                ]);

                $item->getNamedTag()->setString("drop_package", "elite");
                break;
            case "ultimate":
                $item = VanillaBlocks::CHEST()->asItem()->setCount($amount);

                $name = "&r&l&eUltimate " . $config->get("server-name") . " Chest";
                $item->setCustomName(TextFormat::colorize($name));

                $item->setLore([
                    TextFormat::colorize('&r&7A cache of equipment packaged by'),
                    TextFormat::colorize('&r&7the Astral Ethereal Base.')
                ]);

                $item->getNamedTag()->setString("drop_package", "ultimate");
                break;
            case "legendary":
                $item = VanillaBlocks::CHEST()->asItem()->setCount($amount);

                $name = "&r&l&6Legendary " . $config->get("server-name") . " Chest";
                $item->setCustomName(TextFormat::colorize($name));

                $item->setLore([
                    TextFormat::colorize('&r&7A cache of equipment packaged by'),
                    TextFormat::colorize('&r&7the Astral Ethereal Base.')
                ]);

                $item->getNamedTag()->setString("drop_package", "legendary");
                break;
            case "godly":
                $item = VanillaBlocks::CHEST()->asItem()->setCount($amount);

                $name = "&r&l&cGodly " . $config->get("server-name") . " Chest";
                $item->setCustomName(TextFormat::colorize($name));

                $item->setLore([
                    TextFormat::colorize('&r&7A cache of equipment packaged by'),
                    TextFormat::colorize('&r&7the Astral Ethereal Base.')
                ]);

                $item->getNamedTag()->setString("drop_package", "godly");
                break;
        }
        return $item;
    }
}