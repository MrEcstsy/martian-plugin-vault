<?php

namespace wockkinmycup\LuckyPouches\tasks;

use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;
use cooldogedev\BedrockEconomy\api\legacy\ClosureContext;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as C;
use wockkinmycup\LuckyPouches\Loader;
use wockkinmycup\LuckyPouches\utils\libs\skymin\bossbar\BossBarAPI;

class BossBarRevealTask extends Task {

    private Player $player;
    private int $money;
    private int $revealedPosition = 0;
    protected string $pouchTag;

    public function __construct(Player $player, int $money, string $pouchTag) {
        $this->player = $player;
        $this->money = $money;
        $this->pouchTag = $pouchTag;
    }

    public function onRun(): void {
        if ($this->revealedPosition >= mb_strlen((string) $this->money)) {
            $this->getHandler()->cancel();
            BossBarAPI::getInstance()->hideBossBar($this->player);
            $config = Loader::getInstance()->getConfig();
            $configArray = Loader::getInstance()->getConfig()->getAll();
            $pouchData = $configArray["pouches"][$this->pouchTag];
            $currency = $pouchData["currency"];
            $messages = new Config(Loader::getInstance()->getDataFolder() . "messages.yml", Config::YAML);

            $winnings = $this->money;
            $player = $this->player;
            if ($currency === "BEDROCKECONOMY") {
                BedrockEconomyAPI::legacy()->addToPlayerBalance(
                    $this->player->getName(),
                    $this->money,
                    ClosureContext::create(
                        function (bool $wasUpdated) use($config, $player, $winnings, $messages, $currency) {
                            BossBarAPI::getInstance()->hideBossBar($player);
                            if ($config->getNested("animations.bossbar.show_prize_message") === true) {
                                if ($currency === "BEDROCKECONOMY") {
                                    $msg = $messages->getNested("prize_message");
                                    $msg = str_replace(["{prefix}", "{prize}"], [$messages->get("prefix"), number_format($winnings)], $msg);
                                    $player->sendMessage(C::colorize($msg));
                                }
                            }
                        }
                    )
                );
            }

            if ($currency === "odyssey") {
                $session = \xtcy\odysseyrealm\Loader::getSessionManager()->getSession($player);
                $session->addBalance($this->money);
                if ($config->getNested("animations.bossbar.show_prize_xp_message") === true) {
                    $msg = $messages->getNested("prize_xp_message");
                    $msg = str_replace(["{prefix}", "{prize}"], [$messages->get("prefix"), number_format($winnings)], $msg);
                    $player->sendMessage(C::colorize($msg));
                }
            }

            if ($currency === "XP") {
                $this->player->getXpManager()->addXp($this->money);
                BossBarAPI::getInstance()->hideBossBar($player);
                if ($config->getNested("animations.bossbar.show_prize_xp_message") === true) {
                    $msg = $messages->getNested("prize_xp_message");
                    $msg = str_replace(["{prefix}", "{prize}"], [$messages->get("prefix"), number_format($winnings)], $msg);
                    $player->sendMessage(C::colorize($msg));
                }
            }

            return;
        }

        $this->revealedPosition++;
        $moneyString = (string) $this->money;
        $revealedMoney = mb_substr($moneyString, 0, $this->revealedPosition);
        $obfuscatedMoney = $this->obfuscateAmount(mb_substr($moneyString, $this->revealedPosition));

        $revealColor = Loader::getInstance()->getConfig()->getNested("animations.bossbar.reveal_color");
        $obfuscateColor = Loader::getInstance()->getConfig()->getNested("animations.bossbar.obfuscate_color");

        $configArray = Loader::getInstance()->getConfig()->getAll();
        $pouchData = $configArray["pouches"][$this->pouchTag];
        $currency = $pouchData["currency"];

        if ($currency === "BEDROCKECONOMY") {
            $config = Loader::getInstance()->getConfig();
            $bossBar = $config->getNested("animations.bossbar.text");
            $revealedBossBar = C::colorize($revealColor) . "$" . C::colorize($revealColor) . $revealedMoney . C::colorize($obfuscateColor) . $obfuscatedMoney;
            $bossBar = str_replace("{reward}", $revealedBossBar, $bossBar);
            BossBarAPI::getInstance()->sendBossBar($this->player, C::colorize($bossBar));
        }

        if ($currency === "odyssey") {
            $config = Loader::getInstance()->getConfig();
            $bossBar = $config->getNested("animations.bossbar.text");
            $revealedBossBar = C::colorize($revealColor) . "$" . C::colorize($revealColor) . $revealedMoney . C::colorize($obfuscateColor) . $obfuscatedMoney;
            $bossBar = str_replace("{reward}", $revealedBossBar, $bossBar);
            BossBarAPI::getInstance()->sendBossBar($this->player, C::colorize($bossBar));
        }

        if ($currency === "XP") {
            $config = Loader::getInstance()->getConfig();
            $bossBar = $config->getNested("animations.bossbar.text");
            $revealedBossBar = C::colorize($revealColor) . C::colorize($revealColor) . $revealedMoney . C::colorize($obfuscateColor) . $obfuscatedMoney . "&r" . $revealColor. " XP";
            $bossBar = str_replace("{reward}", $revealedBossBar, $bossBar);
            BossBarAPI::getInstance()->sendBossBar($this->player, C::colorize($bossBar));
        }
    }

    private function obfuscateAmount($amount): string {
        $obfuscated = "";
        $obfuscateColor = Loader::getInstance()->getConfig()->getNested("animations.bossbar.obfuscate_color");
        foreach (mb_str_split($amount) as $char) {
            $obfuscated .= C::colorize($obfuscateColor) . "§k$char";
        }
        return $obfuscated;
    }

}