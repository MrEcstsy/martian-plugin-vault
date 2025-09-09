<?php

namespace wockkinmycup\utilitycore\tasks;

use pocketmine\entity\effect\VanillaEffects;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use wockkinmycup\utilitycore\addons\warps\WarpAPI;
use wockkinmycup\utilitycore\utils\Utils;
use xtcy\ArcaneCore\Loader;

class TeleportationTask extends Task
{

    private Position $start_position;
    private PluginBase $loader;
    private Player $player;
    private string $warp;
    private int $timer;

    public function __construct(PluginBase $loader, Player $player, string $warp)
    {
        $this->warp = $warp;
        $this->player = $player;
        $this->start_position = $player->getPosition();
        $this->timer = Utils::getConfiguration($loader, "config.yml")->get("warp-delay");
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
            $player->sendTip(TextFormat::colorize("&r&l&3SERVER &8» &r&7Teleport commencing in &c" . $this->timer . " seconds \n        &r&cDon't Move!"));
            $this->timer--;
        } else {
            $player->sendMessage(TextFormat::colorize("&r&l&3SERVER &8» &r&4Pending Teleport request cancelled."));
            $player->getEffects()->remove(VanillaEffects::NAUSEA());
            $this->getHandler()->cancel();
            return;
        }

        if ($this->timer === 0) {
            $player->getEffects()->remove(VanillaEffects::NAUSEA());
            $player->teleport(WarpAPI::getWarp($this->warp));
            $player->sendTip(TextFormat::colorize("&r&l&3SERVER &8» &r&7Teleportation commencing..."));
            $this->getHandler()->cancel();
            return;
        }
    }
}