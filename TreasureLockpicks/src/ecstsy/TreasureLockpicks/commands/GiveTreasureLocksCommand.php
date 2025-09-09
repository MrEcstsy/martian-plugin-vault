<?php

namespace ecstsy\TreasureLockpicks\commands;

use CortexPE\Commando\BaseCommand;
use ecstsy\TreasureLockpicks\commands\subcommands\LockpickSubCommand;
use ecstsy\TreasureLockpicks\commands\subcommands\TreasureSubCommand;
use ecstsy\TreasureLockpicks\Loader;
use pocketmine\utils\TextFormat as C;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;

class GiveTreasureLocksCommand extends BaseCommand {

    public function prepare(): void
    {
        $this->setPermission("command.admin");
        $this->registerSubCommand(new LockpickSubCommand(Loader::getInstance(), "lockpick", "Give player lockpicks", ['lp']));
        $this->registerSubCommand(new TreasureSubCommand(Loader::getInstance(), "treasure", "Give player treasures", ['t']));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            return;
        }

        $sender->sendMessage(C::colorize("&r&l&aTreasure Lockpicks: &dv" . Server::getInstance()->getPluginManager()->getPlugin("TreasureLockpicks")->getDescription()->getVersion()));
        $sender->sendMessage(C::colorize("&r&a * &d/gtl lp <player> [amount] [chance] &8- &7Give lockpicksk"));
        $sender->sendMessage(C::colorize("&r&a * &d/gtl t <player> <type> [amount] &8- &7Give treasures"));
    }

    public function getPermission(): string
    {
        return "command.admin";
    }
}