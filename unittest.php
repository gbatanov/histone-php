<?php

/**
 * Запуск из командной строки Windows 
 * >unittest.php 
 * или (если расширение не зарегистрировано)
 * >php -f unittest.php 
 * 
 * Путь к php и phpunit должен быть зарегистрирован в системе
 * 
 * @package unittest
 */
$PHP_BIN_DIR = str_replace('\\', '/', dirname(getenv('PHP_PEAR_PHP_BIN')));
$PHP_PEAR_BIN_DIR = str_replace('\\', '/', getenv('PHP_PEAR_BIN_DIR'));
if (!$PHP_BIN_DIR)
/* Замените путь на путь к скрипту (не папке!) phpunit */
	$PHP_BIN_DIR = "C:/Zend/ZendServer/bin";
if (!$PHP_PEAR_BIN_DIR)
	$PHP_PEAR_BIN_DIR = $PHP_BIN_DIR;

$path_parts = pathinfo($argv[0]);
$WORK_DIR = str_replace('\\', '/', $path_parts['dirname']) . '/';
$command = 'php ' . '"' . $PHP_PEAR_BIN_DIR . '/phpunit"   --bootstrap "' . $WORK_DIR . 'main/unittests/bootstrap.php" --log-junit "' . $WORK_DIR . 'TESTS-TestSuites.xml" "' . $WORK_DIR . 'main/unittests/PHP/main/php/SpondeTest.php"';

$res = `$command`;
if (strpos($res, 'PHPUnit') === false && strpos($res, 'Time') === false) // PHPUnit не выполнялся вообще или не дошел до вывода результатов
	throw new Exception($res);
else
	echo $res;
?>
