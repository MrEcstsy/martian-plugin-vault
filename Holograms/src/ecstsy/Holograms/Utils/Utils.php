<?php

namespace ecstsy\Holograms\Utils;

use ecstsy\Holograms\Loader;
use pocketmine\utils\Config;
use RuntimeException;

class Utils {

    private static array $configCache = [];

    /**
     * @throws RuntimeException if the configuration file is not found or if the format is unsupported.
     */
    public static function getConfiguration(string $fileName): Config {
        $pluginFolder = Loader::getInstance()->getDataFolder();
        $filePath = $pluginFolder . $fileName;

        if (isset(self::$configCache[$filePath])) {
            return self::$configCache[$filePath];
        }

        if (!file_exists($filePath)) {
            throw new RuntimeException("Configuration file '$filePath' not found.");
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
                throw new RuntimeException("Unsupported configuration file format for '$filePath'.");
        }

        self::$configCache[$filePath] = $config;
        return $config;
    }

}