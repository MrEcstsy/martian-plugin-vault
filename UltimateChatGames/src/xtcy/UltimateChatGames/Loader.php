<?php

namespace UltimateChatGames\src\xtcy\UltimateChatGames;

use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;
use cooldogedev\BedrockEconomy\database\exception\RecordNotFoundException;
use cooldogedev\BedrockEconomy\libs\cooldogedev\libSQL\exception\SQLException;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskHandler;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat;

class Loader extends PluginBase implements Listener
{
    private Config $config;
    private Config $msgConfig;
    private array $words;
    private bool $gameRunning = false;
    private string $currentWord = "";
    public string $scrambledWord = "";
    private bool $wordGuessed = false;
    public bool $gameEnded = false;
    private TaskHandler $gameDurationHandler;

    use SingletonTrait;

    public function onLoad(): void
    {
        self::setInstance($this);
        $this->saveResource("config.yml");
        $this->saveResource("messages.yml");
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $this->msgConfig = new Config($this->getDataFolder() . "messages.yml", Config::YAML);
        $this->words = $this->config->get("words", []);
    }

    public function onEnable(): void
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->startGameTimer();
    }

    public function startGameTimer(): void
    {
        $interval = $this->config->get("settings")["interval"] ?? 120;
        $this->getScheduler()->scheduleDelayedRepeatingTask(new ClosureTask(function (): void {
            $this->startGame();
        }), $interval * 20, $interval * 20);
    }

    public function startGame(): void
    {
        if ($this->gameRunning) {
            return;
        }
        $this->gameRunning = true;
        $this->wordGuessed = false;

        $this->currentWord = $this->getRandomWord();
        $this->scrambledWord = str_shuffle($this->currentWord);

        $messages = $this->msgConfig->getNested("messages.start", []);
        foreach ($messages as $message) {
            $formattedMessage = TextFormat::colorize($message);
            $this->broadcastMessage(str_replace("{word}", $this->scrambledWord, $formattedMessage));
        }

        $duration = $this->config->get("settings")["duration"] ?? 60;
        $this->gameDurationHandler = $this->getScheduler()->scheduleDelayedTask(new ClosureTask(function (): void {
            if (!$this->wordGuessed) {
                $messages = $this->msgConfig->getNested("messages.no-winner", []);
                $this->endGame($messages);
            }
        }), $duration * 20);
    }

    public function endGame(array $messages): void
    {
        foreach ($messages as $message) {
            $this->broadcastMessage(TextFormat::colorize($message));
        }
        $this->gameRunning = false;
        $this->gameDurationHandler->cancel();
    }

    private function broadcastMessage(string $message): void
    {
        $players = $this->getServer()->getOnlinePlayers();
        foreach ($players as $player) {
            $player->sendMessage($message);
        }
    }

    public function getRandomWord(): string
    {
        return $this->words[array_rand($this->words)];
    }

    public function onPlayerChat(PlayerChatEvent $event): void
    {
        if (!$this->gameRunning) {
            return;
        }

        $player = $event->getPlayer();
        $message = strtolower($event->getMessage());
        $word = strtolower($this->currentWord);

        if ($message === $word) {
            $event->cancel();
            $min = $this->config->get("settings")["min"] ?? 1000;
            $max = $this->config->get("settings")["max"] ?? 100000;
            $reward = mt_rand($min, $max);
            $config = $this->msgConfig;
            //$session = \xtcy\odysseyrealm\Loader::getSessionManager()->getSession($player);
            BedrockEconomyAPI::CLOSURE()->add(
                $player->getXuid(),
                $player->getName(),
                $reward,
                00,
                static function () use($player, $reward, $config): void {
                    $winnerMessage = $config->getNested("messages.winner", []);
                    Loader::getInstance()->endGame(str_replace(["{player}", "{reward}"], [$player->getName(), number_format($reward)], $winnerMessage));
                    },
                static function (SQLException $exception): void {
                    if ($exception instanceof RecordNotFoundException) {
                        Loader::getInstance()->getLogger()->warning('Account not found');
                        return;
                    }

                    Loader::getInstance()->getLogger()->warning('An error occurred while updating the balance.');
                }
            );
        }
    }
}

