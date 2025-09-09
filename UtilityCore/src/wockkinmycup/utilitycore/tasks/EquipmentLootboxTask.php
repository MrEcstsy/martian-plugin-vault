<?php

namespace wockkinmycup\utilitycore\tasks;

use muqsit\invmenu\InvMenu;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use wockkinmycup\utilitycore\addons\customArmor\sets\GhoulArmor;
use wockkinmycup\utilitycore\addons\customArmor\sets\PhantomSet;
use wockkinmycup\utilitycore\addons\customArmor\sets\SupremeArmor;
use wockkinmycup\utilitycore\addons\customArmor\sets\TitanArmor;
use wockkinmycup\utilitycore\items\Vouchers;
use wockkinmycup\utilitycore\utils\Utils;

class EquipmentLootboxTask extends Task {

    /** @var InvMenu */
    private InvMenu $invMenu;

    /** @var int */
    private int $glassCount;

    /** @var int */
    private int $lastGlassUpdateTick;

    protected Player $player;

    public function __construct(InvMenu $invMenu, Player $player, int $initialGlassCount = 7) {
        $this->invMenu = $invMenu;
        $this->glassCount = $initialGlassCount;
        $this->lastGlassUpdateTick = 0;
        $this->player = $player;
        $this->updateGlass($this->glassCount);
    }

    public function onRun(): void {
        if (empty($this->invMenu->getInventory()->getViewers())) {
            $this->getHandler()->cancel();
            return;
        }

        $set = ["helmet", "chestplate", "leggings", "boots"];
        $items = [];

        foreach ($set as $piece) {
            $items[] = PhantomSet::give($piece);
            $items[] = GhoulArmor::give($piece);
            $items[] = SupremeArmor::give($piece);
            $items[] = TitanArmor::give($piece);
        }

        $tick = Server::getInstance()->getTick();

        if ($tick % 20 === 0 && $this->glassCount > 0) {
            $this->glassCount--;
            $this->updateGlass($this->glassCount);
            $this->lastGlassUpdateTick = $tick;

            if ($this->glassCount <= 1) {
                $this->getHandler()->cancel();
                return;
            }
        }

        $delayTicks = 5;
        if ($tick - $this->lastGlassUpdateTick >= $delayTicks) {
            $randomIndex = array_rand($items);
            $randomItem = $items[$randomIndex];
            $this->invMenu->getInventory()->setItem(2, $randomItem);
            Utils::playSound($this->player, "random.click");

        }
    }

    private function updateGlass(int $glassCount): void {
        $this->invMenu->getInventory()->setItem(0, VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::BLACK)->asItem()->setCount($glassCount)->setCustomName(" "));
        $this->invMenu->getInventory()->setItem(1, VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::BLACK)->asItem()->setCustomName(" "));
        $this->invMenu->getInventory()->setItem(3, VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::BLACK)->asItem()->setCustomName(" "));
        $this->invMenu->getInventory()->setItem(4, VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::BLACK)->asItem()->setCount($glassCount)->setCustomName(" "));
    }
}
