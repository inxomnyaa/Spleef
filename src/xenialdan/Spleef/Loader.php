<?php

namespace xenialdan\Spleef;

use pocketmine\block\BlockIds;
use pocketmine\entity\Attribute;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\IronShovel;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\item\Shovel;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\AdventureSettingsPacket;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use xenialdan\BossBarAPI\API as BossBarAPI;
use xenialdan\gameapi\API;
use xenialdan\gameapi\Arena;
use xenialdan\gameapi\event\WinEvent;
use xenialdan\gameapi\Game;
use xenialdan\gameapi\Team;

class Loader extends PluginBase implements Game{
	/** @var Loader */
	private static $instance = null;
	/** @var Arena[] */
	private static $arenas = [];
	/** @var API */
	public $API;
	/** @var BossBarAPI */
	public $BossBarAPI;

	/**
	 * Returns an instance of the plugin
	 * @return Loader
	 */
	public static function getInstance(){
		return self::$instance;
	}

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
		$this->getServer()->getPluginManager()->registerEvents(new JoinGameListener(), $this);
		$this->getServer()->getPluginManager()->registerEvents(new LeaveGameListener(), $this);
		API::registerGame($this);
		$levelname = "spleef";
		$arena = new Arena($levelname, $this);
		$team = new Team(TextFormat::BLUE, "PLAYER");
		$team->setMinPlayers(2);
		$team->setMaxPlayers(16);
		$arena->addTeam($team);
		$this->addArena($arena);
	}

	public function removePlayer(Arena $arena, Player $player){
		$player->sendSettings();
		if (count($arena->getPlayers()) > 1){
			$arena->removePlayer($player);
		}
		if (count($arena->getPlayers()) === 1){
			Server::getInstance()->broadcastMessage("If game does not stop, try /lobby", $arena->getPlayers());
			print __CLASS__ . '-' . __LINE__ . ':';//TODO REMOVE
			$winner = null;
			foreach ($arena->getPlayers() as $players){
				print __CLASS__ . '-' . __LINE__ . ':';//TODO REMOVE
				$arena->removePlayer($players);
				$winner = $players;
			}
			print __CLASS__ . '-' . __LINE__ . ':';//TODO REMOVE
			if (!is_null($winner)){
				print __CLASS__ . '-' . __LINE__ . ':';//TODO REMOVE
				Server::getInstance()->getPluginManager()->callEvent($ev = new WinEvent($this, $arena, $winner));
				print __CLASS__ . '-' . __LINE__ . ':';//TODO REMOVE
				$ev->announce();
			}

			print __CLASS__ . '-' . __LINE__ . ':';//TODO REMOVE
			API::stop($this);
			print __CLASS__ . '-' . __LINE__ . ':';//TODO REMOVE
			API::resetArena($arena);
			print __CLASS__ . '-' . __LINE__ . ':';//TODO REMOVE
		}
	}

	/**
	 * Adds an arena
	 * @param Arena $arena
	 */
	public function addArena(Arena $arena){
		self::$arenas[$arena->getLevelName()] = $arena;
	}

	/**
	 * Removes an arena
	 * @param Arena $arena
	 */
	public function removeArena(Arena $arena){
		unset(self::$arenas[$arena->getLevelName()]);
	}

	public function onLoad(){
		self::$instance = $this;
	}

	/**
	 * returns all arenas
	 * @return Arena[]
	 */
	public function getArenas(){
		return self::$arenas;
	}

	/**
	 * @return Player[]
	 */
	public function getPlayers(){
		$players = [];
		foreach ($this->getArenas() as $arena){
			$players = array_merge($players, $arena->getPlayers());
		}
		return $players;
	}

	/**
	 * The prefix of the game
	 * Used for messages and signs
	 * @return string;
	 */
	public function getPrefix(): string{
		return $this->getDescription()->getPrefix();
	}

	/**
	 * The names of the authors
	 * @return string;
	 */
	public function getAuthors(){
		return implode(", ", $this->getDescription()->getAuthors());
	}

	/**
	 * @param Arena $arena
	 */
	public function startArena(Arena $arena){
		#$pk = new AdventureSettingsPacket();
		#$pk->setFlag(AdventureSettingsPacket::WORLD_IMMUTABLE, false);
		#$pk->setFlag(AdventureSettingsPacket::NO_PVP, true);
		#$pk->setFlag(AdventureSettingsPacket::BUILD_AND_MINE, true);

		/** @var Shovel $shovel */
		$shovel = ItemFactory::get(ItemIds::IRON_SHOVEL);
		$enchantment = Enchantment::getEnchantment(Enchantment::UNBREAKING);
		if(!is_null($enchantment))
			$shovel->addEnchantment(new EnchantmentInstance($enchantment, 20));
		$shovel->setUnbreakable();
		$shovel->setLore(["Supershovel 3000", "Break the snow with the shovel!"]);
		$shovel->setNamedTagEntry((new ListTag("CanDestroy", [new StringTag((string)BlockIds::SNOW_BLOCK)])));

		foreach ($arena->getPlayers() as $player){
			$player->setGamemode(Player::SURVIVAL);
			$player->getInventory()->addItem($shovel);

			$player->setHealth($player->getMaxHealth());
			$player->setFood($player->getMaxFood());
			$player->setSaturation($player->getAttributeMap()->getAttribute(Attribute::SATURATION)->getMaxValue());
			#$pk2 = clone $pk;
			#$pk2->commandPermission = ($player->isOp() ? AdventureSettingsPacket::PERMISSION_OPERATOR : AdventureSettingsPacket::PERMISSION_NORMAL);
			#$pk2->playerPermission = ($player->isOp() ? PlayerPermissions::OPERATOR : PlayerPermissions::MEMBER);
			#$pk2->entityUniqueId = $player->getId();
			#$player->dataPacket($pk2);
		}

		BossBarAPI::setTitle('Good luck! ' . count($this->getPlayers()) . ' players alive', $arena->bossbarids['state'], $this->getPlayers());
		BossBarAPI::setPercentage(100, $arena->bossbarids['state'], $this->getPlayers());
	}

	/**
	 * TODO use this
	 * @param Arena $arena
	 */
	public function stopArena(Arena $arena){
		// TODO: Implement stopArena() method.
	}
}