<?php

namespace ecstsy\OreWatch;

use ecstsy\OreWatch\Commands\OreWatchCommand;
use ecstsy\OreWatch\Listeners\EventListener;
use ecstsy\OreWatch\Player\PlayerManager;
use ecstsy\OreWatch\Utils\LanguageManager;
use ecstsy\OreWatch\Utils\Queries;
use JackMD\ConfigUpdater\ConfigUpdater;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;

class Loader extends PluginBase {

    use SingletonTrait;

    private LanguageManager $languageManager;

    public static DataConnector $connector;

    public static PlayerManager $playerManager;


    private int $configVer = 1;

    public function onLoad() : void {
        self::setInstance($this);
    }

    public function onEnable(): void {
        $subDirectories = ["locale"];

        foreach ($subDirectories as $directory) {
            $this->saveAllFilesInDirectory($directory);
        }

        $language = $this->getConfig()->get("language", "messages-eng");
        $this->languageManager = new LanguageManager($this, $language);

        ConfigUpdater::checkUpdate($this, $this->getConfig(), "version", $this->configVer);

        $this->getServer()->getCommandMap()->registerAll("OreWatch", [
            new OreWatchCommand($this, "orewatch", "View the ore watch commands", ["ow"]),
        ]);

        self::$connector = libasynql::create($this, ["type" => "sqlite", "sqlite" => ["file" => "sqlite.sql"], "worker-limit" => 2], ["sqlite" => "sqlite.sql"]);
        self::$connector->executeGeneric(Queries::PLAYERS_INIT);
        self::$connector->waitAll();

        self::$playerManager = new PlayerManager($this);

        $listeners = [new EventListener() ];

        foreach ($listeners as $listener) {
            $this->getServer()->getPluginManager()->registerEvents($listener, $this);
        }
    }

    private function saveAllFilesInDirectory(string $directory): void {
        $resourcePath = $this->getFile() . "resources/$directory/";
        if (!is_dir($resourcePath)) {
            $this->getLogger()->warning("Directory $directory does not exist.");
            return;
        }

        $files = scandir($resourcePath);
        if ($files === false) {
            $this->getLogger()->warning("Failed to read directory $directory.");
            return;
        }

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $this->saveResource("$directory/$file");
        }
    }

    public function getLang(): LanguageManager {
        return $this->languageManager;
    }

    public static function getDatabase(): DataConnector {
        return self::$connector;
    }

    public static function getPlayerManager(): PlayerManager {
        return self::$playerManager;
    }
}