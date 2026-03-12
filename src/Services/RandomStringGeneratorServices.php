<?php
/**
 * Created by PhpStorm.
 * User: LANGANFIN Rogelio
 * Date: 18/08/2021
 * Time: 16:44
 */

namespace App\Services;


class RandomStringGeneratorServices
{
    private $lengthRandomId;
    private $numberItteration;

    public function __construct()
    {
        $this->lengthRandomId = 6;
        $this->numberItteration = 2;
    }
    function random_alphanumeric_custom_itteration($prefix = "RAND")
    {
        $string = "";
        $itteration = $this->numberItteration;
        for ($i = 0; $i < $itteration; $i++) {
            $string .= $this->random_alphanumeric();
            if ($i < $itteration - 1) {
                $string .= "-";
            }
        }
        return $prefix . "-" . $string;

    }
    function random_alphanumeric_full($prefix = "RAND")
    {
        $string = "";
        $itteration = $this->numberItteration;
        for ($i = 0; $i < $itteration; $i++) {
            $string .= $this->random_alphanumeric();
            if ($i < $itteration - 1) {
                $string .= "-";
            }
        }
        return $prefix . "-" . $string;

    }

    function random_alphanumeric()
    {
        $length = $this->lengthRandomId;
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ12345689';
        $my_string = '';
        for ($i = 0; $i < $length; $i++) {
            $pos = mt_rand(0, strlen($chars) - 1);
            $my_string .= substr($chars, $pos, 1);
        }
        return $my_string;
    }
    function random_alphanumeric_custom_length($length)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ12345689';
        $my_string = '';
        for ($i = 0; $i < $length; $i++) {
            $pos = mt_rand(0, strlen($chars) - 1);
            $my_string .= substr($chars, $pos, 1);
        }
        return $my_string;
    }

}