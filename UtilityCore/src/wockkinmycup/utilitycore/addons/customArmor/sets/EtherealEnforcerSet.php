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

class EtherealEnforcerSet {

    public static function give(string $piece, int $amount = 1): ?Item {
        $item = VanillaItems::AIR();
        $config = Utils::getConfiguration(Loader::getInstance(), "customarmor.yml");

        switch (strtolower($piece)) {
            case "helmet":
                $item = StringToItemParser::getInstance()->parse($config->getNested("sets.ethereal_enforcer.helmet.item"))->setCount($amount);

                $name = $config->getNested("sets.ethereal_enforcer.helmet.name");
                $item->setCustomName(C::RESET . C::colorize($name));

                $lore = [];
                $setBonusLines = $config->getNested('sets.ethereal_enforcer.set-bonus');
                foreach ($config->getNested("sets.ethereal_enforcer.helmet.lore") as $line) {
                    $lore[] = C::RESET . C::colorize(str_replace('{set_bonus}', implode(PHP_EOL, $setBonusLines), $line));
                }
                $item->setLore($lore);
                $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3));
                $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4));

                $item->getNamedTag()->setString("customarmor", "ethereal_enforcer");
                break;
            case "chestplate":
                $item = StringToItemParser::getInstance()->parse($config->getNested("sets.ethereal_enforcer.chestplate.item"))->setCount($amount);

                $name = $config->getNested("sets.ethereal_enforcer.chestplate.name");
                $item->setCustomName(C::RESET . C::colorize($name));

                $lore = [];
                $setBonusLines = $config->getNested('sets.ethereal_enforcer.set-bonus');
                foreach ($config->getNested("sets.ethereal_enforcer.chestplate.lore") as $line) {
                    $lore[] = C::RESET . C::colorize(str_replace('{set_bonus}', implode(PHP_EOL, $setBonusLines), $line));
                }
                $item->setLore($lore);
                $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3));
                $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4));

                $item->getNamedTag()->setString("customarmor", "ethereal_enforcer");
                break;
            case "leggings":
                $item = StringToItemParser::getInstance()->parse($config->getNested("sets.ethereal_enforcer.leggings.item"))->setCount($amount);

                $name = $config->getNested("sets.ethereal_enforcer.leggings.name");
                $item->setCustomName(C::RESET . C::colorize($name));

                $lore = [];
                $setBonusLines = $config->getNested('sets.ethereal_enforcer.set-bonus');
                foreach ($config->getNested("sets.ethereal_enforcer.leggings.lore") as $line) {
                    $lore[] = C::RESET . C::colorize(str_replace('{set_bonus}', implode(PHP_EOL, $setBonusLines), $line));
                }
                $item->setLore($lore);
                $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3));
                $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4));

                $item->getNamedTag()->setString("customarmor", "ethereal_enforcer");
                break;
            case "boots":
                $item = StringToItemParser::getInstance()->parse($config->getNested("sets.ethereal_enforcer.boots.item"))->setCount($amount);

                $name = $config->getNested("sets.ethereal_enforcer.boots.name");
                $item->setCustomName(C::RESET . C::colorize($name));

                $lore = [];
                $setBonusLines = $config->getNested('sets.ethereal_enforcer.set-bonus');
                foreach ($config->getNested("sets.ethereal_enforcer.boots.lore") as $line) {
                    $lore[] = C::RESET . C::colorize(str_replace('{set_bonus}', implode(PHP_EOL, $setBonusLines), $line));
                }
                $item->setLore($lore);
                $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3));
                $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4));

                $item->getNamedTag()->setString("customarmor", "ethereal_enforcer");
                break;
            case "weapon":
                $item = StringToItemParser::getInstance()->parse($config->getNested("sets.ethereal_enforcer.weapon.item"))->setCount($amount);

                $name = $config->getNested("sets.ethereal_enforcer.weapon.name");
                $item->setCustomName(C::RESET . C::colorize($name));

                $lore = [];
                $setBonusLines = $config->getNested("sets.ethereal_enforcer.weapon-bonus");
                foreach ($config->getNested("sets.ethereal_enforcer.weapon.lore") as $line) {
                    $lore[] = C::RESET . C::colorize(str_replace('{weapon_bonus}', implode(PHP_EOL, $setBonusLines), $line));
                }
                $item->setLore($lore);
                $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3));
                $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 5));

                $item->getNamedTag()->setString("customarmor", "ethereal_enforcer");
                break;
        }
        return $item;
    }
}