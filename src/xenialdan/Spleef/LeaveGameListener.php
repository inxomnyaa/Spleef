<?php

namespace xenialdan\Spleef;

use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use xenialdan\gameapi\API;

/**
 * Class LeaveGameListener
 * @package xenialdan\Spleef
 * Listens for interacts for leaving games or teams
 */
class LeaveGameListener implements Listener
{

    public function onDeath(PlayerDeathEvent $ev)
    {
        if (API::isArenaOf(Loader::getInstance(), ($player = $ev->getPlayer())->getLevel()) && API::isPlaying($player, Loader::getInstance())) {
            /** @noinspection PhpUnhandledExceptionInspection */
            API::getArenaByLevel(Loader::getInstance(), $player->getLevel())->removePlayer($player);
        }
    }

    public function onDisconnectOrKick(PlayerQuitEvent $ev)
    {
        if (API::isArenaOf(Loader::getInstance(), $ev->getPlayer()->getLevel()))
            /** @noinspection PhpUnhandledExceptionInspection */
            API::getArenaByLevel(Loader::getInstance(), $ev->getPlayer()->getLevel())->removePlayer($ev->getPlayer());
    }

    public function onLevelChange(EntityLevelChangeEvent $ev)
    {
        if ($ev->getEntity() instanceof Player) {
            if (API::isArenaOf(Loader::getInstance(), $ev->getOrigin()) && API::isPlaying($ev->getEntity(), Loader::getInstance()))//TODO test if still calls it twice
                /** @noinspection PhpUnhandledExceptionInspection */
                API::getArenaByLevel(Loader::getInstance(), $ev->getOrigin())->removePlayer($ev->getEntity());
        }
    }
}