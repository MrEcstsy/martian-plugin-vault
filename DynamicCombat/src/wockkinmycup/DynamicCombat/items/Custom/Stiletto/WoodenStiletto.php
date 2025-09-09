<?php

namespace wockkinmycup\DynamicCombat\items\Custom\Stiletto;

use customiesdevs\customies\item\component\DurabilityComponent;
use customiesdevs\customies\item\component\HandEquippedComponent;
use customiesdevs\customies\item\component\MaxStackSizeComponent;
use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\block\BlockToolType;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\Sword;
use pocketmine\item\ToolTier;

class WoodenStiletto extends Sword implements ItemComponents {
    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier)
    {
        $name = 'Wooden Stiletto';

        $info = ToolTier::WOOD();

        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::GROUP_SWORD,
        );

        parent::__construct($identifier, $name, $info);

        $this->initComponent('wooden_stiletto',$inventory);

        $this->addComponent(new DurabilityComponent($this->getMaxDurability()));
        $this->addComponent(new MaxStackSizeComponent(1));
        $this->addComponent(new HandEquippedComponent(true));
    }

    public function getAttackPoints(): int
    {
        return 3;
    }

    public function getBlockToolType(): int
    {
        return BlockToolType::SWORD;
    }

    public function getMiningEfficiency(bool $isCorrectTool): float
    {
        return 1.0;
    }

    public function getMaxDurability(): int
    {
        return 30;
    }
}