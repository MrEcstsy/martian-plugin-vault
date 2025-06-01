<?php

namespace ecstsy\LevelSystem\Commands;

use CortexPE\Commando\BaseCommand;
use ecstsy\LevelSystem\Loader;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;

class LevelCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(C::RED . "This command can only be used in-game.");
            return;
        }

        $session = Loader::getPlayerManager()->getSession($sender);
        $cfg = Loader::getInstance()->getConfig();

        if ($session->isConnected() !== null) {
            foreach ($cfg->getNested("messages.display-level") as $message) {
                $sender->sendMessage(C::colorize(str_replace("{level}", $session->getLevel(), $message)));
            }
        }
    }

    public function getPermission(): string {
        return "levelsystem.default";
    }
}