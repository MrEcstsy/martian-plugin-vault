<?php

namespace ecstsy\TreasureLockpicks\utils;

use ecstsy\TreasureLockpicks\Loader;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat as C;

class TLUtils {

    public static function getTreasure(string $type, int $amount = 1, bool $lockStatus = false): ?Item {
        $config = Loader::getInstance()->getConfig();
        $treasureConfig = $config->getNested("treasures.$type");

        if ($treasureConfig === null) {
            return null; 
        }
    
        $itemName = $treasureConfig["name"] ?? "&r&l&fExample Treasure &r&7(Right Click)";
        $itemLore = $treasureConfig["lore"] ?? ['&r&7Configure this treasure in the configuration.'];
    
        $item = StringToItemParser::getInstance()->parse($treasureConfig["type"] ?? "chest")->setCount($amount);
    
        $item->setCustomName(C::colorize($itemName));
        
        if (!empty($itemLore)) {
            $lore = [];
            foreach ($itemLore as $line) {
                $lore[] = C::colorize($line);
            }
            $item->setLore(str_replace("{LOCK_STATUS}", $lockStatus ? "§r§l§aUnlocked" : "§r§l§cLocked", $lore));
        }
    
        $item->getNamedTag()->setString("treasure", strtolower($type));
        $item->getNamedTag()->setString("lock_status", $lockStatus ? "unlocked" : "locked");
        
        return $item;
    }
    
    public static function getLockpick(string $string, int $amount = 1, int $successChance = 100): ?Item {
        $config = Loader::getInstance()->getConfig();
        $lockpickConfig = $config->getNested("lockpick");
        
        if ($lockpickConfig === null) {
            return null; 
        }

        $itemName = $lockpickConfig["name"] ?? "&r&7This Lockpick is capable of Unlocking many";
        $itemLore = $lockpickConfig["lore"] ?? ["&r&7This Lockpick is capable of Unlocking many", "&r&7Treasures that many have failed to open!", " ", " &r&d* &7Unlocks: &dTreasures &8[&7Any Tier&8]"," &r&d* &7Success Chance: &a{SUCCESS_CHANCE}%", " ", "&r&l&d(!) &r&dDrag n' Drop this onto a Treasure!"];
        
        $item = StringToItemParser::getInstance()->parse($lockpickConfig["type"] ?? "tripwire_hook")->setCount($amount);
        
        $item->setCustomName(C::colorize($itemName));
        
        if (!empty($itemLore)) {
            $lore = [];
            foreach ($itemLore as $line) {
                $lore[] = C::colorize($line);
            }
            $item->setLore(str_replace("{SUCCESS_CHANCE}", $successChance, $lore));
        }
        
        $item->getNamedTag()->setString("lockpick", "true");
        $item->getNamedTag()->setInt("successChance", $successChance);
        
        return $item;
    }

    public static function getTreasureIdentifiers(): array {
        $treasuresConfig = Loader::getInstance()->getConfig()->get("treasures", []);
        return array_keys($treasuresConfig);
    }
    
        /**
     * Returns an online player whose name begins with or equals the given string (case insensitive).
     * The closest match will be returned, or null if there are no online matches.
     *
     * @param string $name The prefix or name to match.
     * @return Player|null The matched player or null if no match is found.
     */
    public static function getPlayerByPrefix(string $name): ?Player {
        $found = null;
        $name = strtolower($name);
        $delta = PHP_INT_MAX;

        /** @var Player[] $onlinePlayers */
        $onlinePlayers = Server::getInstance()->getOnlinePlayers();

        foreach ($onlinePlayers as $player) {
            if (stripos($player->getName(), $name) === 0) {
                $curDelta = strlen($player->getName()) - strlen($name);

                if ($curDelta < $delta) {
                    $found = $player;
                    $delta = $curDelta;
                }

                if ($curDelta === 0) {
                    break;
                }
            }
        }

        return $found;
    }
}