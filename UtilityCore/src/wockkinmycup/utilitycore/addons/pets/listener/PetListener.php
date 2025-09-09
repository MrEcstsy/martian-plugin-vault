<?php

namespace wockkinmycup\utilitycore\addons\pets\listener;

use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;

class PetListener implements Listener
{

    public static array $petCooldowns = [];

    public static array $abilityCooldowns = [];

    public function onPlace(BlockPlaceEvent $e) {
        $i = $e->getItem();
        $t = $i->getNamedTag();
        if ($t->getTag('pet')) {
            $e->cancel();
        }
    }
}