<?php

namespace wockkinmycup\enchantfragments;

use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\item\VanillaItems;
use pocketmine\world\sound\AnvilFallSound;
use pocketmine\world\sound\XpLevelUpSound;

class EventListener implements Listener {

    /**
     * @priority HIGHEST
     */
    public function onPlayerDropUnbreakingFragment(InventoryTransactionEvent $event): void
    {
        $transaction = $event->getTransaction();
        $actions = array_values($transaction->getActions());
        if (count($actions) === 2) {
            foreach ($actions as $i => $action) {
                $ids = [VanillaItems::DIAMOND_HELMET()->getTypeId(), VanillaItems::DIAMOND_CHESTPLATE()->getTypeId(), VanillaItems::DIAMOND_LEGGINGS()->getTypeId(), VanillaItems::DIAMOND_BOOTS()->getTypeId()];
                if ($action instanceof SlotChangeAction && ($otherAction = $actions[($i + 1) % 2]) instanceof SlotChangeAction && ($itemClickedWith = $action->getTargetItem())->getTypeId() === VanillaItems::IRON_INGOT()->getTypeId() && ($itemClicked = $action->getSourceItem())->getTypeId() !== VanillaItems::AIR()->getTypeId() && in_array($itemClicked->getTypeId(), $ids) && $itemClickedWith->getCount() === 1 && $itemClickedWith->getNamedTag()->getString("enchantmentfragment", "") !== "") {
                    if ($itemClicked->getNamedTag()->getString("enchantmentfragment", "") === "unbreakingv") {
                        $event->getTransaction()->getSource()->sendMessage("§r§c§l(!) §r§cYou cannot do that!");
                        $transaction->getSource()->getWorld()->addSound($transaction->getSource()->getLocation(), new AnvilFallSound());
                        return;
                    }
                    $event->cancel();

                    if ($itemClicked->getNamedTag()->getString("enchantmentfragment", "") !== "unbreakingv") {
                        $itemClicked->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 5));
                        $itemClicked->getNamedTag()->setString("enchantmentfragment", "unbreakingv");
                        $itemClicked->getNamedTag()->setInt("enchantmentfragmenttier", $itemClickedWith->getNamedTag()->getInt("enchantmentfragmenttier"));
                    }

                    Utils::spawnParticleV2($event->getTransaction()->getSource(), "minecraft:villager_happy");
                    $action->getInventory()->setItem($action->getSlot(), $itemClicked);
                    $otherAction->getInventory()->setItem($otherAction->getSlot(), VanillaItems::AIR());
                    $transaction->getSource()->getWorld()->addSound($transaction->getSource()->getLocation(), new XpLevelUpSound(100));
                    return;
                }
            }
        }
    }

    /**
     * @priority HIGHEST
     */
    public function onPlayerDropThornsFragment(InventoryTransactionEvent $event): void
    {
        $transaction = $event->getTransaction();
        $actions = array_values($transaction->getActions());
        if (count($actions) === 2) {
            foreach ($actions as $i => $action) {
                $ids = [VanillaItems::DIAMOND_HELMET()->getTypeId(), VanillaItems::DIAMOND_CHESTPLATE()->getTypeId(), VanillaItems::DIAMOND_LEGGINGS()->getTypeId(), VanillaItems::DIAMOND_BOOTS()->getTypeId()];
                if ($action instanceof SlotChangeAction && ($otherAction = $actions[($i + 1) % 2]) instanceof SlotChangeAction && ($itemClickedWith = $action->getTargetItem())->getTypeId() === VanillaItems::REDSTONE_DUST()->getTypeId() && ($itemClicked = $action->getSourceItem())->getTypeId() !== VanillaItems::AIR()->getTypeId() && in_array($itemClicked->getTypeId(), $ids) && $itemClickedWith->getCount() === 1 && $itemClickedWith->getNamedTag()->getString("enchantmentfragment", "") !== "") {
                    if ($itemClicked->getNamedTag()->getString("enchantmentfragment", "") === "thornsiii") {
                        $event->getTransaction()->getSource()->sendMessage("§r§c§l(!) §r§cYou cannot do that!");
                        $transaction->getSource()->getWorld()->addSound($transaction->getSource()->getLocation(), new AnvilFallSound());
                        return;
                    }
                    $event->cancel();

                    if ($itemClicked->getNamedTag()->getString("enchantmentfragment", "") !== "thornsiii") {
                        $itemClicked->addEnchantment(new EnchantmentInstance(VanillaEnchantments::THORNS(), 3));
                        $itemClicked->getNamedTag()->setString("enchantmentfragment", "thornsiii");
                        $itemClicked->getNamedTag()->setInt("enchantmentfragmenttier", $itemClickedWith->getNamedTag()->getInt("enchantmentfragmenttier"));
                    }

                    Utils::spawnParticleV2($event->getTransaction()->getSource(), "minecraft:villager_happy");
                    $action->getInventory()->setItem($action->getSlot(), $itemClicked);
                    $otherAction->getInventory()->setItem($otherAction->getSlot(), VanillaItems::AIR());
                    $transaction->getSource()->getWorld()->addSound($transaction->getSource()->getLocation(), new XpLevelUpSound(100));
                    return;
                }
            }
        }
    }

    /**
     * @priority HIGHEST
     */
    public function onPlayerDropDepthStriderFragment(InventoryTransactionEvent $event): void
    {
        $transaction = $event->getTransaction();
        $actions = array_values($transaction->getActions());
        if (count($actions) === 2) {
            foreach ($actions as $i => $action) {
                $ids = [VanillaItems::DIAMOND_BOOTS()->getTypeId()];
                if ($action instanceof SlotChangeAction && ($otherAction = $actions[($i + 1) % 2]) instanceof SlotChangeAction && ($itemClickedWith = $action->getTargetItem())->getTypeId() === VanillaItems::LAPIS_LAZULI()->getTypeId() && ($itemClicked = $action->getSourceItem())->getTypeId() !== VanillaItems::AIR()->getTypeId() && in_array($itemClicked->getTypeId(), $ids) && $itemClickedWith->getCount() === 1 && $itemClickedWith->getNamedTag()->getString("enchantmentfragment", "") !== "") {
                    if ($itemClicked->getNamedTag()->getString("enchantmentfragment", "") === "depthstrideriii") {
                        $event->getTransaction()->getSource()->sendMessage("§r§c§l(!) §r§cYou cannot do that!");
                        $transaction->getSource()->getWorld()->addSound($transaction->getSource()->getLocation(), new AnvilFallSound());
                        return;
                    }
                    $event->cancel();

                    if ($itemClicked->getNamedTag()->getString("enchantmentfragment", "") !== "depthstrideriii") {
                        $itemClicked->addEnchantment(new EnchantmentInstance(StringToEnchantmentParser::getInstance()->parse("depth_strider"), 3));
                        $itemClicked->getNamedTag()->setString("enchantmentfragment", "depthstrideriii");
                        $itemClicked->getNamedTag()->setInt("enchantmentfragmenttier", $itemClickedWith->getNamedTag()->getInt("enchantmentfragmenttier"));
                    }

                    Utils::spawnParticleV2($event->getTransaction()->getSource(), "minecraft:villager_happy");
                    $action->getInventory()->setItem($action->getSlot(), $itemClicked);
                    $otherAction->getInventory()->setItem($otherAction->getSlot(), VanillaItems::AIR());
                    $transaction->getSource()->getWorld()->addSound($transaction->getSource()->getLocation(), new XpLevelUpSound(100));
                    return;
                }
            }
        }
    }

    /**
     * @priority HIGHEST
     */
    public function onPlayerDropLootingFragment(InventoryTransactionEvent $event): void
    {
        $transaction = $event->getTransaction();
        $actions = array_values($transaction->getActions());
        if (count($actions) === 2) {
            foreach ($actions as $i => $action) {
                $ids = [VanillaItems::DIAMOND_SWORD()->getTypeId()];
                if ($action instanceof SlotChangeAction && ($otherAction = $actions[($i + 1) % 2]) instanceof SlotChangeAction && ($itemClickedWith = $action->getTargetItem())->getTypeId() === VanillaItems::GOLD_INGOT()->getTypeId() && ($itemClicked = $action->getSourceItem())->getTypeId() !== VanillaItems::AIR()->getTypeId() && in_array($itemClicked->getTypeId(), $ids) && $itemClickedWith->getCount() === 1 && $itemClickedWith->getNamedTag()->getString("enchantmentfragment", "") !== "") {
                    if ($itemClicked->getNamedTag()->getString("enchantmentfragment", "") === "lootingv") {
                        $event->getTransaction()->getSource()->sendMessage("§r§c§l(!) §r§cYou cannot do that!");
                        $transaction->getSource()->getWorld()->addSound($transaction->getSource()->getLocation(), new AnvilFallSound());
                        return;
                    }
                    $event->cancel();

                    if ($itemClicked->getNamedTag()->getString("enchantmentfragment", "") !== "lootingv") {
                        $itemClicked->addEnchantment(new EnchantmentInstance(StringToEnchantmentParser::getInstance()->parse("looting"), 5));
                        $itemClicked->getNamedTag()->setString("enchantmentfragment", "lootingv");
                        $itemClicked->getNamedTag()->setInt("enchantmentfragmenttier", $itemClickedWith->getNamedTag()->getInt("enchantmentfragmenttier"));
                    }

                    Utils::spawnParticleV2($event->getTransaction()->getSource(), "minecraft:villager_happy");
                    $action->getInventory()->setItem($action->getSlot(), $itemClicked);
                    $otherAction->getInventory()->setItem($otherAction->getSlot(), VanillaItems::AIR());
                    $transaction->getSource()->getWorld()->addSound($transaction->getSource()->getLocation(), new XpLevelUpSound(100));
                    return;
                }
            }
        }
    }
}