<?php

namespace ecstsy\RPGSkills\Listeners;

use ecstsy\RPGSkills\Loader;
use ecstsy\RPGSkills\Utils\Utils;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\inventory\PlayerInventory;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\scheduler\ClosureTask;

class SkillListener implements Listener {

    public function onInventoryTransaction(InventoryTransactionEvent $event): void {
        $player = $event->getTransaction()->getSource();

        foreach ($event->getTransaction()->getActions() as $action) {
            if ($action instanceof SlotChangeAction) {
                $inventory = $action->getInventory();

                // Ensure this is the player's inventory
                if ($inventory instanceof PlayerInventory) {
                    $item = $action->getTargetItem();

                    // Check if the item has a skill requirement (like a Diamond Pickaxe)
                    $updatedItem = Utils::updateItemLore($player, $item);

                    // Update the item in the player's inventory
                    $inventory->setItem($action->getSlot(), $updatedItem);
                }
            }
        }
    }
}
