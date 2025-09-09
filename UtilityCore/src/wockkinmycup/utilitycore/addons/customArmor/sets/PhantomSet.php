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

class PhantomSet {

    public static function give(string $piece, int $amount = 1): ?Item {
        $item = VanillaItems::AIR();
        $config = Utils::getConfiguration(Loader::getInstance(), "customarmor.yml");

        switch (strtolower($piece)) {
            case "helmet":
                $item = StringToItemParser::getInstance()->parse($config->getNested("sets.phantom.helmet.item"))->setCount($amount);

                $name = $config->getNested("sets.phantom.helmet.name");
                $item->setCustomName(C::RESET . C::colorize($name));

                $lore = [];
                $setBonusLines = $config->getNested('sets.phantom.set-bonus');
                foreach ($config->getNested("sets.phantom.helmet.lore") as $line) {
                    $lore[] = C::RESET . C::colorize(str_replace('{set_bonus}', implode(PHP_EOL, $setBonusLines), $line));
                }
                $item->setLore($lore);
                $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3));
                $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4));

                $item->getNamedTag()->setString("customarmor", "phantom");
                break;
            case "chestplate":
                $item = StringToItemParser::getInstance()->parse($config->getNested("sets.phantom.chestplate.item"))->setCount($amount);

                $name = $config->getNested("sets.phantom.chestplate.name");
                $item->setCustomName(C::RESET . C::colorize($name));

                $lore = [];
                $setBonusLines = $config->getNested('sets.phantom.set-bonus');
                foreach ($config->getNested("sets.phantom.chestplate.lore") as $line) {
                    $lore[] = C::RESET . C::colorize(str_replace('{set_bonus}', implode(PHP_EOL, $setBonusLines), $line));
                }
                $item->setLore($lore);
                $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3));
                $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4));

                $item->getNamedTag()->setString("customarmor", "phantom");
                break;
            case "leggings":
                $item = StringToItemParser::getInstance()->parse($config->getNested("sets.phantom.leggings.item"))->setCount($amount);

                $name = $config->getNested("sets.phantom.leggings.name");
                $item->setCustomName(C::RESET . C::colorize($name));

                $lore = [];
                $setBonusLines = $config->getNested('sets.phantom.set-bonus');
                foreach ($config->getNested("sets.phantom.leggings.lore") as $line) {
                    $lore[] = C::RESET . C::colorize(str_replace('{set_bonus}', implode(PHP_EOL, $setBonusLines), $line));
                }
                $item->setLore($lore);
                $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3));
                $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4));

                $item->getNamedTag()->setString("customarmor", "phantom");
                break;
            case "boots":
                $item = StringToItemParser::getInstance()->parse($config->getNested("sets.phantom.boots.item"))->setCount($amount);

                $name = $config->getNested("sets.phantom.boots.name");
                $item->setCustomName(C::RESET . C::colorize($name));

                $lore = [];
                $setBonusLines = $config->getNested('sets.phantom.set-bonus');
                foreach ($config->getNested("sets.phantom.boots.lore") as $line) {
                    $lore[] = C::RESET . C::colorize(str_replace('{set_bonus}', implode(PHP_EOL, $setBonusLines), $line));
                }
                $item->setLore($lore);
                $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3));
                $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4));

                $item->getNamedTag()->setString("customarmor", "phantom");
                break;
            case "weapon":
                $item = StringToItemParser::getInstance()->parse($config->getNested("sets.phantom.weapon.item"))->setCount($amount);

                $name = $config->getNested("sets.phantom.weapon.name");
                $item->setCustomName(C::RESET . C::colorize($name));

                $lore = [];
                $setBonusLines = $config->getNested("sets.phantom.weapon-bonus");
                foreach ($config->getNested("sets.phantom.weapon.lore") as $line) {
                    $lore[] = C::RESET . C::colorize(str_replace('{weapon_bonus}', implode(PHP_EOL, $setBonusLines), $line));
                }
                $item->setLore($lore);
                $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3));
                $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 5));

                $item->getNamedTag()->setString("customarmor", "phantom");
                break;
        }
        return $item;
    }
}