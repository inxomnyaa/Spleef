<?php

namespace xenialdan\Spleef;

use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use xenialdan\gameapi\API;

/**
 * Class LeaveGameListener
 * @package xenialdan\Spleef
 * Listens for interacts for leaving games or teams
 */
class LeaveGameListener implements Listener{

	public function onDeath(EntityDeathEvent $ev){
		if ($ev->getEntity() instanceof Player){
			if (API::isArena(Loader::getInstance(), $ev->getEntity()->getLevel()) && API::isPlaying(Loader::getInstance(), $ev->getEntity()))
				API::getArenaByLevel(Loader::getInstance(), $ev->getEntity()->getLevel())->removePlayer($ev->getEntity());
		}
	}

	public function onDisconnectOrKick(PlayerQuitEvent $ev){
		if (API::isArena(Loader::getInstance(), $ev->getPlayer()->getLevel()) && API::isPlaying(Loader::getInstance(), $ev->getPlayer()))
			API::getArenaByLevel(Loader::getInstance(), $ev->getPlayer()->getLevel())->removePlayer($ev->getPlayer());
	}

	public function onLevelChange(EntityLevelChangeEvent $ev){
		if ($ev->getEntity() instanceof Player){
			if (API::isArena(Loader::getInstance(), $ev->getOrigin()) && API::isPlaying(Loader::getInstance(), $ev->getEntity()))
				API::getArenaByLevel(Loader::getInstance(), $ev->getOrigin())->removePlayer($ev->getEntity());
		}
	}
}