<?php

namespace ecstsy\FarmersEvolution;

use ecstsy\FarmersEvolution\listeners\EventListener;
use ecstsy\FarmersEvolution\utils\Utils;
use JackMD\ConfigUpdater\ConfigUpdater;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;

class Loader extends PluginBase {
    use SingletonTrait;

    public const CFGVERSION = 1;

    protected function onLoad(): void {
        self::setInstance($this);
    }

    protected function onEnable(): void {
        ConfigUpdater::checkUpdate($this, Utils::getConfiguration("config.yml"), "version", self::CFGVERSION);

        $listeners = [
            new EventListener()
        ];

        foreach ($listeners as $listener) {
            $this->getServer()->getPluginManager()->registerEvents($listener, $this);
        }
    }
}