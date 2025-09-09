<?php

namespace ecstsy\TreasureLockpicks;

use ecstsy\TreasureLockpicks\commands\GiveTreasureLocksCommand;
use ecstsy\TreasureLockpicks\listener\TreasureListener;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;

class Loader extends PluginBase {

    use SingletonTrait;

    public function onLoad(): void
    {
        self::setInstance($this);
    }

    public function onEnable(): void
    {
        $currentVersion = $this->getConfig()->get("version");

        if ($currentVersion === null || $currentVersion !== "1.0") {
            $this->getLogger()->info($currentVersion === null ? "Updating configuration to new format" : "Updating configuration to version 1.0");
            $this->saveOldConfig();
            $this->saveDefaultConfig();
        }

        $this->getServer()->getPluginManager()->registerEvents(new TreasureListener(), $this);
        $this->getServer()->getCommandMap()->registerAll("TreasureLockpicks", [
            new GiveTreasureLocksCommand($this, "givetreasurelocks", "Give treasures or locks to a player", ["gtl", "givetl"]),
        ]);
    }
    
    private function saveOldConfig(): void
    {
        $oldConfigPath = $this->getDataFolder() . "old_config.yml";
        $this->saveResource("config.yml", false);
        rename($this->getDataFolder() . "config.yml", $oldConfigPath);
    }
}