<?php

namespace ecstsy\RPGSkills;

use ecstsy\RPGSkills\Commands\SetLevelCommand;
use ecstsy\RPGSkills\Listeners\EventListener;
use ecstsy\RPGSkills\Listeners\SkillListener;
use ecstsy\RPGSkills\Player\PlayerManager;
use ecstsy\RPGSkills\Utils\Queries;
use ecstsy\RPGSkills\Utils\Utils;
use JackMD\ConfigUpdater\ConfigUpdater;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;

class Loader extends PluginBase {

    use SingletonTrait;

    public int $cfgVer = 1;

    public static DataConnector $connector;

    public static PlayerManager $manager;

    protected function onLoad(): void {
        self::setInstance($this);
    }

    public function onEnable(): void {
        ConfigUpdater::checkUpdate($this, $this->getConfig(), "version", 1);
        $config = Utils::getConfiguration("config.yml");

        self::$connector = libasynql::create($this, ["type" => "sqlite", "sqlite" => ["file" => "sqlite.sql"], "worker-limit" => 2], ["sqlite" => "sqlite.sql"]);
        self::$connector->executeGeneric(Queries::PLAYERS_INIT);
        self::$connector->waitAll();

        self::$manager = new PlayerManager($this);

        $listeners = [
            new EventListener(),
            new SkillListener()
        ];

        foreach ($listeners as $listener) {
            $this->getServer()->getPluginManager()->registerEvents($listener, $this);
        }

        $this->getServer()->getCommandMap()->registerAll("RPGSkills", [
            new SetLevelCommand($this, "setlevel", "Set your RPGSkill level")
        ]);
    }

    public static function getDatabase(): DataConnector {
        return self::$connector;
    }

    public static function getPlayerManager(): PlayerManager {
        return self::$manager;
    }
}