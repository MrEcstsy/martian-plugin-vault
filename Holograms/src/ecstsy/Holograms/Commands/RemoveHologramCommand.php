<?php

namespace ecstsy\Holograms\Commands;

use ecstsy\Holograms\libs\CortexPE\Commando\args\IntegerArgument;
use ecstsy\Holograms\libs\CortexPE\Commando\BaseCommand;
use ecstsy\Holograms\Utils\HologramManager;
use pocketmine\command\CommandSender;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;

class RemoveHologramCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());

        $this->registerArgument(0, new IntegerArgument("hologram_id", false));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(C::RED . "You must be a player to use this command.");
            return;
        }
        
        $entityId = $args["hologram_id"];
        if (HologramManager::removeHologram($sender, $entityId)) {
            $sender->sendMessage(C::colorize("&r&l&c(!) &r&cSuccessfully removed hologram &r&l&c" . $entityId));
        } else {
            $sender->sendMessage(C::colorize("&r&l&c(!) &r&cNo hologram found with ID &r&l&c" . $entityId));
        }

    }

    public function getPermission(): string {
        return "holograms.remove";
    }
}