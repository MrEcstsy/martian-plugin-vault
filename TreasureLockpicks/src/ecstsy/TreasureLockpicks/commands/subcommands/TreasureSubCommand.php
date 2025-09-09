<?php

namespace ecstsy\TreasureLockpicks\commands\subcommands;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use ecstsy\TreasureLockpicks\utils\TLUtils;

use pocketmine\utils\TextFormat as C;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class TreasureSubCommand extends BaseSubCommand {

    public function prepare(): void {

        $this->setPermission("command.admin");
        $this->registerArgument(0, new RawStringArgument("name", false));
        $this->registerArgument(1, new RawStringArgument("type", true));
        $this->registerArgument(2, new IntegerArgument("amount", true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            return;
        }

        if (isset($args["name"])) {
            $player = TLUtils::getPlayerByPrefix($args["name"]);
            
            if ($player !== null) {
                if (isset($args["type"])) {
                    if (isset($args["amount"])) {
                        $player->getInventory()->addItem(TLUtils::getTreasure($args["type"], $args["amount"]));
                        $player->sendMessage(C::colorize("&r&l&a(!) &r&aYou got a &b" . $args["amount"] . "x " . $args["type"] . " &atreasure!"));
                    }
                }
            }
        }
    }
}