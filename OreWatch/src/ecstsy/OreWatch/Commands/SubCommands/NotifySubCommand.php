<?php

namespace ecstsy\OreWatch\Commands\SubCommands;

use CortexPE\Commando\BaseSubCommand;
use ecstsy\OreWatch\Loader;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;

class NotifySubCommand extends BaseSubCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(C::RED . "This command can only be used in-game.");
            return;
        }
    
        $lang = Loader::getInstance()->getLang();
        $session = Loader::getPlayerManager()->getSession($sender);
    
        if ($session === null) {
            $sender->sendMessage(C::RED . "Your session could not be found. Please try rejoining the server.");
            return;
        }
    
        $session->toggleNotify();
    
        if ($session->getNotify()) {
            $sender->sendMessage(C::colorize($lang->getNested("prefix") . $lang->getNested("notification-enabled")));
        } else {
            $sender->sendMessage(C::colorize($lang->getNested("prefix") . $lang->getNested("notification-disabled")));
        }
    }    
    
    public function getPermission(): string {
        return "OreWatch.staff";
    }
}