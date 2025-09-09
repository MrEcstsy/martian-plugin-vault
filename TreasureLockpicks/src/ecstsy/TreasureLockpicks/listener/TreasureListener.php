<?php

namespace ecstsy\TreasureLockpicks\listener;

use ecstsy\TreasureLockpicks\Loader;
use ecstsy\TreasureLockpicks\utils\TLUtils;

use pocketmine\utils\TextFormat as C;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;

class TreasureListener implements Listener {

    public function onPlace(BlockPlaceEvent $event): void {
        $item = $event->getItem();
        $tag = $item->getNamedTag();

        if ($tag->getTag("treasure")) {
            $event->cancel();
        }

        if ($tag->getTag("lockpick")) {
            $event->cancel();
        }
    }

    public function onUse(PlayerItemUseEvent $event): void {
        $player = $event->getPlayer();
        $item = $event->getItem();
        $identifiers = TLUtils::getTreasureIdentifiers();
    
        if (($tag = $item->getNamedTag()->getTag("treasure")) && ($lockTag = $item->getNamedTag()->getTag("lock_status"))) {
            foreach ($identifiers as $identifier) {
                if ($tag->getValue() === $identifier) {
                    if ($lockTag->getValue() === "unlocked") {
                        $treasureConfig = Loader::getInstance()->getConfig()->getNested("treasures.$identifier.items");
                        $totalChance = 0;
                        $possibleRewards = [];
        
                        foreach ($treasureConfig as $reward) {
                            $totalChance += $reward["chance"];
                            $possibleRewards[$totalChance] = $reward;
                        }
        
                        $randomNumber = mt_rand(1, $totalChance);
        
                        foreach ($possibleRewards as $chance => $reward) {
                            if ($randomNumber <= $chance) {
                                $itemReward = StringToItemParser::getInstance()->parse($reward["item"]);
                                $amount = $reward["amount"] ?? 1;
                                $itemReward->setCount($amount);
        
                                if (!empty($reward["name"])) {
                                    $itemReward->setCustomName(C::colorize($reward["name"]));
                                }
        
                                if (!empty($reward["lore"])) {
                                    $lore = [];
                                    foreach ($reward["lore"] as $line) {
                                        $lore[] = C::colorize($line);
                                    }
                                    $itemReward->setLore($lore);
                                }
        
                                if (!empty($reward["enchantments"])) {
                                    foreach ($reward["enchantments"] as $enchantment) {
                                        $ench = StringToEnchantmentParser::getInstance()->parse($enchantment["enchant"]);
                                        $level = $enchantment["level"];
                                        $itemReward->addEnchantment(new EnchantmentInstance($ench, $level));
                                    }
                                }
        
                                if (!empty($reward["nbt"]["tag"]) && !empty($reward["nbt"]["value"])) {
                                    $tag = $reward["nbt"]["tag"];
                                    $value = $reward["nbt"]["value"];
                                    $itemReward->getNamedTag()->setString($tag, $value);
                                }

                                $item->pop();
                                $player->getInventory()->setItemInHand($item);
                                $player->getInventory()->addItem($itemReward);
                                break;
                            }
                        }
                    } else {
                        $player->sendMessage(C::colorize("&r&l&c(!) &r&cThis treasure is locked!"));
                    }
                }
            }
        }
    }    

    public function onDrop(InventoryTransactionEvent $event): void {
        $transaction = $event->getTransaction();
        $actions = array_values($transaction->getActions());
        $config = Loader::getInstance()->getConfig();
    
        if (count($actions) === 2) {
            $lockpickApplied = false; // Flag to track if the lockpick has been applied
            foreach ($actions as $i => $action) {
                if ($action instanceof SlotChangeAction && ($oAction = $actions[($i + 1) % 2]) instanceof SlotChangeAction
                    && ($itemClickedWith = $action->getTargetItem())->getTypeId() === StringToItemParser::getInstance()->parse(Loader::getInstance()->getConfig()->getNested("lockpick.type"))->getTypeId()
                    && ($itemClicked = $action->getSourceItem())->getTypeId() !== VanillaItems::AIR()->getTypeId()
                    && ($identifiers = TLUtils::getTreasureIdentifiers())
                    && in_array($itemClicked->getTypeId(), array_map(function ($identifier) use ($config) {
                        return StringToItemParser::getInstance()->parse($config->getNested("treasures.$identifier.type"))->getTypeId();
                    }, $identifiers), true)
                    && $itemClickedWith->getCount() === 1
                    && $itemClicked->getCount() === 1 
                    && $itemClickedWith->getNamedTag()->getTag("lockpick")
                    && !$lockpickApplied // Check if the lockpick hasn't been applied yet
                ) {
                    $lockChance = $itemClickedWith->getNamedTag()->getInt("successChance");
    
                    if ($lockChance !== 0) {
                        if (mt_rand(1, 100) <= $lockChance) {
                            $tag = $itemClicked->getNamedTag();
                            $tag->removeTag("lock_status");
                            $tag->setString("lock_status", "unlocked");
                            $lore = $itemClicked->getLore();
                            $index = array_search("§r§l§cLocked", $lore);
                            if ($index !== false) {
                                $lore[$index] = "§r§l§aUnlocked";
                            }
                            $itemClicked->setLore($lore);
                            $oAction->getInventory()->setItem($oAction->getSlot(), VanillaItems::AIR());
                            $transaction->getSource()->getInventory()->setItem($action->getSlot(), $itemClicked);
                            $lockpickApplied = true; // Set the flag to true to indicate that the lockpick has been applied
                        } else {
                            $oAction->getInventory()->setItem($oAction->getSlot(), VanillaItems::AIR());
                        }
                    }
    
                    $event->cancel();
                }
            }
        }
    } 
}