<?php

namespace Mhacker\SkinShop;

class SkinConvertor
{
    public function __construct()
    {
    }

    public function convert(string $path)
    {
        $skinPath = $path;
        $img = @imagecreatefrompng($skinPath);
        $skinbytes = "";
        $s = (int)@getimagesize($skinPath)[1];
        for($y = 0; $y < $s; $y++) {
            for($x = 0; $x < 64; $x++) {
                $colorat = @imagecolorat($img, $x, $y);
                $a = ((~((int)($colorat >> 24))) << 1) & 0xff;
                $r = ($colorat >> 16) & 0xff;
                $g = ($colorat >> 8) & 0xff;
                $b = $colorat & 0xff;
                $skinbytes .= chr($r) . chr($g) . chr($b) . chr($a);
            }
        }
        @imagedestroy($img);
    }
}