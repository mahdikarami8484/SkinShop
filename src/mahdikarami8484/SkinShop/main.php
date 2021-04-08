<?php

// بسم الله ارحمن ارحیم

namespace mahdikarami8484\SkinShop;

use pocketmine\entity\Skin;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

use pocketmine\event\Listener;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\utils\TextFormat as T;

class main extends PluginBase implements Listener
{
    /** @var array */
    public static $skinPaths;

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

    public function onEnable()
    {
        //scins folder
        @mkdir($this->getDataFolder() . "skins");
        //prices config
        if (!file_exists($this->getDataFolder() . "prices.yml")) {
            $cfg = new Config($this->getDataFolder() . "prices.yml");
            $cfg->set('note', "#write skin price (if skin is skin.png write skin: 20000 if skin price is 20000)");
            $cfg->save();
        }
        $cfg = new Config($this->getDataFolder() . "prices.yml");
        $i = 0;
        foreach (scandir($this->getDataFolder() . "skins") as $skin) {
            if ($skin != "." and $skin != "..") {
                //gereftan esm skin as folan.png
                $name = explode(".", $skin)[0];
                if (!is_null($cfg->get($name))) { //agar meghdar adadi baray felan gozashte bood

                    //[0] return data folder path
                    //[1] return skin name
                    self::$skinPaths[$i] = [$this->getDataFolder() . "/skins/" . $skin, $name];
                    $i++;
                } else continue;
            }
        }
        $this->eco = $this->getServer()->getPluginManager()->getPlugin('EconomyAPI');
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onCommand(CommandSender $player, Command $cmd, string $label, array $args): bool
    {
        switch ($cmd->getName()) {
            case "sh":
                if (!$player instanceof player) {
                    $player->sendMessage("use this cmd in game");
                    return false;
                }
                $this->form($player);
                break;
        }
        return true;
    }

    public function form(Player $player)
    {
        $cfg = new Config($this->getDataFolder() . "prices.yml");
        $api = $this->getServer()->getPluginManager()->getPlugin('FormAPI');
        $form = $api->createSimpleForm(function (Player $player, $data) use ($cfg) {
            if ($data === null) {
                return true;
            }
            //money kamtar
            if ($this->eco->myMoney($player) >= $cfg->get(self::$skinPaths[$data][1])) {
                $this->eco->reduceMoney($player, $cfg->get(self::$skinPaths[$data][1]));
                $skinPath = self::$skinPaths[$data][0];
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
                $player->setSkin(new Skin($player->getSkin()->getSkinId(), $skinbytes, "", "", ""));
                $player->sendSkin();
            } else {
                $player->sendMessage(T::DARK_RED . "[  !  ] Your money is not enough");
            }
        });
        $form->setTitle("§4§l<< &bSkin &eShop §4>>");
        foreach (self::$skinPaths as $name => $array) {
            $price = $cfg->get($array[1]);
            $form->addButton(self::$colors[rand(0, (count(self::$colors) - 1))] . $array[1] . "    $price$");
        }
        $form->sendToPlayer($player);
        return $form;
    }
}
// الهم صله الله محمد و اله محمد
