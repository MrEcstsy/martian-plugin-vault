<?php

namespace ecstsy\MartianUtilities\entity;

use pocketmine\block\Block;
use pocketmine\block\Slab;
use pocketmine\block\Stair;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\player\Player;

abstract class BaseHostileEntity extends Living {
    
    protected ?Player $target = null;
    protected bool $persistent = false;

    public function entityBaseTick(int $tickDiff = 1): bool {
        $hasParentTicked = parent::entityBaseTick($tickDiff);
        $this->updateAI();
        return $hasParentTicked;
    }

    public function findClosestPlayer(): ?Player {
        $closestDistance = 15;
        $closestPlayer = null;

        foreach ($this->getWorld()->getPlayers() as $player) {
            if ($player->isOnline()) {
                $distance = $player->getPosition()->distance($this->getPosition());
                if ($distance < $closestDistance) {
                    $closestDistance = $distance;
                    $closestPlayer = $player;
                }
            }
        }

        return $closestPlayer;
    }

    protected function updateAI(): void {
        if (!$this->hasTarget()) {
            $this->target = $this->findClosestPlayer();
        }

        if ($this->hasTarget()) {
            $this->followTarget();
            if ($this->target !== null) {
                $this->lookAt($this->target->getPosition()->add(0, $this->getEyeHeight(), 0));

                foreach ($this->getPlayersInRange(1) as $player) {
                    $this->attackPlayer($player);
                }
            }
        } else {
            $this->lookAt($this->getPosition()->add($this->getMotion()->x, 0, $this->getMotion()->z));
        }

        if ($this->shouldJump()) {
            $this->jump();
        }
    }

    public function attackPlayer(Player $player): void {
        $player->attack(new EntityDamageEvent($this, EntityDamageEvent::CAUSE_ENTITY_ATTACK, 6));
    }

    public function followTarget(): void {
        if ($this->target instanceof Player) {
            $distance = $this->target->getPosition()->distance($this->getPosition());
            if ($distance <= 10) {
                $direction = $this->target->getPosition()->subtractVector($this->getPosition()->asVector3())->normalize();
                $this->motion->x = $direction->x * 0.2;
                $this->motion->z = $direction->z * 0.2;
            } else {
                $this->clearTarget();
            }
        }
    }

    public function clearTarget(): void {
        $this->target = null;
    }

    public function hasTarget(): bool {
        return $this->target instanceof Player;
    }

    public function getPlayersInRange(float $radius): array {
        $players = [];
        foreach ($this->getWorld()->getNearbyEntities($this->getBoundingBox()->expandedCopy($radius, $radius, $radius)) as $entity) {
            if ($entity instanceof Player && $entity->isOnline()) {
                $players[] = $entity;
            }
        }
        return $players;
    }

    public function jump(): void {
        $this->motion->y = $this->gravity * $this->getJumpMultiplier();
    }

    public function shouldJump(): bool {
        $frontBlock = $this->getFrontBlock();
        return $frontBlock->isSolid() || $frontBlock instanceof Stair || $frontBlock instanceof Slab;
    }

    public function getFrontBlock($y = 0): Block {
        $direction = $this->getDirectionVector();
        $pos = $this->getPosition()->add($direction->x, $y, $direction->z)->floor();
        return $this->getWorld()->getBlockAt($pos->x, $pos->y, $pos->z);
    }

    public function getJumpMultiplier(): int {
        $frontBlock = $this->getFrontBlock();
        $belowBlock = $this->getFrontBlock(-0.5);
        $belowFrontBlock = $this->getFrontBlock(-1);

        if ($frontBlock->isSolid()) {
            return 3;
        }

        if ($frontBlock instanceof Slab || $belowBlock instanceof Slab || $belowFrontBlock instanceof Slab) {
            return 10;
        }

        if ($frontBlock instanceof Stair || $belowBlock instanceof Stair || $belowFrontBlock instanceof Stair) {
            return 10;
        }

        return 5;
    }

    public function setPersistence(bool $persistent): self {
        $this->persistent = $persistent;
        return $this;
    }
}