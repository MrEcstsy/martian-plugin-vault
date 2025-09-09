<?php

namespace wockkinmycup\superscroll\utils;

use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use wockkinmycup\superscroll\Loader;
use wockkinmycup\utilitycore\utils\Utils;

class Scrolls
{

    public static function getSuperScrolls(string $type, int $amount = 1): ?Item {
        $item = VanillaItems::AIR()->setCount($amount);
        $config = Utils::getConfiguration(Loader::getInstance(), "config.yml");
        switch ($type) {
            case "enchanter":
                $item = StringToItemParser::getInstance()->parse($config->getNested("superscrolls.enchanter.item"))->setCount($amount);

                $name = $config->getNested("superscrolls.enchanter.name");
                $item->setCustomName(TextFormat::colorize($name));

                $lorecfg = $config->getNested("superscrolls.enchanter.lore");
                $lore = [];
                foreach ($lorecfg as $line) {
                    $color = TextFormat::colorize($line);
                    $lore[] = $color;
                }
                $item->setLore($lore);

                $item->getNamedTag()->setString("superscrolls", "enchanter");
                break;
        }

        return $item;
    }

    public static function increaseEnchantments(Item $item): void
    {
        $enchantments = $item->getEnchantments();

        foreach ($enchantments as $enchantment) {
            $level = $enchantment->getLevel();
            $enchantmentName = $enchantment->getType()->getName();
            $item->addEnchantment(new EnchantmentInstance(StringToEnchantmentParser::getInstance()->parse(Server::getInstance()->getLanguage()->translate($enchantmentName)), $level + 1));
        }
    }
}