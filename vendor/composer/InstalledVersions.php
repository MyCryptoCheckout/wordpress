<?php











namespace Composer;

use Composer\Semver\VersionParser;






class InstalledVersions
{
private static $installed = array (
  'root' => 
  array (
    'pretty_version' => 'dev-master',
    'version' => 'dev-master',
    'aliases' => 
    array (
    ),
    'reference' => 'ca8d1e96c1903f9f4c1f5ae707e373bab9b41c1b',
    'name' => '__root__',
  ),
  'versions' => 
  array (
    '__root__' => 
    array (
      'pretty_version' => 'dev-master',
      'version' => 'dev-master',
      'aliases' => 
      array (
      ),
      'reference' => 'ca8d1e96c1903f9f4c1f5ae707e373bab9b41c1b',
    ),
    'bitwasp/bitcoin' => 
    array (
      'pretty_version' => 'v0.0.35.1',
      'version' => '0.0.35.1',
      'aliases' => 
      array (
      ),
      'reference' => '14dd3eb03165d01c5ed3bcd77cd139ed0b3449c9',
    ),
    'bitwasp/buffertools' => 
    array (
      'pretty_version' => 'v0.4.7',
      'version' => '0.4.7.0',
      'aliases' => 
      array (
      ),
      'reference' => '9683014f59b401f6858ce069ebddc35140463fe1',
    ),
    'bitwasp/secp256k1-php' => 
    array (
      'pretty_version' => 'v0.1.4',
      'version' => '0.1.4.0',
      'aliases' => 
      array (
      ),
      'reference' => 'dba022a223a5b6d3480ac30f119008ef036a7e34',
    ),
    'btccom/cashaddress' => 
    array (
      'pretty_version' => 'v0.0.3',
      'version' => '0.0.3.0',
      'aliases' => 
      array (
      ),
      'reference' => '29501f7875eca5325d61b4634714028db78bfc5f',
    ),
    'composer/semver' => 
    array (
      'pretty_version' => '1.7.2',
      'version' => '1.7.2.0',
      'aliases' => 
      array (
      ),
      'reference' => '647490bbcaf7fc4891c58f47b825eb99d19c377a',
    ),
    'fgrosse/phpasn1' => 
    array (
      'pretty_version' => 'v1.5.4',
      'version' => '1.5.4.0',
      'aliases' => 
      array (
      ),
      'reference' => '4fe0afb91b4ce3ca08c63d9cf31cec1150828e97',
    ),
    'lastguest/murmurhash' => 
    array (
      'pretty_version' => '1.3.0',
      'version' => '1.3.0.0',
      'aliases' => 
      array (
      ),
      'reference' => '8eb06483456bc98f5adb7707d981a8ef6a065fa2',
    ),
    'mdanter/ecc' => 
    array (
      'pretty_version' => 'v0.4.3',
      'version' => '0.4.3.0',
      'aliases' => 
      array (
      ),
      'reference' => 'fa3405da1b2bb4772a0c908c65b0c3e9dde4ccfd',
    ),
    'mycryptocheckout/api' => 
    array (
      'pretty_version' => '20211021',
      'version' => '20211021',
      'aliases' => 
      array (
      ),
      'reference' => 'f9e33f34ab6dff5d7cfa2c327d2552f19bfd5f08',
    ),
    'paragonie/random_compat' => 
    array (
      'pretty_version' => 'v2.0.20',
      'version' => '2.0.20.0',
      'aliases' => 
      array (
      ),
      'reference' => '0f1f60250fccffeaf5dda91eea1c018aed1adc2a',
    ),
    'pleonasm/merkle-tree' => 
    array (
      'pretty_version' => '1.0.0',
      'version' => '1.0.0.0',
      'aliases' => 
      array (
      ),
      'reference' => '9ddc9d0a0e396750fada378f3aa90f6c02dd56a1',
    ),
  ),
);







public static function getInstalledPackages()
{
return array_keys(self::$installed['versions']);
}









public static function isInstalled($packageName)
{
return isset(self::$installed['versions'][$packageName]);
}














public static function satisfies(VersionParser $parser, $packageName, $constraint)
{
$constraint = $parser->parseConstraints($constraint);
$provided = $parser->parseConstraints(self::getVersionRanges($packageName));

return $provided->matches($constraint);
}










public static function getVersionRanges($packageName)
{
if (!isset(self::$installed['versions'][$packageName])) {
throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
}

$ranges = array();
if (isset(self::$installed['versions'][$packageName]['pretty_version'])) {
$ranges[] = self::$installed['versions'][$packageName]['pretty_version'];
}
if (array_key_exists('aliases', self::$installed['versions'][$packageName])) {
$ranges = array_merge($ranges, self::$installed['versions'][$packageName]['aliases']);
}
if (array_key_exists('replaced', self::$installed['versions'][$packageName])) {
$ranges = array_merge($ranges, self::$installed['versions'][$packageName]['replaced']);
}
if (array_key_exists('provided', self::$installed['versions'][$packageName])) {
$ranges = array_merge($ranges, self::$installed['versions'][$packageName]['provided']);
}

return implode(' || ', $ranges);
}





public static function getVersion($packageName)
{
if (!isset(self::$installed['versions'][$packageName])) {
throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
}

if (!isset(self::$installed['versions'][$packageName]['version'])) {
return null;
}

return self::$installed['versions'][$packageName]['version'];
}





public static function getPrettyVersion($packageName)
{
if (!isset(self::$installed['versions'][$packageName])) {
throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
}

if (!isset(self::$installed['versions'][$packageName]['pretty_version'])) {
return null;
}

return self::$installed['versions'][$packageName]['pretty_version'];
}





public static function getReference($packageName)
{
if (!isset(self::$installed['versions'][$packageName])) {
throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
}

if (!isset(self::$installed['versions'][$packageName]['reference'])) {
return null;
}

return self::$installed['versions'][$packageName]['reference'];
}





public static function getRootPackage()
{
return self::$installed['root'];
}







public static function getRawData()
{
return self::$installed;
}



















public static function reload($data)
{
self::$installed = $data;
}
}
