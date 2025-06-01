<?php

namespace ecstsy\Holograms\Commands;

use ecstsy\Holograms\libs\CortexPE\Commando\BaseCommand;
use ecstsy\Holograms\Utils\HologramManager;
use pocketmine\command\CommandSender;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;

class CreateHologramCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(C::RED . "You must be a player to use this command.");
            return;
        }

        $item = $sender->getInventory()->getItemInHand();
        if ($item->getTypeId() === VanillaItems::AIR()->getTypeId()) {
            $sender->sendMessage(C::RED . "You must be holding an item to create a hologram.");
        } else {
            HologramManager::createHologram($sender, $item);
            $sender->sendMessage(C::GREEN . "Hologram created!");
        }

    }

    public function getPermission(): string {
        return "holograms.create";
    }
}