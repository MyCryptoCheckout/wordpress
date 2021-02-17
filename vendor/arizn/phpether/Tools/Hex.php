<?php
namespace phpEther\Tools;

class Hex
{
	
	static function buffer($hex)
    {
		if(is_array($hex)||is_object($hex)){
			array_walk($hex,function(&$val ){
				$val = \phpEther\Tools\Hex::buffer($val);
			});
			return $hex;
		}
		if(substr($hex,0,2) != '0x'&&is_numeric($hex)){
			return \BitWasp\Buffertools\Buffer::Int($hex);
		}
		if(is_string($hex)){
			if(substr($hex,0,2) === '0x')
			return \BitWasp\Buffertools\Buffer::hex(self::cleanPrefix($hex));
			return new \BitWasp\Buffertools\Buffer($hex);
		}
		if(is_int($hex)){
			return \BitWasp\Buffertools\Buffer::Int($hex);
		}
		return new \BitWasp\Buffertools\Buffer($hex);
    }
	
	
	static function fromInt(int $integer)
    {
        $hex = dechex($integer);
        if (strlen($hex) % 2) {
            $hex = "0" . $hex;
        }
        return $hex;
    }
	
    static function fromDec( $integer , $zeroprefix = true)
    {
		if(substr($integer,0,2) === '0x') {
            return $integer;
        }
        $hex = dechex($integer);
        if ($zeroprefix && strlen($hex) % 2) {
            $hex = "0" . $hex;
        }
        return '0x'.$hex;
    }
	
	
	static function toDec(string $hex)
    {
       return  hexdec(self::cleanPrefix($hex));
        
    }

    static function trim($hex)
    {
        while (substr($hex, 0, 2) === "00") {
            $hex = substr($hex, 2);
        }

        return $hex;
    }
	
	static function getHex($hex)
    {
		//var_dump($hex);
		$hex = $hex->getHex() ;
		while (substr($hex, 0, 2) === "00") {
            $hex = substr($hex, 2);
        }
		return str_replace('0x0','0x','0x'.$hex);
    }
	
    static function cleanPrefix($hex)
    {
		
        if(substr($hex,0,2) === '0x') {
            $hex = substr($hex, 2);
        }
		while (substr($hex, 0, 2) === "00") {
            $hex = substr($hex, 2);
        }
		if (strlen($hex) % 2) {
            $hex = "0" . $hex;
        }
        return $hex;
    }
}
