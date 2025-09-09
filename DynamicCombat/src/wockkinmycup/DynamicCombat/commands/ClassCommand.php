<?php

namespace wockkinmycup\DynamicCombat\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use wockkinmycup\utilitycore\managers\PlayerManager;

class ClassCommand extends Command {

    public function __construct() {
        parent::__construct("class");
        $this->setPermission("dynamic_combat.class");
    }

    public function execute(CommandSender $sender, string $label, array $args): bool {
        if ($sender instanceof Player) {
            if (isset($args[0])) {
                $className = strtolower($args[0]);
                PlayerManager::setPlayerClass($sender, $className);
                $sender->sendMessage("You have chosen the $className class!");
            } else {
                $sender->sendMessage("Usage: /class [className]");
            }
        }
        return true;
    }
}
