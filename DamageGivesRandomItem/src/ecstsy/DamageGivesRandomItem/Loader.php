<?php

namespace ecstsy\DamageGivesRandomItem;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;

class Loader extends PluginBase implements Listener {
    use SingletonTrait;

    public function onLoad(): void {
        self::setInstance($this);
    }

    public function onEnable(): void {

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onHit(EntityDamageEvent $event): void {
        $entity = $event->getEntity();

        if ($entity instanceof Player) {
            $items = VanillaItems::getAll();
            $randomItem = $items[array_rand($items)];

            $entity->getWorld()->dropItem($entity->getPosition(), $randomItem->setCount(mt_rand(1, 64)));
        }
    }
}