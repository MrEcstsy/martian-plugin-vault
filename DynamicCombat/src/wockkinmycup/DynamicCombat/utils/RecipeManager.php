<?php

namespace wockkinmycup\DynamicCombat\utils;

use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\block\VanillaBlocks;
use pocketmine\crafting\ExactRecipeIngredient;
use pocketmine\crafting\ShapedRecipe;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\Server;

final class RecipeManager
{
    public function init(): void {
        $emerald = VanillaItems::EMERALD();
        $stick = VanillaItems::STICK();
        $air = VanillaItems::AIR();
        $itemFactory = CustomiesItemFactory::getInstance();

        $this->registerAllEmeraldTools(
            $emerald,
            $stick,
            $itemFactory->get(CustomIds::EMERALD_SWORD),
        );
    }


    public function registerAllArmors(Item $itemIngot, Item $helmet, Item $chestplate, Item $leggings, Item $boots): void {
        $air = VanillaBlocks::AIR()->asItem();


        $this->registerCraft([
            [$itemIngot, $itemIngot, $itemIngot],
            [$itemIngot,  $air, $itemIngot],
            [$air,  $air, $air],
            [$helmet]
        ]);


        $this->registerCraft([
            [$air, $air, $air],
            [$itemIngot,  $itemIngot, $itemIngot],
            [$itemIngot,  $air, $itemIngot],
            [$helmet]
        ]);



        $this->registerCraft([
            [$itemIngot, $air, $itemIngot],
            [$itemIngot,  $itemIngot, $itemIngot],
            [$itemIngot,  $itemIngot, $itemIngot],
            [$chestplate]
        ]);


        $this->registerCraft([
            [$itemIngot, $itemIngot, $itemIngot],
            [$itemIngot,  $air, $itemIngot],
            [$itemIngot,  $air, $itemIngot],
            [$leggings]
        ]);


        $this->registerCraft([
            [$air, $air, $air],
            [$itemIngot,  $air, $itemIngot],
            [$itemIngot,  $air, $itemIngot],
            [$boots]
        ]);


        $this->registerCraft([
            [$itemIngot, $air, $itemIngot],
            [$itemIngot,  $air, $itemIngot],
            [$air,  $air, $air],
            [$boots]
        ]);
    }


    public function registerAllEmeraldTools(Item $item, Item $stick, Item $sword): void {
        $air = VanillaBlocks::AIR()->asItem();

        //SWORD
        $this->registerCraft(
            [
                [$air, $item, $air],
                [$air, $item,  $air],
                [$air, $stick, $air],
                [$sword]
            ]);

    }



    public function registerCraft(array $craft): void
    {
        $shape = ["", "", ""];
        $y = "a";

        $ingredients = [];


        foreach ($craft[0] as $item) {
            if ($item instanceof Item) {
                if ($item->isNull()|| $item->getName() === 'Air') {
                    $shape[0] .= " ";
                } else {
                    $shape[0] .= $y;
                    $count = $item->getCount();
                    if ($count > 1) {
                        $item->setCount(1);
                        $recipe = new ExactRecipeIngredient($item);
                        $recipe->getItem()->setCount($count);
                    } else $recipe = new ExactRecipeIngredient($item);
                    $ingredients[strval($y)] = $recipe;
                    $y++;
                }
            }
        }

        foreach ($craft[1] as $item) {
            if ($item instanceof Item) {
                if ($item->isNull()|| $item->getName() === 'Air') {
                    $shape[1] .= " ";
                } else {
                    $shape[1] .= $y;
                    $count = $item->getCount();
                    if ($count > 1) {
                        $item->setCount(1);
                        $recipe = new ExactRecipeIngredient($item);
                        $recipe->getItem()->setCount($count);
                    } else $recipe = new ExactRecipeIngredient($item);
                    $ingredients[strval($y)] = $recipe;
                    $y++;
                }
            }
        }

        foreach ($craft[2] as $item) {
            if ($item instanceof Item) {
                if ($item->isNull() || $item->getName() === 'Air') {
                    $shape[2] .= " ";
                } else {
                    $shape[2] .= $y;
                    $count = $item->getCount();
                    if ($count > 1) {
                        $item->setCount(1);
                        $recipe = new ExactRecipeIngredient($item);
                        $recipe->getItem()->setCount($count);
                    } else $recipe = new ExactRecipeIngredient($item);
                    $ingredients[strval($y)] = $recipe;
                    $y++;
                }
            }
        }


        $shape = new ShapedRecipe(
            $shape,
            $ingredients,
            [$craft[3][0]]
        );


        Server::getInstance()->getCraftingManager()->registerShapedRecipe($shape);
    }
}