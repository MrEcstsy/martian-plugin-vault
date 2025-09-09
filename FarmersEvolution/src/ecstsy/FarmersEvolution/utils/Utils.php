<?php

namespace ecstsy\FarmersEvolution\utils;

use ecstsy\FarmersEvolution\Loader;
use pocketmine\block\Crops;
use pocketmine\utils\Config;
use pocketmine\world\World;

class Utils {

    private static array $configCache = [];

    public static function getConfiguration(string $fileName): ?Config {
        $pluginFolder = Loader::getInstance()->getDataFolder();
        $filePath = $pluginFolder . $fileName;

        if (isset(self::$configCache[$filePath])) {
            return self::$configCache[$filePath];
        }

        if (!file_exists($filePath)) {
            Loader::getInstance()->getLogger()->warning("Configuration file '$filePath' not found.");
            return null;
        }
        
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
                Loader::getInstance()->getLogger()->warning("Unsupported configuration file format for '$filePath'.");
                return null;
        }

        self::$configCache[$filePath] = $config;
        return $config; 
    }

    public static function checkGrowthConditions(Crops $block, array $conditions): bool {
        foreach ($conditions as $condition) {
            $type = $condition["type"];
            switch ($type) {
                case "time-of-day":
                    case 'time_of_day':
                        if (!self::checkTimeOfDay($block, $condition)) return false;
                        break;
                    case 'nearby_water':
                        //if (!self::checkNearbyWater($block, $condition)) return false;
                        break;
                    case 'nearby_light_source':
                        //if (!self::checkNearbyLightSource($block, $condition)) return false;
                        break;
            }
        }
        return true;
    }

    private static function checkTimeOfDay(Crops $block, array $condition): bool {
        $world = $block->getPosition()->getWorld();
        $time = $world->getTimeOfDay();

        $requiredTime = $condition['time'] === 'day' ? World::TIME_DAY : World::TIME_NIGHT;
        return ($time >= $requiredTime);
    }
}