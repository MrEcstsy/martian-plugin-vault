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

class SupremeArmor {

    public static function give(string $piece, int $amount = 1): ?Item {
        $item = VanillaItems::AIR();
        $config = Utils::getConfiguration(Loader::getInstance(), "customarmor.yml");

        switch (strtolower($piece)) {
            case "helmet":
                $item = StringToItemParser::getInstance()->parse($config->getNested("sets.supreme.helmet.item"))->setCount($amount);

                $name = $config->getNested("sets.supreme.helmet.name");
                $item->setCustomName(C::RESET . C::colorize($name));

                $lore = [];
                $setBonusLines = $config->getNested('sets.supreme.set-bonus');
                foreach ($config->getNested("sets.supreme.helmet.lore") as $line) {
                    $lore[] = C::colorize(C::RESET . str_replace('{set_bonus}', implode(PHP_EOL, $setBonusLines), $line));
                }
                $item->setLore($lore);
                $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3));
                $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4));

                $item->getNamedTag()->setString("customarmor", "supreme");
                break;
            case "chestplate":
                $item = StringToItemParser::getInstance()->parse($config->getNested("sets.supreme.chestplate.item"))->setCount($amount);

                $name = $config->getNested("sets.supreme.chestplate.name");
                $item->setCustomName(C::RESET . C::colorize($name));

                $lore = [];
                $setBonusLines = $config->getNested('sets.supreme.set-bonus');
                foreach ($config->getNested("sets.supreme.chestplate.lore") as $line) {
                    $lore[] = C::colorize(C::RESET . str_replace('{set_bonus}', implode(PHP_EOL, $setBonusLines), $line));
                }
                $item->setLore($lore);
                $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3));
                $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4));

                $item->getNamedTag()->setString("customarmor", "supreme");
                break;
            case "leggings":
                $item = StringToItemParser::getInstance()->parse($config->getNested("sets.supreme.leggings.item"))->setCount($amount);

                $name = $config->getNested("sets.supreme.leggings.name");
                $item->setCustomName(C::RESET . C::colorize($name));

                $lore = [];
                $setBonusLines = $config->getNested('sets.supreme.set-bonus');
                foreach ($config->getNested("sets.supreme.leggings.lore") as $line) {
                    $lore[] = C::colorize(C::RESET . str_replace('{set_bonus}', implode(PHP_EOL, $setBonusLines), $line));
                }
                $item->setLore($lore);
                $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3));
                $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4));

                $item->getNamedTag()->setString("customarmor", "supreme");
                break;
            case "boots":
                $item = StringToItemParser::getInstance()->parse($config->getNested("sets.supreme.boots.item"))->setCount($amount);

                $name = $config->getNested("sets.supreme.boots.name");
                $item->setCustomName(C::RESET . C::colorize($name));

                $lore = [];
                $setBonusLines = $config->getNested('sets.supreme.set-bonus');
                foreach ($config->getNested("sets.supreme.boots.lore") as $line) {
                    $lore[] = C::colorize(C::RESET . str_replace('{set_bonus}', implode(PHP_EOL, $setBonusLines), $line));
                }
                $item->setLore($lore);
                $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3));
                $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4));

                $item->getNamedTag()->setString("customarmor", "supreme");
                break;
        }
        return $item;
    }
}