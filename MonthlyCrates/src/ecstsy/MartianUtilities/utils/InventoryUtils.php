<?php

declare(strict_types=1);

namespace ecstsy\MartianUtilities\utils;

use pocketmine\inventory\Inventory;
use pocketmine\item\Item;

final class InventoryUtils {

    /**
     * Fill the borders of the inventory with gray glass.
     *
     * @param Inventory $inventory
     */
    public static function fillBorders(Inventory $inventory, Item $glassType, array $excludedSlots = []): void
    {
        $size = $inventory->getSize();
        $rows = intdiv($size, 9); 
    
        for ($row = 0; $row < $rows; $row++) {
            for ($col = 0; $col < 9; $col++) {
                $slot = $row * 9 + $col;
    
                if (!in_array($slot, $excludedSlots) && ($col === 0 || $col === 8 || $row === 0 || $row === $rows - 1)) {
                    $item = clone $glassType;
                    $item->setCustomName(" ");
                    $inventory->setItem($slot, $item);
                }
            }
        }
    }

    /**
     * Fill the entire inventory a block, excluding the specified slots.
     *
     * @param Inventory $inventory
     * @param Item      $glassType
     * @param array     $excludedSlots
     */

    public static function fillInventory(Inventory $inventory, Item $glassType, array $excludedSlots = []): void
    {
        $size = $inventory->getSize();

        for ($slot = 0; $slot < $size; $slot++) {
            if (!in_array($slot, $excludedSlots)) {
                $inventory->setItem($slot, $glassType->setCustomName(" "));
            }
        }
    }
}
