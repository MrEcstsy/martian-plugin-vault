<?php

namespace wockkinmycup\enchantfragments\utils;

use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;
use pocketmine\utils\TextFormat as C;
use wockkinmycup\enchantfragments\Loader;

class Utils {

    public static function createEnchantFragment(int $tier, int $amount = 1): ?Item {
        $item = VanillaItems::AIR()->setCount($amount);
        $config = Loader::getInstance()->getConfig();
        switch ($tier) {
            case 0:
                $item = StringToItemParser::getInstance()->parse($config->getNested("fragments.unbreaking.item"));
                $item->setCustomName(C::colorize($config->getNested("fragments.unbreaking.name")));
                $lore = [];
                foreach ($config->getNested("fragments.unbreaking.lore") as $line) {
                    $color = C::colorize($line);
                    $lore[] = $color;
                }
                $item->setLore($lore);

                $item->getNamedTag()->setString("enchantmentfragment", "unbreakingv");
                $item->getNamedTag()->setInt("enchantmentfragmenttier", 0);
                break;
            case 1:
                $item = VanillaItems::REDSTONE_DUST()->setCount($amount);
                $item->setCustomName("§r§l§cEnchantment Fragment [§r§7Thorns III§l§c]");
                $item->setLore([
                    "§r§7Drag n' Drop on a pair of",
                    "§r§c'§7armor§c'§7 to enchant §cThorns III§7."
                ]);
                $item->getNamedTag()->setString("enchantmentfragment", "thornsiii");
                $item->getNamedTag()->setInt("enchantmentfragmenttier", 1);
                break;
            case 2:
                $item = VanillaItems::LAPIS_LAZULI()->setCount($amount);
                $item->setCustomName("§r§l§bEnchantment Fragment [§r§7Depth Strider III§l§b]");
                $item->setLore([
                    "§r§7Drag n' Drop on a pair of",
                    "§r§b'§7armor§b'§7 to enchant §bDepth Strider III§7."
                ]);

                $item->getNamedTag()->setString("enchantmentfragment", "depthstrideriii");
                $item->getNamedTag()->setInt("enchantmentfragmenttier", 2);
                break;
            case 3:
                $item = VanillaItems::GOLD_INGOT()->setCount($amount);
                $item->setCustomName("§r§l§bEnchantment Fragment [§r§dLooting V§l§b]");
                $item->setLore([
                    "§r§7Drag n' Drop on a pair of",
                    "§r§b'§7weapon§b'§7 to enchant §dLooting V§7."
                ]);
                $item->getNamedTag()->setString("enchantmentfragment", "lootingv");
                $item->getNamedTag()->setInt("enchantmentfragmenttier", 3);
                break;
        }
        return $item;
    }
}