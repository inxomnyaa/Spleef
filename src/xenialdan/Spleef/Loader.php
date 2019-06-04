<?php

namespace xenialdan\Spleef;

use pocketmine\block\BlockIds;
use pocketmine\entity\Entity;
use pocketmine\entity\object\ItemEntity;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\item\Shovel;
use pocketmine\level\Level;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use xenialdan\customui\elements\Button;
use xenialdan\customui\elements\Input;
use xenialdan\customui\elements\Label;
use xenialdan\customui\elements\StepSlider;
use xenialdan\customui\windows\CustomForm;
use xenialdan\customui\windows\ModalForm;
use xenialdan\customui\windows\SimpleForm;
use xenialdan\gameapi\API;
use xenialdan\gameapi\Arena;
use xenialdan\gameapi\Game;
use xenialdan\gameapi\Team;
use xenialdan\Spleef\commands\SpleefCommand;

class Loader extends Game
{
    /** @var Loader */
    private static $instance = null;

    /**
     * Returns an instance of the plugin
     * @return Loader
     */
    public static function getInstance()
    {
        return self::$instance;
    }

    public function onLoad()
    {
        self::$instance = $this;
    }

    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents(new JoinGameListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new LeaveGameListener(), $this);
        $this->getServer()->getCommandMap()->register("spleef", new SpleefCommand($this));
        /** @noinspection PhpUnhandledExceptionInspection */
        API::registerGame($this);
        foreach (glob($this->getDataFolder() . "*.json") as $v) {
            $this->addArena($this->getNewArena($v));
        }
    }

    /**
     * Create and return a new arena, used for addArena in onLoad, setupArena and resetArena (in @see ArenaAsyncCopyTask)
     * @param string $settingsPath The path to the .json file used for the settings. Basename should be levelname
     * @return Arena
     */
    public function getNewArena(string $settingsPath): Arena
    {
        $settings = new SpleefSettings($settingsPath);
        $levelname = basename($settingsPath, ".json");
        $arena = new Arena($levelname, $this, $settings);
        $team = new Team(TextFormat::RESET, "Players");
        $team->setMinPlayers(2);
        $team->setMaxPlayers((int)$settings->maxPlayers);
        $arena->addTeam($team);
        return $arena;
    }

    /**
     * @param Arena $arena
     */
    public function startArena(Arena $arena): void
    {
        /** @var Shovel $shovel */
        $shovel = ItemFactory::get(ItemIds::IRON_SHOVEL);
        $enchantment = Enchantment::getEnchantment(Enchantment::UNBREAKING);
        if (!is_null($enchantment))
            $shovel->addEnchantment(new EnchantmentInstance($enchantment, 20));
        $shovel->setUnbreakable();
        $shovel->setNamedTagEntry((new ListTag("CanDestroy", [new StringTag((string)BlockIds::SNOW_BLOCK)])));

        foreach ($arena->getPlayers() as $player) {
            $player->getInventory()->addItem($shovel);
        }

        $arena->bossbar->setSubTitle()->setTitle('Good luck! ' . count($arena->getPlayers()) . ' players alive')->setPercentage(1);
    }

    /**
     * Called AFTER API::stopArena, do NOT use $arena->stopArena() in this function - will result in an recursive call
     * @param Arena $arena
     */
    public function stopArena(Arena $arena): void
    {
    }

    /**
     * Called right when a player joins a team in an arena of this game. Used to set up players
     * @param Player $player
     */
    public function onPlayerJoinTeam(Player $player): void
    {
    }

    /**
     * Callback function for array_filter
     * If return value is true, this entity will be deleted.
     * @param Entity $entity
     * @return bool
     */
    public function removeEntityOnArenaReset(Entity $entity): bool
    {
        return $entity instanceof ItemEntity;
    }

    /**
     * A method for setting up an arena.
     * @param Player $player The player who will run the setup
     */
    public function setupArena(Player $player): void
    {
        $form = new SimpleForm("Spleef arena setup");
        $na = "New arena";
        $form->addButton(new Button($na));
        $ea = "Edit arena";
        $form->addButton(new Button($ea));
        $form->setCallable(function (Player $player, $data) use ($na, $ea) {
            if ($data === $na) {
                $form = new SimpleForm("Spleef arena setup", "New arena via");
                $nw = "New world";
                $form->addButton(new Button($nw));
                $ew = "Existing world";
                $form->addButton(new Button($ew));
                $form->setCallable(function (Player $player, $data) use ($ew, $nw) {
                    $new = true;
                    if ($data === $ew) {
                        $new = false;
                        $form = new SimpleForm("Spleef arena setup", "New arena from $data");
                        foreach (API::getAllWorlds() as $worldName) {
                            $form->addButton(new Button($worldName));
                        }
                    } else {
                        $form = new CustomForm("Spleef arena setup");
                        $form->addElement(new Label("New arena from $data"));
                        $form->addElement(new Input("World name", "Example: bw4x1"));
                    }
                    $form->setCallable(function (Player $player, $data) use ($new) {
                        $setup["name"] = $new ? $data[1] : $data;
                        if ($new) {
                            API::$generator->generateLevel($setup["name"]);
                        }
                        Server::getInstance()->loadLevel($setup["name"]);
                        $form = new CustomForm("Spleef teams setup");
                        $form->addElement(new StepSlider("Maximum players per team", array_keys(array_fill(2, 15, ""))));
                        $form->setCallable(function (Player $player, $data) use ($new, $setup) {
                            $setup["maxplayers"] = intval($data[0]);
                            //New arena
                            $settings = new SpleefSettings($this->getDataFolder() . $setup["name"] . ".json");
                            $settings->maxPlayers = $setup["maxplayers"];
                            $settings->save();
                            $this->addArena($this->getNewArena($this->getDataFolder() . $setup["name"] . ".json"));
                            //Messages
                            $player->sendMessage(TextFormat::GOLD . TextFormat::BOLD . "Done! Spleef arena was set up with following settings:");
                            $player->sendMessage(TextFormat::AQUA . "World name: " . TextFormat::DARK_AQUA . $setup["name"]);
                            $player->sendMessage(TextFormat::AQUA . "Maximum players per team: " . TextFormat::DARK_AQUA . $setup["name"]);
                        });
                        $player->sendForm($form);
                    });
                    $player->sendForm($form);
                });
                $player->sendForm($form);
            } elseif ($data === $ea) {
                $form = new SimpleForm("Edit Spleef arena");
                $build = "Build in world";
                $button = new Button($build);
                $button->addImage(Button::IMAGE_TYPE_PATH, "textures/ui/icon_recipe_construction");
                $form->addButton($button);
                $delete = "Delete arena";
                $button = new Button($delete);
                $button->addImage(Button::IMAGE_TYPE_PATH, "textures/ui/trash");
                $form->addButton($button);
                $form->setCallable(function (Player $player, $data) use ($delete, $build) {
                    switch ($data) {
                        case $build:
                            {
                                $form = new SimpleForm($build, "Select the arena you'd like to build in");
                                foreach ($this->getArenas() as $arena) $form->addButton(new Button($arena->getLevelName()));
                                $form->setCallable(function (Player $player, $data) {
                                    $worldname = $data;
                                    $arena = API::getArenaByLevelName($this, $worldname);
                                    $this->getServer()->broadcastMessage("Stopping arena, reason: Admin actions", $arena->getPlayers());
                                    $arena->stopArena();
                                    $arena->setState(Arena::SETUP);
                                    if (!$this->getServer()->isLevelLoaded($worldname)) $this->getServer()->loadLevel($worldname);
                                    $player->teleport($arena->getLevel()->getSpawnLocation());
                                    $player->setGamemode(Player::CREATIVE);
                                    $player->setAllowFlight(true);
                                    $player->setFlying(true);
                                    $player->getInventory()->clearAll();
                                    $arena->getLevel()->stopTime();
                                    $arena->getLevel()->setTime(Level::TIME_DAY);
                                    $player->sendMessage(TextFormat::GOLD . "You may now freely edit the arena. You will not be able to break iron, gold or stained clay blocks, nor to place concrete YET");
                                });
                                $player->sendForm($form);
                                break;
                            }
                        case $delete:
                            {
                                $form = new SimpleForm("Delete Spleef arena", "Select an arena to remove. The world will NOT be deleted");
                                foreach ($this->getArenas() as $arena) $form->addButton(new Button($arena->getLevelName()));
                                $form->setCallable(function (Player $player, $data) {
                                    $worldname = $data;
                                    $form = new ModalForm("Confirm delete", "Please confirm that you want to delete the arena \"$worldname\"", "Delete $worldname", "Abort");
                                    $form->setCallable(function (Player $player, $data) use ($worldname) {
                                        if ($data) {
                                            $arena = API::getArenaByLevelName($this, $worldname);
                                            $this->deleteArena($arena) ? $player->sendMessage(TextFormat::GREEN . "Successfully deleted the arena") : $player->sendMessage(TextFormat::RED . "Removed the arena, but config file could not be deleted!");
                                        }
                                    });
                                });
                                $player->sendForm($form);
                                break;
                            }
                    }
                });
                $player->sendForm($form);
            }
        });
        $player->sendForm($form);
    }
}