<?php

namespace wockkinmycup\DynamicCombat;

use customiesdevs\customies\item\CustomiesItemFactory;
use wockkinmycup\DynamicCombat\commands\ClassCommand;
use wockkinmycup\DynamicCombat\items\Custom\Stiletto\DiamondStiletto;
use wockkinmycup\DynamicCombat\items\Custom\Stiletto\GoldenStiletto;
use wockkinmycup\DynamicCombat\items\Custom\Stiletto\IronStiletto;
use wockkinmycup\DynamicCombat\items\Custom\Stiletto\NetheriteStiletto;
use wockkinmycup\DynamicCombat\items\Custom\Stiletto\StoneStiletto;
use wockkinmycup\DynamicCombat\items\Custom\Swords\EmeraldSword;
use wockkinmycup\DynamicCombat\utils\RecipeManager;
use wockkinmycup\libs\Packs;
use pocketmine\plugin\PluginBase;
use wockkinmycup\DynamicCombat\items\Custom\Stiletto\WoodenStiletto;
use wockkinmycup\DynamicCombat\utils\CustomIds;

class Loader extends PluginBase {

    public static Loader $instance;

    public function onLoad(): void
    {
        self::$instance = $this;
    }

    public function onEnable(): void
    {
        $this->saveDefaultConfig();
        $this->saveResource("items.yml");
        $this->getServer()->getPluginManager()->registerEvents(new CombatListener(), $this);
        $this->registerArmory();
        (new RecipeManager())->init();
        $this->getServer()->getCommandMap()->registerAll("dynamiccombat", [
            new ClassCommand(),
        ]);
    }

    public static function getInstance(): Loader {
        return self::$instance;
    }

    public function registerArmory() {
        $customies = CustomiesItemFactory::getInstance();

        # Stiletto's
        $customies->registerItem(WoodenStiletto::class, CustomIds::WOODEN_STILETTO, "Wooden Stiletto");
        $customies->registerItem(StoneStiletto::class, CustomIds::STONE_STILETTO, "Stone Stiletto");
        $customies->registerItem(IronStiletto::class, CustomIds::IRON_STILETTO, "Iron Stiletto");
        $customies->registerItem(GoldenStiletto::class, CustomIds::GOLDEN_STILETTO, "Golden Stiletto");
        $customies->registerItem(DiamondStiletto::class, CustomIds::DIAMOND_STILETTO, "Diamond Stiletto");
        $customies->registerItem(NetheriteStiletto::class, CustomIds::NETHERITE_STILETTO, "Netherite Stiletto");

        # Sword's
        $customies->registerItem(EmeraldSword::class, CustomIds::EMERALD_SWORD, "Emerald Sword");
    }
}