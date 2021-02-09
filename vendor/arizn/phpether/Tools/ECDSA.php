<?php
/**
 *
 * @author Ofumbi Stephen
 */
namespace phpEther\Tools;
use Mdanter\Ecc\EccFactory;
if (!extension_loaded('gmp')) {
    throw new \Exception('GMP extension seems not to be installed');
}
class ECDSA
{
	
	/**
     * Hex Encode
     *
     * Encodes a decimal $number into a hexadecimal string.
     *
     * @param    int $number
     * @return    string
     */
    public static function hex_encode($number)
    {
        $hex = gmp_strval(gmp_init($number, 10), 16);
        return (strlen($hex) % 2 != 0) ? '0' . $hex : $hex;
    }
    /**
     * Hex Decode
     *
     * Decodes a hexadecimal $hex string into a decimal number.
     *
     * @param    string $hex
     * @return    int
     */
    public static function hex_decode($hex)
    {
        return gmp_strval(gmp_init($hex, 16), 10);
    }
	
	
	public static function decompress_public_key($key)
    {
        $math = EccFactory::getAdapter();
        $y_byte = substr($key, 0, 2);
        $x_coordinate = substr($key, 2);
        $x = self::hex_decode($x_coordinate);
        $theory = $math->getNumberTheory($math);
        $generator = EccFactory::getSecgCurves($math)->generator256k1();
        $curve = $generator->getCurve();
        try {
            $x3 = $math->powmod(gmp_init($x, 10), gmp_init(3, 10), $curve->getPrime());
            $y2 = $math->add($x3, $curve->getB());
            $y0 = $theory->squareRootModP($y2, $curve->getPrime());
            if ($y0 == null) {
                throw new \InvalidArgumentException("Invalid public key");
            }
            $y1 = $math->sub($curve->getPrime(), $y0);
            $y = ($y_byte == '02')
                ? (($math->mod($y0, gmp_init(2, 10)) == '0') ? $y0 : $y1)
                : (($math->mod($y0, gmp_init(2, 10)) !== '0') ? $y0 : $y1);
            $y_coordinate = str_pad($math->decHex(gmp_strval($y)), 64, '0', STR_PAD_LEFT);
            $point = $curve->getPoint(gmp_init($x, 10), $y);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("Invalid public key");
        }
        return '04' . $x_coordinate . $y_coordinate;
    }
}