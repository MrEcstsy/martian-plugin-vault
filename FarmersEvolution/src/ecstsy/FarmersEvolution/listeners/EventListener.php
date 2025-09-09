<?php

namespace ecstsy\FarmersEvolution\listeners;

use ecstsy\FarmersEvolution\utils\Utils;
use pocketmine\block\Crops;
use pocketmine\event\block\BlockGrowEvent;
use pocketmine\event\Listener;

class EventListener implements Listener {

    public function onCropGrow(BlockGrowEvent $event): void {
        $block = $event->getBlock();

        if ($block instanceof Crops) {
            $cropName = strtolower($block->getName());

            $cropConfig = Utils::getConfiguration("config.yml")->getAll();

            if (!$cropConfig) return;

            $conditionsMet = Utils::checkGrowthConditions($block, $cropConfig[$cropName]['conditions']);

            if (!$conditionsMet) {
                $event->cancel();
                return;
            }

            $growthModifier = $cropConfig[$cropName]['growth-speed-modifier'] ??  1.0;
        }
    }
}