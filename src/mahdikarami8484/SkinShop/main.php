<?php

namespace mahdikarami8484\SkinShop;

use mahdikarami8484\SkinShop\Lib\SimpleForm;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\entity\Skin;
use pocketmine\event\player\PlayerChangeSkinEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;

use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\utils\TextFormat as T;

class main extends PluginBase implements Listener
{
    /** @var array */
    public static $skins = [];

    /** @var TextFormat[] */
    public static $colors = [
        T::DARK_BLUE,
        T::DARK_GREEN,
        T::DARK_AQUA,
        T::DARK_RED,
        T::DARK_PURPLE,
        T::GOLD,
        T::BLUE,
        T::GREEN,
        T::AQUA,
        T::RED,
        T::LIGHT_PURPLE,
        T::YELLOW
    ];

    /** @var EconomyAPI */
    public $eco;

    /** @var Skin[]  */
    private $PlayerSkins;

    /** @var Config */
    public $db;

    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        @mkdir($this->getDataFolder() . "skins");
        $this->saveDefaultConfig();
        $cfg = $this->getConfig();
        $this->db = new Config($this->getDataFolder() . "db.json");
        foreach (scandir($this->getDataFolder() . "skins") as $skin) {
            if ($skin != "." and $skin != "..") {
                $name = explode(".", $skin)[0];
                if (is_numeric($cfg->get($name))) {
                    self::$skins[] = $name;
                } else continue;
            }
        }
        $this->eco = $this->getServer()->getPluginManager()->getPlugin('EconomyAPI');
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        switch ($command->getName()) {
            case "sh":
                if (!$sender instanceof player) {
                    $player->sendMessage("use this cmd in game");
                    return false;
                }
                if (isset($args[0]) and $args[0] == 'reset') {
                    if (isset($this->PlayerSkins[$sender->getName()])) {
                        $sender->setSkin($this->PlayerSkins[$sender->getName()]);
                        $sender->sendSkin();
                        unset($this->PlayerSkins[$sender->getName()]);
                        $sender->sendMessage("§aThe Skin was reset");
                    } else {
                        $sender->sendMessage("§c[!] You cant reset your skin");
                    }
                    return true;
                }
                $this->form($sender);
        }
        return parent::onCommand($sender, $command, $label, $args);
    }

    public function form(Player $player)
    {
        $cfg = $this->getConfig();
        $api = $this->getServer()->getPluginManager()->getPlugin('FormAPI');
        $form = new SimpleForm(function (Player $player, $data) use ($cfg) {
            if ($data === null) {
                return true;
            }
            $skinName = self::$skins[$data];

            if ($this->isOwned($player, $skinName)) {
                if(!isset($this->PlayerSkins[$player->getName()])){
                    $this->PlayerSkins[$player->getName()] = $player->getSkin();
                }
                $player->setSkin(new Skin($player->getSkin()->getSkinId(), $this->pngToSkinBytes($this->getDataFolder() . "skins/$skinName.png"), "", "", ""));
                $player->sendSkin();
                $player->sendMessage("§aSkin was set\n§cTo reset your skin use /sh reset");
                return true;
            }

            $price = $cfg->get($skinName);
            if ($this->eco->myMoney($player) >= $price) {
                $this->eco->reduceMoney($player, $price);
                $this->setOwned($player, $skinName);
                $player->sendMessage("§aYou bought $skinName Skin");
            } else {
                $player->sendMessage(T::DARK_RED . "[  !  ] Your money is not enough");
            }
        });
        $form->setTitle("§4§l<< §bSkin §eShop §4>>");
if(count(self::skins) == 0){
$from->addButton ("§4 please set price and skin");
$form->sendToPlayer($player);
        return $form;
}else {
        foreach (self::$skins as $name) {
            $price = $cfg->get($name);
            $form->addButton(self::$colors[array_rand(self::$colors)] . "$name    $price$" . $this->ownStatus($player, $name));
        }
        $form->sendToPlayer($player);
        return $form;
    }

    public function isOwned(Player $player, string $skinName) : bool
    {
        if (!is_array($this->db->getNested($player->getName()))) {
            return false;
        }
        return in_array($skinName, $this->db->getNested($player->getName()));
    }

    public function ownStatus(Player $player, string $skinName)
    {
        if (!is_array($this->db->getNested($player->getName()))) {
            return '     §4Not Owned';
        }
        return in_array($skinName, $this->db->getNested($player->getName())) ? '     §2Owned' : '     §4Not Owned';
    }

    public function setOwned(Player $player, string $skinName)
    {
        $skins = is_array($this->db->getNested($player->getName())) ? $this->db->getNested($player->getName()) : [];
        array_push($skins, $skinName);
        $this->db->setNested($player->getName(), $skins);
        $this->db->save();
    }


    public function pngToSkinBytes(string $skinPath)
    {
        $img = @imagecreatefrompng($skinPath);
        $skinbytes = "";
        $s = (int)@getimagesize($skinPath)[1];
        for ($y = 0; $y < $s; $y++) {
            for ($x = 0; $x < 64; $x++) {
                $colorat = @imagecolorat($img, $x, $y);
                $a = ((~((int)($colorat >> 24))) << 1) & 0xff;
                $r = ($colorat >> 16) & 0xff;
                $g = ($colorat >> 8) & 0xff;
                $b = $colorat & 0xff;
                $skinbytes .= chr($r) . chr($g) . chr($b) . chr($a);
            }
        }
        @imagedestroy($img);
        return $skinbytes;
    }
}
}
