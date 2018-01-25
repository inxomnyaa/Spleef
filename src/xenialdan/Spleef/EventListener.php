<?php

namespace xenialdan\Spleef;

use pocketmine\block\Snow;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\item\IronShovel;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\Player;
use xenialdan\gameapi\API;
use xenialdan\gameapi\Arena;

/**
 * Class EventListener
 * @package xenialdan\Spleef
 * Listens for all normal events
 */
class EventListener implements Listener{

	public function onDamage(EntityDamageEvent $event){
		if (!$event->getEntity() instanceof Player) return;
		if ($event instanceof EntityDamageEvent){
			if (API::isArena(Loader::getInstance(), ($entity = $event->getEntity())->getLevel()) && API::isPlaying(Loader::getInstance(), $entity)){
				$arena = API::getArenaByLevel(Loader::getInstance(), $entity->getLevel());
				if ($arena->getState() !== Arena::INGAME) return;
				switch ($event->getCause()){
					case EntityDamageEvent::CAUSE_VOID: {
						$event->setCancelled();
						Loader::getInstance()->removePlayer(API::getArenaByLevel(Loader::getInstance(), $entity->getLevel()), $entity);
						break;
					}
					case EntityDamageEvent::CAUSE_PROJECTILE: {
						break;
					}
					default:
						$event->setCancelled();
				}
			}
		}
	}

	public function onBlockBreakEvent(BlockBreakEvent $event){
		if (API::isArena(Loader::getInstance(), ($entity = $event->getPlayer())->getLevel()) && API::isPlaying(Loader::getInstance(), $entity)){
			if (!$event->getBlock() instanceof Snow) $event->setCancelled();
			else{
				/** @var IronShovel $item */
				$item =
					$event->getPlayer()->getInventory()->getItemInHand();
				$item->setDamage(0);
				$event->setDrops([ItemFactory::get(ItemIds::SNOWBALL, 0, 4)]);
			}
		}
	}
}