<?php

namespace wockkinmycup\advancedbounty\Utils;

use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use wockkinmycup\advancedbounty\Loader;

class Utils {

    public static function getConfigurations(string $type = "default") : Config {
        return match ($type) {
            "messages" => new Config(Loader::getInstance()->getDataFolder() . "messages.yml", Config::YAML),
            "bounty" => new Config(Loader::getInstance()->getDataFolder() . "data/bounty.json", Config::JSON),
            default => new Config(Loader::getInstance()->getDataFolder() . "config.yml", Config::YAML),
        };
    }

    /**
     * Returns an online player whose name begins with or equals the given string (case insensitive).
     * The closest match will be returned, or null if there are no online matches.
     *
     * @param string $name The prefix or name to match.
     * @return Player|null The matched player or null if no match is found.
     */
    public static function customGetPlayerByPrefix(string $name): ?Player {
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

        $amount = intval($shorthand) * $multiplier;

        return $amount;
    }

    public static function translateTime(int $seconds): string
    {
        $timeUnits = [
            'week' => 60 * 60 * 24 * 7,
            'day' => 60 * 60 * 24,
            'hour' => 60 * 60,
            'minute' => 60,
            'second' => 1,
        ];

        $parts = [];

        foreach ($timeUnits as $unit => $value) {
            if ($seconds >= $value) {
                $amount = floor($seconds / $value);
                $seconds %= $value;
                $parts[] = $amount . ' ' . ($amount == 1 ? $unit : $unit . 's');
            }
        }

        return implode(', ', $parts);
    }
}