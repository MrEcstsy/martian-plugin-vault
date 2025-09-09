<?php

declare(strict_types=1);

namespace ecstsy\MonthlyCrates\utils;

use ecstsy\MartianUtilities\utils\GeneralUtils;
use ecstsy\MartianUtilities\utils\InventoryUtils;
use ecstsy\MartianUtilities\utils\ItemUtils;
use ecstsy\MonthlyCrates\Loader;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use muqsit\invmenu\type\InvMenuTypeIds;
use pocketmine\inventory\Inventory;
use pocketmine\item\StringToItemParser;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat as C;
use pocketmine\world\sound\ClickSound;
use pocketmine\world\sound\XpLevelUpSound;

final class Screens {

    public static function getCrateOpenMenu(Player $player, string $type): ?InvMenu {
        $menu = InvMenu::create(InvMenuTypeIds::TYPE_DOUBLE_CHEST);
        $inventory = $menu->getInventory();
        $config = GeneralUtils::getConfiguration(Loader::getInstance(), "crates.yml");
        $cfgData = $config->getAll();

        if (!isset($cfgData['crates'][$type])) {
            $player->sendMessage(C::colorize("&r&4Error: &cThe crate type '" . $type . "' does not exist!"));
            return null;
        }
        
        $crateData = $cfgData['crates'][$type];

        $menu->setName(C::colorize($crateData['title']));

        $fillerItem = Utils::parseItemFromConfig($crateData['animation']['panes']['filler']);
        $hiddenItem = Utils::parseItemFromConfig($crateData['animation']['panes']['hidden']);
        $lockedItem = Utils::parseItemFromConfig($crateData['animation']['panes']['locked']);

        InventoryUtils::fillInventory($inventory, $fillerItem);

        $hiddenSlots = [12, 13, 14, 21, 22, 23, 30, 31, 32];
        foreach ($hiddenSlots as $slot) {
            $inventory->setItem($slot, $hiddenItem);
        }

        $inventory->setItem(49, $lockedItem);

        $claimedSlots = [];

        $menu->setListener(InvMenu::readonly(function (DeterministicInvMenuTransaction $transaction) use($crateData, $inventory, &$claimedSlots): void {
            $player = $transaction->getPlayer();
            $slot = $transaction->getAction()->getSlot();
            
            if (!in_array($slot, [12, 13, 14, 21, 22, 23, 30, 31, 32])) {
                return;
            }

            $claimedSlots[$slot] = true;
            $player->getWorld()->addSound($player->getPosition()->asVector3(), new ClickSound());
            $colorSlots = Utils::getSurroundingSlots($slot);

            $rewards = Utils::buildCrateItems($crateData, $player);

            Loader::getInstance()->getScheduler()->scheduleRepeatingTask(new class($inventory, $crateData, $colorSlots, $slot, $player, $rewards, $claimedSlots) extends Task {
                private Inventory $inventory;
                private array $crateData;
                private array $colorSlots;
                private int $clickSlot;
                private Player $player;
                private int $ticks = 0;
                private int $maxTicks;
                private array $colorItems;
                private array $rewards;
                private array $claimedSlots;

                public function __construct(Inventory $inventory, array $crateData, array $colorSlots, int $clickSlot, Player $player, array $rewards, array &$claimedSlots) {
                    $this->inventory = $inventory;
                    $this->crateData = $crateData;
                    $this->colorSlots = $colorSlots;
                    $this->clickSlot = $clickSlot;
                    $this->player = $player;
                    $this->maxTicks = ($crateData["animation"]["shuffle-time"] ?? 5) * 20;
                    $this->claimedSlots = &$claimedSlots;

                    $this->colorItems = array_map(fn($mat) => StringToItemParser::getInstance()->parse($mat), $crateData["animation"]["colors"]);
                    $this->rewards = $rewards;
                }

                public function onRun(): void {
                    if ($this->ticks >= $this->maxTicks) {
                        $this->revealReward();
                        $this->getHandler()->cancel();
                        return;
                    }

                    // Randomize surrounding color slots
                    foreach ($this->colorSlots as $slot) {
                        $this->inventory->setItem($slot, $this->colorItems[array_rand($this->colorItems)]);
                    }

                    // Randomize reward in clicked slot
                    $this->inventory->setItem($this->clickSlot, $this->rewards[array_rand($this->rewards)]);

                    if ($this->ticks % 4 == 0) {
                        $this->player->broadcastSound(new ClickSound());
                    }

                    $this->ticks++;
                }

                private function revealReward(): void {
                    $this->player->broadcastSound(new XpLevelUpSound(100));

                    // Select final reward
                    $rewardItem = $this->rewards[array_rand($this->rewards)];
                    $this->inventory->setItem($this->clickSlot, $rewardItem);

                    foreach ($this->colorSlots as $slot) {
                        $this->inventory->setItem($slot, Utils::parseItemFromConfig($this->crateData["animation"]["panes"]["filler"]));
                    }

                    // Check if all rewards are unlocked
                    if (count($this->claimedSlots) === 9) {
                        $this->triggerFinalPhase();
                    }
                }

                private function triggerFinalPhase(): void {
                    $this->player->broadcastSound(new XpLevelUpSound(120));
                    
                    Loader::getInstance()->getScheduler()->scheduleRepeatingTask(new class($this->inventory, $this->crateData, $this->player) extends Task {
                        private Inventory $inventory;
                        private array $crateData;
                        private Player $player;
                        private int $ticks = 0;

                        public function __construct(Inventory $inventory, array $crateData, Player $player) {
                            $this->inventory = $inventory;
                            $this->crateData = $crateData;
                            $this->player = $player;
                        }

                        public function onRun(): void {
                            if ($this->ticks >= 5) {
                                $this->finalizeCrate();
                                $this->getHandler()->cancel();
                                return;
                            }

                            $randomColor = Utils::parseItemFromConfig($this->crateData["animation"]["colors"][array_rand($this->crateData["animation"]["colors"])]);
                            InventoryUtils::fillInventory($this->inventory, $randomColor);

                            $this->ticks++;
                        }

                        private function finalizeCrate(): void {
                            InventoryUtils::fillInventory($this->inventory, Utils::parseItemFromConfig($this->crateData["animation"]["panes"]["filler"]));
                            $this->inventory->setItem(49, Utils::parseItemFromConfig($this->crateData["animation"]["panes"]["final"]));
                        }
                    }, 10);
                }
            }, 2);
        }));

        return $menu;
    }
}