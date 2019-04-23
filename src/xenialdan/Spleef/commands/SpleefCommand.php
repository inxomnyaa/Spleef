<?php

declare(strict_types=1);

namespace xenialdan\Spleef\commands;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
use xenialdan\gameapi\API;
use xenialdan\gameapi\Game;
use xenialdan\Spleef\Loader;

class SpleefCommand extends PluginCommand
{
    public function __construct(Plugin $plugin)
    {
        parent::__construct("spleef", $plugin);
        $this->setPermission("spleef.command");
        $this->setDescription("Spleef commands for setup or leaving a game");
        $this->setUsage("/spleef | /spleef setup | /spleef endsetup | /spleef leave | /spleef forcestart | /spleef stop | /spleef status | /spleef info");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        /** @var Player $sender */
        $return = $sender->hasPermission($this->getPermission());
        if (!$return) {
            $sender->sendMessage(TextFormat::RED . "You do not have permissions to run this command");
            return true;
        }
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "This command is for players only");
            return false;
        }
        try {
            $return = true;
            switch ($args[0] ?? "setup") {
                case "setup":
                    {
                        if (!$sender->hasPermission("spleef.command.setup")) {
                            $sender->sendMessage(TextFormat::RED . "You do not have permissions to run this command");
                            return true;
                        }
                        /** @var Game $p */
                        $p = $this->getPlugin();
                        $p->setupArena($sender);
                        break;
                    }
                case "leave":
                    {
                        if (!$sender->hasPermission("spleef.command.leave")) {
                            $sender->sendMessage(TextFormat::RED . "You do not have permissions to run this command");
                            return true;
                        }
                        $arena = API::getArenaOfPlayer($sender);
                        if (is_null($arena)) {
                            /** @var Game $plugin */
                            $plugin = $this->getPlugin();
                            $sender->sendMessage(TextFormat::RED . "It appears that you are not playing " . $plugin->getPrefix());
                            return true;
                        }
                        if (API::isPlaying($sender, $this->getPlugin())) $arena->removePlayer($sender);
                        break;
                    }
                case "endsetup":
                    {
                        if (!$sender->hasPermission("spleef.command.endsetup")) {
                            $sender->sendMessage(TextFormat::RED . "You do not have permissions to run this command");
                            return true;
                        }
                        /** @var Game $p */
                        $p = $this->getPlugin();
                        $p->endSetupArena($sender);
                        break;
                    }
                case "stop":
                    {
                        if (!$sender->hasPermission("spleef.command.stop")) {
                            $sender->sendMessage(TextFormat::RED . "You do not have permissions to run this command");
                            return true;
                        }
                        API::getArenaByLevel(Loader::getInstance(), $sender->getLevel())->stopArena();
                        break;
                    }
                case "forcestart":
                    {
                        if (!$sender->hasPermission("spleef.command.forcestart")) {
                            $sender->sendMessage(TextFormat::RED . "You do not have permissions to run this command");
                            return true;
                        }
                        $arena = API::getArenaOfPlayer($sender);
                        if (is_null($arena)) {
                            /** @var Game $plugin */
                            $plugin = $this->getPlugin();
                            $sender->sendMessage(TextFormat::RED . "It appears that you are not playing " . $plugin->getPrefix());
                            return true;
                        }
                        $arena->startTimer($arena->getOwningGame());
                        $arena->forcedStart = true;
                        $arena->setTimer(5);
                        $sender->getServer()->broadcastMessage("Arena will start immediately due to a forced start by " . $sender->getDisplayName(), $arena->getPlayers());
                        break;
                    }
                default:
                    {
                        $return = false;
                        throw new \InvalidArgumentException("Unknown argument supplied: " . $args[0]);
                    }
            }
        } catch (\Throwable $error) {
            $this->getPlugin()->getLogger()->logException($error);
            $return = false;
        } finally {
            return $return;
        }
    }
}
