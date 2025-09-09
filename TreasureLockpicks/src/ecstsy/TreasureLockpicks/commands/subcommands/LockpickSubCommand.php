<?php

namespace ecstsy\TreasureLockpicks\commands\subcommands;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use ecstsy\TreasureLockpicks\utils\TLUtils;

use pocketmine\utils\TextFormat as C;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class LockpickSubCommand extends BaseSubCommand {

    public function prepare(): void {
        $this->setPermission("command.admin");
        $this->registerArgument(0, new RawStringArgument("name", false));
        $this->registerArgument(1, new IntegerArgument("amount", true));
        $this->registerArgument(2, new IntegerArgument("chance", true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            return;
        }

        if (isset($args["name"])) {
            $player = TLUtils::getPlayerByPrefix($args["name"]);

            if ($player instanceof Player) {
                if (isset($args["amount"])) {
                    if (isset($args["chance"])) {
                        $player->getInventory()->addItem(TLUtils::getLockpick($player, $args["amount"], $args["chance"]));
                        $player->sendMessage(C::colorize("&r&l&a(!) &r&aSuccessfully given " . $args["amount"] . "x to " . $player->getName() . "!"));
                    }
                }
            } else {
                $sender->sendMessage(C::colorize("&r&l&c(!) &r&cInvalid player"));
            }
        } else {
            $sender->sendMessage(C::colorize("&r&l&c(!) &r&cInvalid name"));
        }
    }
}