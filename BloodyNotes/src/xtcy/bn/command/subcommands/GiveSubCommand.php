<?php

namespace xtcy\bn\command\subcommands;

use Exception;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;
use xtcy\bn\Loader;
use xtcy\bn\utils\libs\CortexPE\Commando\args\IntegerArgument;
use xtcy\bn\utils\libs\CortexPE\Commando\args\RawStringArgument;
use xtcy\bn\utils\libs\CortexPE\Commando\BaseSubCommand;
use xtcy\bn\utils\libs\CortexPE\Commando\exception\ArgumentOrderException;
use xtcy\bn\utils\Notes;
use xtcy\bn\utils\Utils;

class GiveSubCommand extends BaseSubCommand
{

    /**
     * @throws ArgumentOrderException
     */
    protected function prepare(): void
    {
        $this->registerArgument(0, new RawStringArgument("identifier", false));
        $this->registerArgument(1, new IntegerArgument("amount", true));
        $this->registerArgument(2, new RawStringArgument("player", true));
        $this->setPermission("bloodynotes.command.give");
        $this->setUsage(C::colorize("&r&4Usage: &r&c/bloodynotes give <identifier> [amount] [player]"));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $config = Utils::getConfiguration(Loader::getInstance(), "messages.yml");
        if (!$sender instanceof Player) {
            $sender->sendMessage(C::colorize($config->get("no-perm")));
            return;
        }

        $noteType = $args['identifier'] ?? null;
        $amount = isset($args["amount"]) ? max(1, (int)$args['amount']) : 1;
        $player = $args["player"] ?? $sender->getName();

        if ($noteType === null) {
            $this->sendUsage();
            return;
        }

        $targetPlayer = Utils::getPlayerByPrefix($player);
        if ($targetPlayer !== null) {
            $noteData = $config->get("notes.$noteType");
            if ($noteData === null) {
                $sender->sendMessage("Note identifier '$noteType' doesn't exist. Double-check your configuration.");
                return;
            }

            try {
                for ($i = 0; $i < $amount; $i++) {
                    $note = Notes::getNoteType($noteType, 1);
                    $targetPlayer->getInventory()->addItem($note);
                }

                $givenMessage = $config->get("note-given");
                $sender->sendMessage(C::colorize(str_replace(['{player}', '{amount}', '{type}'], [$targetPlayer->getName(), $amount, $noteType], $givenMessage)));
            } catch (Exception $e) {
                $sender->sendMessage("Error while giving note to the player: " . $e->getMessage());
            }
        } else {
            $sender->sendMessage(C::colorize($config->get("invalid-player")));
        }
    }
}