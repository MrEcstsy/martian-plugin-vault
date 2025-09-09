<?php

namespace ecstsy\OreWatch\Commands;

use CortexPE\Commando\BaseCommand;
use ecstsy\OreWatch\Commands\SubCommands\NotifySubCommand;
use ecstsy\OreWatch\Loader;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;

class OreWatchCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());

        $this->registerSubCommand(new NotifySubCommand("notify", "Allows you to turn on and off alerts"));
    }   

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(C::RED . "This command can only be used in-game.");
            return;
        }

        $lang = Loader::getInstance()->getLang();

        $messages = [
            $lang->getNested("prefix") . "&r&7/orewatch notify - Allows you to turn on and off alerts",
            $lang->getNested("prefix") . "&r&7/orewatch inspect <player> - Allows you to view a specific player!",
            $lang->getNested("prefix") . "&r&7/orewatch interval - Gives you the interval of when the cache system will loop!",
            $lang->getNested("prefix") . "&r&7/orewatch settiongs - allows you to view setting options",
            $lang->getNested("prefix") . "&r&7/orewatch reload - Allows you to reload all config files!",
        ];

        foreach ($messages as $message) {
            $sender->sendMessage(C::colorize($message));
        }
    }

    public function getPermission(): string {
        return "OreWatch.staff";
    }
}