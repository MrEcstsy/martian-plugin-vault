<?php

namespace ecstsy\xphealth;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerExperienceChangeEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;

class Loader extends PluginBase implements Listener {
    use SingletonTrait;

    public function onLoad(): void {
        self::setInstance($this);
    }

    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onExperienceChange(PlayerExperienceChangeEvent $event): void {
        $entity = $event->getEntity();
        if ($entity instanceof Player) {
            $entity->setMaxHealth(20 + 2 * $event->getNewLevel());
        }
    }

    public function onRespawn(PlayerRespawnEvent $event): void { 
        $player = $event->getPlayer();

        $player->setMaxHealth(20);
    }
}