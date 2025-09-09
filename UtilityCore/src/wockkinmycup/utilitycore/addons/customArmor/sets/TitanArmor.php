<?php

namespace wockkinmycup\utilitycore\addons\customArmor\sets;

use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;
use pocketmine\utils\TextFormat as C;
use wockkinmycup\utilitycore\Loader;
use wockkinmycup\utilitycore\utils\Utils;

class TitanArmor {

    public static function give(string $piece, int $amount = 1): ?Item {
        $item = VanillaItems::AIR();
        $config = Utils::getConfiguration(Loader::getInstance(), "customarmor.yml");

        switch (strtolower($piece)) {
            case "helmet":
                $item = StringToItemParser::getInstance()->parse($config->getNested("sets.titan.helmet.item"))->setCount($amount);

                $name = $config->getNested("sets.titan.helmet.name");
                $item->setCustomName(C::RESET . C::colorize($name));

                $lore = [];
                $setBonusLines = $config->getNested('sets.titan.set-bonus');
                foreach ($config->getNested("sets.titan.helmet.lore") as $line) {
                    $lore[] = C::RESET . C::colorize(str_replace('{set_bonus}', implode(PHP_EOL, $setBonusLines), $line));
                }
                $item->setLore($lore);
                $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3));
                $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4));

                $item->getNamedTag()->setString("customarmor", "titan");
                break;
            case "chestplate":
                $item = StringToItemParser::getInstance()->parse($config->getNested("sets.titan.chestplate.item"))->setCount($amount);

                $name = $config->getNested("sets.titan.chestplate.name");
                $item->setCustomName(C::RESET . C::colorize($name));

                $lore = [];
                $setBonusLines = $config->getNested('sets.titan.set-bonus');
                foreach ($config->getNested("sets.titan.chestplate.lore") as $line) {
                    $lore[] = C::RESET . C::colorize(str_replace('{set_bonus}', implode(PHP_EOL, $setBonusLines), $line));
                }
                $item->setLore($lore);
                $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3));
                $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4));

                $item->getNamedTag()->setString("customarmor", "titan");
                break;
            case "leggings":
                $item = StringToItemParser::getInstance()->parse($config->getNested("sets.titan.leggings.item"))->setCount($amount);

                $name = $config->getNested("sets.titan.leggings.name");
                $item->setCustomName(C::RESET . C::colorize($name));

                $lore = [];
                $setBonusLines = $config->getNested('sets.titan.set-bonus');
                foreach ($config->getNested("sets.titan.leggings.lore") as $line) {
                    $lore[] = C::RESET . C::colorize(str_replace('{set_bonus}', implode(PHP_EOL, $setBonusLines), $line));
                }
                $item->setLore($lore);
                $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3));
                $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4));

                $item->getNamedTag()->setString("customarmor", "titan");
                break;
            case "boots":
                $item = StringToItemParser::getInstance()->parse($config->getNested("sets.titan.boots.item"))->setCount($amount);

                $name = $config->getNested("sets.titan.boots.name");
                $item->setCustomName(C::RESET . C::colorize($name));

                $lore = [];
                $setBonusLines = $config->getNested('sets.titan.set-bonus');
                foreach ($config->getNested("sets.titan.boots.lore") as $line) {
                    $lore[] = C::RESET . C::colorize(str_replace('{set_bonus}', implode(PHP_EOL, $setBonusLines), $line));
                }
                $item->setLore($lore);
                $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3));
                $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4));

                $item->getNamedTag()->setString("customarmor", "titan");
                break;
        }
        return $item;
    }
}