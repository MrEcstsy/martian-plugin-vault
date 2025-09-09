<?php

namespace wockkinmycup\utilitycore\addons\customArmor\listener;

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\inventory\ArmorInventory;
use pocketmine\inventory\CallbackInventoryListener;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Durable;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\TextFormat as C;

use wockkinmycup\utilitycore\addons\customArmor\sets\GhoulArmor;
use wockkinmycup\utilitycore\addons\customArmor\sets\PhantomSet;
use wockkinmycup\utilitycore\addons\customArmor\sets\SupremeArmor;
use wockkinmycup\utilitycore\addons\customArmor\sets\TitanArmor;
use wockkinmycup\utilitycore\Loader;
use wockkinmycup\utilitycore\utils\Utils;

class CustomArmorListener implements Listener
{

    public array $abilityCooldown = [];

    public static array $activeAbilities = [];

    public function onJoin(PlayerJoinEvent $event): void
    {
        $player = $event->getPlayer();
        $specialSets = ["supreme", "phantom", "ghoul", "titan"];

        $onSlot = function (Inventory $inventory, int $slot, Item $oldItem) use ($specialSets): void {
            if ($inventory instanceof ArmorInventory) {
                $holder = $inventory->getHolder();
                if ($holder instanceof Player) {
                    $newItem = $inventory->getItem($slot);
                    if ($oldItem instanceof Durable && $newItem instanceof Durable) {
                        if (!$oldItem->equals($newItem, false) && $oldItem->getDamage() !== $newItem->getDamage()) {
                            return;
                        }
                    }

                    foreach ($specialSets as $set) {
                        Utils::checkArmorActivation($holder, $inventory, $set);
                    }
                }
            }
        };

        $onContent = function (Inventory $inventory, array $oldContents) use ($onSlot): void {
            foreach ($oldContents as $slot => $oldItem) {
                if (!($oldItem ?? VanillaItems::AIR())->equals($inventory->getItem($slot), !$inventory instanceof ArmorInventory)) {
                    $onSlot($inventory, $slot, $oldItem);
                }
            }
        };

        $player->getInventory()->getListeners()->add(new CallbackInventoryListener($onSlot, $onContent));
        $player->getArmorInventory()->getListeners()->add(new CallbackInventoryListener($onSlot, $onContent));
    }

    public function onEntityDamage(EntityDamageByEntityEvent $event) {
        $entity = $event->getEntity();
        $attacker = $event->getDamager();
        $currentTime = time();

        if ($attacker instanceof Player) {
            $armorInv = $attacker->getArmorInventory();
            $SupremePieces = Utils::getEquippedArmorPieces($armorInv, "supreme");
            $PhantomPieces = Utils::getEquippedArmorPieces($armorInv, "phantom");
            $GhoulPieces = Utils::getEquippedArmorPieces($armorInv, "ghoul");
            $TitanPieces = Utils::getEquippedArmorPieces($armorInv, "titan");
            $EtherealEnforcerPieces = Utils::getEquippedArmorPieces($armorInv, "ethereal_enforcer");
            $itemInHand = $attacker->getInventory()->getItemInHand();

            $abilityNames = ["halloweenify", "Shadowmeld"];
            foreach ($abilityNames as $abilityName) {
                if (isset($this->abilityCooldown[$attacker->getName()][$abilityName])) {
                    $remainingCooldown = $this->abilityCooldown[$attacker->getName()][$abilityName] - $currentTime;

                    if ($remainingCooldown > 0) {
                        return false;
                    }
                }
            }

            if (count($SupremePieces) === 4) {
                $event->setBaseDamage($event->getBaseDamage() * 1.25);
            }

            /*if (count($PhantomPieces) === 4) {
                $event->setBaseDamage($event->getBaseDamage() * 1.25);
                if (Utils::hasTag($itemInHand, "customarmor", "phantom")) {
                    $event->setBaseDamage($event->getBaseDamage() * 1.10);
                }
            }*/

            if (count($GhoulPieces) === 4) {
                $event->setBaseDamage($event->getBaseDamage() * 1.25);
            }

            if (count($TitanPieces) === 4) {
                $event->setBaseDamage($event->getBaseDamage() * 1.15);
            }

            if (count($EtherealEnforcerPieces) === 4) {
                if (Utils::hasActiveAbility($entity, "ethereal_enforcer")) {
                    $event->setBaseDamage($event->getBaseDamage() * 1.25);
                    if (Utils::hasTag($itemInHand, "customarmor", "ethereal_enforcer")) {
                        $event->setBaseDamage($event->getBaseDamage() * 1.15);
                    }
                }
            }

            $attacker->sendActionBarMessage(C::BOLD . C::RED . "Damage: " . C::RESET . C::WHITE . $event->getFinalDamage());
        }

        if ($entity instanceof Player) {
            $armorInv = $entity->getArmorInventory();
            $supremePieces = Utils::getEquippedArmorPieces($armorInv, "supreme");
            $GhoulPieces = Utils::getEquippedArmorPieces($armorInv, "ghoul");
            $TitanPieces = Utils::getEquippedArmorPieces($armorInv, "titan");
            $EtherealEnforcerPieces = Utils::getEquippedArmorPieces($armorInv, "ethereal_enforcer");

            if (count($supremePieces) === 4) {
                if ($event->getCause() === EntityDamageEvent::CAUSE_PROJECTILE) {
                    $event->setBaseDamage($event->getBaseDamage() * 0.9);
                }
            }

            if (count($TitanPieces) === 4) {
                $event->setBaseDamage($event->getBaseDamage() * 0.65);
            }

            if (count($EtherealEnforcerPieces) === 4) {
                $event->setBaseDamage($event->getBaseDamage() * 0.9);

                $randomChance = mt_rand(1, 100);
                $activationProbability = 100;
                if ($randomChance <= $activationProbability) {

                    $EtherealEnforcerAbility = 'Shadowmeld';
                    $cooldownDuration = Utils::getConfiguration(Loader::getInstance(), "customarmor.yml")->getNested("sets.ethereal_enforcer.ability.cooldown");

                    if (isset(self::$activeAbilities[$entity->getName()])) {
                        $playerAbilities = self::$activeAbilities[$entity->getName()];

                        if (is_array($playerAbilities) && isset($playerAbilities[$EtherealEnforcerAbility])) {
                            unset($playerAbilities[$EtherealEnforcerAbility]);
                        }
                    }

                    self::$activeAbilities[$entity->getName()][$EtherealEnforcerAbility] = $currentTime + $cooldownDuration;

                    Loader::getInstance()->getScheduler()->scheduleDelayedTask(
                        new ClosureTask(function () use ($entity, $EtherealEnforcerAbility) {
                            if (is_array(self::$activeAbilities[$entity->getName()])) {
                                unset(self::$activeAbilities[$entity->getName()][$EtherealEnforcerAbility]);
                            }
                        }),
                        5 * 20
                    );

                    if (Utils::hasActiveAbility($entity, $EtherealEnforcerAbility)) {
                        $entity->sendMessage(C::colorize(Utils::getConfiguration(Loader::getInstance(), "customarmor.yml")->getNested("sets.ethereal_enforcer.ability.message", "&r&9Ethereal Enforcer Ability Used!")));
                    }
                }
                return true;
            }

            if (count($GhoulPieces) === 4) {
                $event->setBaseDamage($event->getBaseDamage() * 0.85);

                $randomChance = mt_rand(1, 100);
                $activationProbability = 100;
                if ($randomChance <= $activationProbability) {

                    $ghoulAbility = "halloweenify";
                    $cooldownDuration = Utils::getConfiguration(Loader::getInstance(), "customarmor.yml")->getNested("sets.ghoul.ability.cooldown");

                    if (isset(self::$activeAbilities[$entity->getName()])) {
                        $playerAbilities = self::$activeAbilities[$entity->getName()];

                        if (is_array($playerAbilities) && isset($playerAbilities[$ghoulAbility])) {
                            unset($playerAbilities[$ghoulAbility]);
                        }
                    }

                    self::$activeAbilities[$entity->getName()][$ghoulAbility] = $currentTime + $cooldownDuration;

                    Loader::getInstance()->getScheduler()->scheduleDelayedTask(
                        new ClosureTask(function () use ($entity, $ghoulAbility) {
                            if (is_array(self::$activeAbilities[$entity->getName()])) {
                                unset(self::$activeAbilities[$entity->getName()][$ghoulAbility]);
                            }
                        }),
                        10 * 20
                    );


                    if (Utils::hasActiveAbility($entity, $ghoulAbility)) {
                        $entity->sendMessage(C::colorize(Utils::getConfiguration(Loader::getInstance(), "customarmor.yml")->getNested("sets.ghoul.ability.message", "&r&6Halloweenify Ability Used!")));

                        $baseDamage = $event->getBaseDamage();
                        $lifestealAmount = 2 + 0.05 * $baseDamage;

                        $entity->setHealth($entity->getHealth() + $lifestealAmount > $entity->getMaxHealth() ? $entity->getMaxHealth() : $entity->getHealth() + $lifestealAmount);
                    }
                }
            }
        }
        return true;
    }

    public function blackPeople(PlayerJoinEvent $e) {
        $armorPieces = ['helmet', 'chestplate', 'leggings', 'boots', 'weapon'];
        foreach ($armorPieces as $armor) {
            //$e->getPlayer()->getInventory()->addItem(TitanArmor::give($armor));
        }
    }
}