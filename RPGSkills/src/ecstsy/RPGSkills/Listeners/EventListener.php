<?php

namespace ecstsy\RPGSkills\Listeners;

use ecstsy\RPGSkills\Player\PlayerManager;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;

class EventListener implements Listener {

    public function onLogin(PlayerLoginEvent $event) {
        $player = $event->getPlayer();
        
        if (PlayerManager::getInstance()->getSession($player) === null) {
            PlayerManager::getInstance()->createSession($player);
        }
    }

    public function onJoin(PlayerJoinEvent $event) {
        $player = $event->getPlayer();
        
        PlayerManager::getInstance()->getSession($player)->setConnected(true);
    }

    public function onQuit(PlayerQuitEvent $event) {
        $player = $event->getPlayer();
        PlayerManager::getInstance()->getSession($player)->setConnected(false);
    }
}