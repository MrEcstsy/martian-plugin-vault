<?php

namespace ecstsy\LevelSystem;

use DaPigGuy\libPiggyEconomy\libPiggyEconomy;
use DaPigGuy\libPiggyEconomy\providers\EconomyProvider;
use ecstsy\LevelSystem\Commands\LevelCommand;
use ecstsy\LevelSystem\Commands\LevelUpCommand;
use ecstsy\LevelSystem\Listener\EventListener;
use ecstsy\LevelSystem\Player\PlayerManager;
use ecstsy\LevelSystem\Utils\LevelUtils;
use ecstsy\LevelSystem\Utils\Queries;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;

class Loader extends PluginBase {
    use SingletonTrait;

    public static DataConnector $connector;

    public static PlayerManager $playerManager;

    public static EconomyProvider $economyProvider;

    public function onLoad(): void {
        self::setInstance($this);
    }

    public function onEnable(): void {
        $this->loadVirions();
        $files = ["config.yml" => 1, "levels.yml" => 1];

        foreach ($files as $file => $version) {
            $this->saveResource($file);
            LevelUtils::checkConfig($file, $version);
        }

        $this->getServer()->getCommandMap()->registerAll("LevelSystem", [
            new LevelCommand($this, "level", "View your level"),
            new LevelUpCommand($this, "levelup", "Level up")
        ]);

        self::$connector = libasynql::create($this, ["type" => "sqlite", "sqlite" => ["file" => "sqlite.sql"], "worker-limit" => 2], ["sqlite" => "sqlite.sql"]);
        self::$connector->executeGeneric(Queries::PLAYERS_INIT);
        self::$connector->waitAll();

        self::$playerManager = new PlayerManager($this);

        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
    }

    public function loadVirions(): void {
        libPiggyEconomy::init();
        self::$economyProvider = libPiggyEconomy::getProvider($this->getConfig()->get("economy"));
    }

    public function onDisable(): void {
        if (isset(self::$connector)) {
            self::$connector->close();
        }
    }

    public static function getDatabase(): DataConnector {
        return self::$connector;
    }

    public static function getPlayerManager(): PlayerManager {
        return self::$playerManager;
    }

    public static function getEconomyProvider(): EconomyProvider {
        return self::$economyProvider;
    }
}