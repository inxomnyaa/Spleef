<?php


namespace xenialdan\Spleef;


use pocketmine\block\BlockIds;
use xenialdan\gameapi\DefaultSettings;

class SpleefSettings extends DefaultSettings
{
    public $noDamageTeam = true;
    public $noEnvironmentDamage = true;
    public $clearInventory = true;
    public $noBlockDrops = false;
    public $immutableWorld = false;
    public $noBreak = true;
    public $noBuild = true;
    public $breakBlockIds = [BlockIds::SNOW_BLOCK];
    public $noBed = true;
    public $startNoWalk = false;
    public $noDropItem = true;
    public $noDamageEntities = true;
    public $noFallDamage = true;
    public $noExplosionDamage = true;
    public $noDrowningDamage = true;
    public $noInventoryEditing = true;
    /** @var int */
    public $maxPlayers = 16;
}