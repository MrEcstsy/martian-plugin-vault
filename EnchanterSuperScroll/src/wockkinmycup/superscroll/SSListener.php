<?php

namespace wockkinmycup\superscroll;

use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;
use pocketmine\world\sound\AnvilFallSound;
use pocketmine\world\sound\XpLevelUpSound;
use wockkinmycup\superscroll\utils\Scrolls;
use wockkinmycup\utilitycore\utils\Utils;
use wockkinmycup\superscroll\utils\Scrolls as SSUtils;

class SSListener implements Listener {

    /*
     * @priority HIGHEST
     */
    public function onDropEnchanterScroll(InventoryTransactionEvent $event): void
    {
        $transaction = $event->getTransaction();
        $actions = array_values($transaction->getActions());
        if (count($actions) === 2) {
            foreach ($actions as $i => $action) {
                $ids = [VanillaItems::DIAMOND_SWORD()->getTypeId()];
                if ($action instanceof SlotChangeAction && ($otherAction = $actions[($i + 1) % 2]) instanceof SlotChangeAction && ($itemClickedWith = $action->getTargetItem())->getTypeId() === StringToItemParser::getInstance()->parse("empty_map")->getTypeId() && ($itemClicked = $action->getSourceItem())->getTypeId() !== VanillaItems::AIR()->getTypeId() && in_array($itemClicked->getTypeId(), $ids) && $itemClickedWith->getCount() === 1 && $itemClickedWith->getNamedTag()->getString("superscrolls", "") !== "") {
                    if (Loader::getInstance()->getConfig()->get("one-time")  === true) {
                        if ($itemClicked->getNamedTag()->getString("superscrolls", "") === "enchanter") {
                            $event->getTransaction()->getSource()->sendMessage("§r§c§l(!) §r§cYou cannot do that!");
                            $transaction->getSource()->getWorld()->addSound($transaction->getSource()->getLocation(), new AnvilFallSound());
                            return;
                        }
                    }
                    $event->cancel();

                    if ($itemClicked->getNamedTag()->getString("superscrolls", "") !== "enchanter") {
                        SSUtils::increaseEnchantments($itemClicked);
                        $itemClicked->getNamedTag()->setString("superscrolls", "enchanter");
                    }

                    Utils::spawnParticle($event->getTransaction()->getSource(), "minecraft:villager_happy");
                    $action->getInventory()->setItem($action->getSlot(), $itemClicked);
                    $otherAction->getInventory()->setItem($otherAction->getSlot(), VanillaItems::AIR());
                    $transaction->getSource()->getWorld()->addSound($transaction->getSource()->getLocation(), new XpLevelUpSound(100));
                    return;
                }
            }
        }
    }
}