<?php

namespace wockkinmycup\DynamicCombat;

use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\TextFormat;
use wockkinmycup\DynamicCombat\items\Armory;
use wockkinmycup\DynamicCombat\utils\CustomIds;
use wockkinmycup\utilitycore\utils\Utils;
use wockkinmycup\DynamicCombat\utils\Utils as DynamicUtils;

class CombatListener implements Listener {

    private array $abilityCooldowns = [];

    public static array $activeAbilities = [];

    public function onUse(PlayerItemUseEvent $ev): bool
    {
        $p = $ev->getPlayer();
        $item = $ev->getItem();
        $tag = $item->getNamedTag();
        $currentTime = time();
        $cfg = Utils::getConfiguration(Loader::getInstance(), "config.yml");

        if ($tag->getTag("dynamic_weapon")) {
            $weaponTag = $tag->getString("dynamic_weapon");
            if ($weaponTag === "poison") {
                $abilityKey = "poison";
                if (isset($this->abilityCooldowns[$p->getName()][$abilityKey])) {
                    $remainingCooldown = $this->abilityCooldowns[$p->getName()][$abilityKey] - $currentTime;
                    if ($remainingCooldown > 0) {
                        $cooldownMsg = $cfg->getNested("messages.cooldown", "&r&l&c[!] &r&7Please wait {cooldown} before using this ability again.");
                        $cooldownMsg = str_replace("{cooldown}", Utils::translateTime($remainingCooldown), $cooldownMsg);
                        $p->sendMessage(TextFormat::colorize($cooldownMsg));
                        return false;
                    }
                }
                $this->abilityCooldowns[$p->getName()][$abilityKey] = $currentTime + $cfg->getNested("cooldowns.poison");
                self::$activeAbilities[$p->getName()][$abilityKey] = true;
                Loader::getInstance()->getScheduler()->scheduleDelayedTask(
                    new ClosureTask(function () use ($p, $abilityKey) {
                        unset(self::$activeAbilities[$p->getName()][$abilityKey]);
                    }),
                    5 * 20
                );
				$p->sendMessage(TextFormat::colorize(Utils::getConfiguration(Loader::getInstance(), "items.yml")->getNested("weapons.poison.activation")));
            } elseif ($weaponTag === "frostbite") {
                $abilityKey = "frostbite";
                if (isset($this->abilityCooldowns[$p->getName()][$abilityKey])) {
                    $remainingCooldown = $this->abilityCooldowns[$p->getName()][$abilityKey] - $currentTime;
                    if ($remainingCooldown > 0) {
                        $cooldownMsg = $cfg->getNested("messages.cooldown", "&r&l&c[!] &r&7Please wait {cooldown} before using this ability again.");
                        $cooldownMsg = str_replace("{cooldown}", Utils::translateTime($remainingCooldown), $cooldownMsg);
                        $p->sendMessage(TextFormat::colorize($cooldownMsg));
                        return false;
                    }
                }
                $this->abilityCooldowns[$p->getName()][$abilityKey] = $currentTime + $cfg->getNested("cooldowns.frostbite");
                self::$activeAbilities[$p->getName()][$abilityKey] = true;
                Loader::getInstance()->getScheduler()->scheduleDelayedTask(
                    new ClosureTask(function () use ($p, $abilityKey) {
                        unset(self::$activeAbilities[$p->getName()][$abilityKey]);
                    }),
                    5 * 20
                );
                $p->sendMessage(TextFormat::colorize(Utils::getConfiguration(Loader::getInstance(), "items.yml")->getNested("weapons.frostbite.activation")));
            }

        }
        return true;
    }

    public function onAttack(EntityDamageByEntityEvent $ev) {
        $damager = $ev->getDamager();
        $entity = $ev->getEntity();

        if ($damager instanceof Player) {
            if (DynamicUtils::hasAbilityActive($damager, "poison")) {
                if ($entity instanceof Player) {
                    $entity->getEffects()->add(new EffectInstance(VanillaEffects::POISON(), 5 * 20, 3, false));
                    if ($entity->getEffects()->has(VanillaEffects::POISON())) {
                        $entity->sendActionBarMessage(TextFormat::RED . "You have been poisoned!");
                    }
                }
            } elseif (DynamicUtils::hasAbilityActive($damager, "frostbite")) {
                if ($entity instanceof Player) {
                    $entity->getEffects()->add(new EffectInstance(VanillaEffects::SLOWNESS(), 5 + 20, 4, false));
                    if ($entity->getEffects()->has(VanillaEffects::SLOWNESS())) {
                        $entity->sendActionBarMessage(TextFormat::RED . "You have Frostbite!");
                    }
                }
            }
        }
    }

    public function join(PlayerJoinEvent $e) {
        $item = CustomiesItemFactory::getInstance()->get(CustomIds::EMERALD_SWORD);
        $e->getPlayer()->getInventory()->addItem($item);
    }
}
