<?php

namespace xenialdan\Spleef;

use pocketmine\block\SignPost;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\tile\Sign;
use pocketmine\utils\TextFormat;

/**
 * Class JoinListener
 * @package xenialdan\Spleef
 * Listens for interacts for joining games or teams
 */
class JoinGameListener implements Listener{

	public function onInteract(PlayerInteractEvent $event){
		$action = $event->getAction();
		$block = $event->getBlock();
		if ($action === PlayerInteractEvent::RIGHT_CLICK_BLOCK && $block instanceof SignPost){
			/** @var $tile Sign */
			if (($tile = $block->getLevel()->getTile($block)) instanceof Sign){
				$this->onClickSign($event, $tile->getText());
			}
		}
	}

	public function onClickSign($event, array $text){
		/** @var PlayerInteractEvent $event */
		if (strpos(strtolower(TextFormat::clean($text[0])), strtolower(TextFormat::clean(Loader::getInstance()->getPrefix()))) !== false){
			if (is_null($arena = Loader::getInstance()->getArenas()[TextFormat::clean($text[1])])){
				$event->getPlayer()->sendMessage(TextFormat::RED . 'Arena not found');
				return;
			}
			if ($arena->joinTeam($event->getPlayer())){
			} else{
				$event->getPlayer()->sendMessage(TextFormat::RED . 'Error joining');
			}
		}
	}

}