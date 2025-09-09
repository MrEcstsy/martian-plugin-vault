<?php

declare(strict_types=1);

namespace ecstsy\MonthlyCrates\utils;

use ecstsy\MartianUtilities\utils\GeneralUtils;
use ecstsy\MonthlyCrates\Loader;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat as C;

final class Utils {

    public static function createMonthlyCrateItem(Player $player, string $type, int $amount = 1): ?Item {
        $config = GeneralUtils::getConfiguration(Loader::getInstance(), "crates.yml");
        $cfgData = $config->getAll();

        if (!isset($cfgData["crates"][$type])) {
            return null;
        }

        $crateData = $cfgData["crates"][$type];

        $material = StringToItemParser::getInstance()->parse($crateData["material"]);

        if ($material === null) {
            return null;
        }

        $material->setCount($amount);
        $material->setCustomName(C::colorize($crateData["name"]));

        $lore = [];

        foreach ($crateData["lore"] as $line) {
            $lore[] = C::colorize(str_replace("{player}", $player->getName(), $line));
        }
        $material->setLore($lore);

        $root = $material->getNamedTag();
        $monthlyCrateTag = new CompoundTag();

        $monthlyCrateTag->setString("monthly_crate", $type);
        $root->setTag("MartianMonthlyCrate", $monthlyCrateTag);
        
        return $material;
    }

    public static function parseItemFromConfig(array $itemData): ?Item {
        $item = StringToItemParser::getInstance()->parse($itemData["material"]);
        if ($item === null) {
            return null;
        }

        $item->setCustomName(C::colorize($itemData["name"]));

        $lore = [];
        foreach ($itemData["lore"] as $line) {
            $lore[] = C::colorize($line);
        }
        $item->setLore($lore);

        return $item;
    }

    public static function getSurroundingSlots(int $slot): array {
        $slotMappings = [
            12 => [3, 9, 10, 11, 39, 48, 15, 16, 17],
            13 => [4, 40, 9, 10, 11, 15, 16, 17],
            14 => [5, 15, 16, 17, 9, 10, 11, 50, 41],
            21 => [18, 19, 20, 24, 25, 26, 3, 39, 48],
            22 => [4, 40, 18, 19, 20, 24, 25, 26],
            23 => [5, 24, 25, 26, 41, 50, 18, 19, 20],
            30 => [39, 48, 3, 27, 28, 29, 33, 34, 35],
            31 => [4, 40, 27, 28, 29, 33, 34, 35],
            32 => [5, 41, 50, 33, 34, 35, 27, 28, 29],
        ];
        return $slotMappings[$slot] ?? [];
    }

    /**
     * Build crate rewards from config.
     *
     * @param array $crateData The crate's config section
     * @param Player $player The player opening the crate (for command replacements)
     * @return array<Item> List of generated items
     */
    public static function buildCrateItems(array $crateData, Player $player): array {
        $items = [];
        $stringToItemParser = StringToItemParser::getInstance();

        if (!isset($crateData["rewards"]) || empty($crateData["rewards"])) {
            return $items;
        }

        foreach ($crateData["rewards"] as $reward) {
            if (!isset($reward["material"])) {
                continue;
            }

            $item = $stringToItemParser->parse($reward["material"]);
            if ($item === null) {
                throw new \InvalidArgumentException("Invalid reward material: " . $reward["material"]);
            }

            if (isset($reward["name"])) {
                $item->setCustomName(C::colorize($reward["name"]));
            }

            if (isset($reward["lore"])) {
                $lore = array_map(fn($line) => C::colorize($line), $reward["lore"]);
                $item->setLore($lore);
            }

            if (isset($reward["chance"]) && mt_rand(1, 100) > $reward["chance"]) {
                continue;
            }

            if (isset($reward["command"])) {
                foreach ($reward["command"] as $cmd) {
                    $cmd = str_replace("{player}", $player->getName(), $cmd);
                    Server::getInstance()->dispatchCommand(new ConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage()), $cmd);
                }
            }

            $items[] = $item;
        }

        return $items;
    }
}