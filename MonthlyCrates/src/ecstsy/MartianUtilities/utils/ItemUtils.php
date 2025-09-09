<?php

declare(strict_types=1);

namespace ecstsy\MartianUtilities\utils;

use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\nbt\TreeRoot;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

final class ItemUtils {

    /**
     * Encodes an item into a base64 string using LittleEndianNbtSerializer.
     *
     * @param Item $item
     * @return string
     */
    public static function encodeItem(Item $item): string {
        $serializer = new LittleEndianNbtSerializer();
        return base64_encode($serializer->write(new TreeRoot($item->nbtSerialize())));
    }

    /**
     * Decodes an item from a base64 string using LittleEndianNbtSerializer.
     *
     * @param string $data
     * @return Item
     */
    public static function decodeItem(string $data): Item {
        $serializer = new LittleEndianNbtSerializer();
        return Item::nbtDeserialize($serializer->read(base64_decode($data))->mustGetCompoundTag());
    }

    public static function setupItems(array $rewardData, ?Player $player = null): array
    {
        $rewards = [];
        $stringToItemParser = StringToItemParser::getInstance();
        
        foreach ($rewardData as $data) {
            if (!isset($data["item"])) {
                continue; 
            }

            $itemString = $data["item"];
            $item = $stringToItemParser->parse($itemString);
            if ($item === null) {
                continue;
            }

            if (isset($data["command"])) {
                $commandString = $data["command"] ?? null;
                if ($commandString !== null) {
                    Server::getInstance()->dispatchCommand(new ConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage()), str_replace("{player}", $player->getName(), $commandString));
                }
            }
    
            $amount = $data["amount"] ?? 1;
            $item->setCount($amount);
    
            $name = $data["name"] ?? null;
            if ($name !== null) {
                $item->setCustomName(TextFormat::colorize($name));
            }
    
            $lore = $data["lore"] ?? null;
            if ($lore !== null) {
                $lore = array_map(function ($line) {
                    return TextFormat::colorize($line);
                }, $lore);
                $item->setLore($lore);
            }
    
            $enchantments = $data["enchantments"] ?? null;
            if ($enchantments !== null) {
                foreach ($enchantments as $enchantmentData) {
                    $enchantment = $enchantmentData["enchant"] ?? null;
                    $level = $enchantmentData["level"] ?? 1;
                    if ($enchantment !== null) {
                        $item->addEnchantment(new EnchantmentInstance(StringToEnchantmentParser::getInstance()->parse($enchantment)), $level);
                    }
                }
            }
    
            $nbtData = $data["nbt"] ?? null;
            if ($nbtData !== null) {
                $tag = $nbtData["tag"] ?? "";
                $value = $nbtData["value"] ?? "";
            
                if (is_int($value)) {
                    $item->getNamedTag()->setInt($tag, $value);
                } else {
                    $item->getNamedTag()->setString($tag, $value);
                }
            }            
    
            $rewards[] = $item;
        }
    
        return $rewards;
    }    
}
