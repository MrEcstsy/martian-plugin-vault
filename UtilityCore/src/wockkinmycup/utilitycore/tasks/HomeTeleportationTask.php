<?php

namespace wockkinmycup\utilitycore\tasks;

use pocketmine\entity\effect\VanillaEffects;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;

class HomeTeleportationTask extends Task
{

    private Position $start_position;
    private PluginBase $loader;
    private Player $player;
    public Position $position;
    private int $timer;

    public function __construct(PluginBase $loader, Player $player, Position $position, int $timer)
    {
        $this->position = $position;
        $this->player = $player;
        $this->start_position = $player->getPosition();
        $this->timer = $timer;
        $loader->getScheduler()->scheduleDelayedRepeatingTask($this, 20, 20);
    }

    public function onRun(): void
    {
        $player = $this->player;
        if (!$player->isOnline()) {
            $this->getHandler()->cancel();
            return;
        }

        if ($player->getPosition()->getFloorX() === $this->start_position->getFloorX() and
            $player->getPosition()->getFloorY() === $this->start_position->getFloorY() and
            $player->getPosition()->getFloorZ() === $this->start_position->getFloorZ()) {
            $player->sendTip(TextFormat::colorize("&r&l&3SERVER &8» &r&7Teleport commencing in &c" . $this->timer . " seconds \n                &r&cDon't Move!"));
            $this->timer--;
        } else {
            $player->sendMessage(TextFormat::colorize("&r&l&3SERVER &8» &r&4Pending Teleport request cancelled."));
            $player->getEffects()->remove(VanillaEffects::NAUSEA());
            $this->getHandler()->cancel();
            return;
        }

        if ($this->timer === 0) {
            $player->getEffects()->remove(VanillaEffects::NAUSEA());
            $player->teleport($this->position);
            $player->sendTip(TextFormat::colorize("&r&l&3SERVER &8» &r&7Teleportation commencing..."));
            $this->getHandler()->cancel();
            return;
        }
    }
}