<?php

namespace ecstsy\LevelSystem\Utils;

use ecstsy\LevelSystem\Loader;
use JackMD\ConfigUpdater\ConfigUpdater;
use pocketmine\color\Color;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\item\Armor;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\item\StringToItemParser;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as C;

class LevelUtils {

    public static function checkConfig(string $configFile, int $latestVersion): void {
        $config = self::getConfiguration(Loader::getInstance(), $configFile);

        ConfigUpdater::checkUpdate(Loader::getInstance(), $config, "version", $latestVersion, "A new version of $configFile is available! Updating...");
    }

    public static function getConfiguration(PluginBase $plugin, string $fileName): Config {
        $pluginFolder = $plugin->getDataFolder();
        $filePath = $pluginFolder . $fileName;

        $config = null;

        if (!file_exists($filePath)) {
            $plugin->getLogger()->warning("Configuration file '$fileName' not found.");
        } else {
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);

            switch ($extension) {
                case 'yml':
                case 'yaml':
                    $config = new Config($filePath, Config::YAML);
                    break;

                case 'json':
                    $config = new Config($filePath, Config::JSON);
                    break;

                default:
                    $plugin->getLogger()->warning("Unsupported configuration file format for '$fileName'.");
                    break;
            }
        }

        return $config;
    }

    public static function setupCommandRewards(array $inputData, Player $player): array {
        $rewards = [];

        foreach ($inputData as $data) {
            $commandString = $data["command"];
            $reward = Server::getInstance()->dispatchCommand(new ConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage()), str_replace("{player}", $player->getName(), $commandString));
        }

        $rewards[] = $reward;

        return $rewards;
    }

    public static function setupItems(array $inputData): array
    {
        $items = [];
        $stringToItemParser = StringToItemParser::getInstance();
    
        foreach ($inputData as $data) {
            $itemString = $data["item"];
            $item = $stringToItemParser->parse($itemString);
    
            if ($item === null) {
                continue;
            }
    
            $amount = $data["amount"] ?? 1;
            $item->setCount($amount);
    
            $name = $data["name"] ?? null;
            if ($name !== null) {
                $item->setCustomName(C::colorize($name));
            }
    
            $lore = $data["lore"] ?? null;
            if ($lore !== null) {
                $lore = array_map(function ($line) {
                    return C::colorize($line);
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

            $color = $data["color"] ?? null;
            if ($item instanceof Armor && $color !== null) {
                $rgb = explode(",", $color);
                $item->setCustomColor(Color::fromRGB((int)$rgb[0]));
            }
    
            $nbtData = $data["nbt"] ?? null;
            if ($nbtData !== null) {
                $tag = $nbtData["tag"] ?? "";
                $value = $nbtData["value"] ?? "";
                $item->getNamedTag()->setString($tag, $value);
            }
    
            $items[] = $item;
        }
    
        return $items;
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

    public static function parseShorthandAmount($shorthand): float|int
    {
        $multipliers = [
            'k' => 1000,
            'm' => 1000000,
            'b' => 1000000000,
        ];
        $lastChar = strtolower(substr($shorthand, -1));
        if (isset($multipliers[$lastChar])) {
            $multiplier = $multipliers[$lastChar];
            $shorthand = substr($shorthand, 0, -1);
        } else {
            $multiplier = 1;
        }

        return intval($shorthand) * $multiplier;
    }

}