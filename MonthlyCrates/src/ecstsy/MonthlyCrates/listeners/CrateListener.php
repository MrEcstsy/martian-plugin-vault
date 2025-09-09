<?php

declare(strict_types=1);

namespace ecstsy\MonthlyCrates\listeners;

use ecstsy\MonthlyCrates\Loader;
use ecstsy\MonthlyCrates\utils\Screens;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemUseEvent;

final class CrateListener implements Listener {

    public function onBlockPlace(BlockPlaceEvent $event): void {
        $item = $event->getItem();
        $tag = $item->getNamedTag();

        if ($tag->getTag("MartianMonthlyCrate") !== null) {
            $event->cancel();
        }
    }

    public function onItemUseEvent(PlayerItemUseEvent $event): void {
        $player = $event->getPlayer();
        $item = $event->getItem();
        $tag = $item->getNamedTag();
        $crateTag = $tag->getCompoundTag("MartianMonthlyCrate");
        $lang = Loader::getLanguageManager();

        if ($crateTag === null || !$crateTag->getTag("monthly_crate")) {
            return;
        }

        $monthlyCrateId = $crateTag->getString("monthly_crate");

        $item->pop();
        $player->getInventory()->setItemInHand($item);

        $menu = Screens::getCrateOpenMenu($player, $monthlyCrateId);

        if ($menu === null) {
            return;
        }

        $menu->send($player);
    }
}