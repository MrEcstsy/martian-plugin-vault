<?php

namespace ecstsy\Holograms;

use ecstsy\Holograms\Commands\CreateHologramCommand;
use ecstsy\Holograms\Commands\RemoveHologramCommand;
use ecstsy\Holograms\libs\JackMD\ConfigUpdater\ConfigUpdater;
use ecstsy\Holograms\Listeners\EventListener;
use ecstsy\Holograms\Utils\HologramManager;
use ecstsy\Holograms\Utils\Utils;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;

class Loader extends PluginBase {

    use SingletonTrait;

    public int $cfgVersion = 1;

    public function onLoad(): void {
        self::setInstance($this);
    }

    public function onEnable(): void {
        $listeners = [
            new EventListener() 
        ];

        foreach ($listeners as $listener) {
            $this->getServer()->getPluginManager()->registerEvents($listener, $this);
        }

        $this->getServer()->getCommandMap()->registerAll("Holograms", [
            new CreateHologramCommand($this, "createhologram", "Create a hologram", ["ch"]),
            new RemoveHologramCommand($this, "removehologram", "Remove a hologram", ["rh"]),
        ]);

        ConfigUpdater::checkUpdate($this, $this->getConfig(), "version", $this->cfgVersion);
        $this->saveResource("holograms.json");

        HologramManager::init(Utils::getConfiguration("holograms.json"));
    }
}