<?php

namespace ecstsy\Holograms\Listeners;

use ecstsy\Holograms\Utils\HologramManager;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

class EventListener implements Listener {

    public function onPlayerJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
        foreach (HologramManager::getHolograms() as $entityId => $data) {
            if ($data['owner'] === $player->getName()) {
                $item = $data['item'];
                $location = $data['location'];
                HologramManager::sendItemPacket($player, $entityId, $item, $location);
            }
        }
    }
}