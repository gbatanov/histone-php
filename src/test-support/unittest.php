<?php
/**
 *    Copyright 2012 MegaFon
 *
 *   Licensed under the Apache License, Version 2.0 (the "License");
 *   you may not use this file except in compliance with the License.
 *   You may obtain a copy of the License at
 *
 *       http://www.apache.org/licenses/LICENSE-2.0
 *
 *   Unless required by applicable law or agreed to in writing, software
 *   distributed under the License is distributed on an "AS IS" BASIS,
 *   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *   See the License for the specific language governing permissions and
 *    limitations under the License.
 */

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

$PHP_BIN_DIR = str_replace('\\', '/', getenv('PHP_PEAR_PHP_BIN'));
$PHP_PEAR_BIN_DIR = str_replace('\\', '/', getenv('PHP_PEAR_BIN_DIR'));
if (!$PHP_BIN_DIR)
/* Замените путь на путь к скрипту (не папке!) phpunit */
	$PHP_BIN_DIR = "C:/Zend/ZendServer/bin";
if (!$PHP_PEAR_BIN_DIR)
	$PHP_PEAR_BIN_DIR = $PHP_BIN_DIR;

$path_parts = pathinfo($argv[0]);
//$WORK_DIR = str_replace('\\', '/', $path_parts['dirname']) . '/';

$WORK_DIR = implode('/', explode('/', str_replace('\\', '/', __DIR__), -2));


$command = 'php ' . '"' . $PHP_PEAR_BIN_DIR . '/phpunit"   --bootstrap "' . $WORK_DIR . '/src/test-support/tests-bootstrap.php" --log-junit "' . $WORK_DIR . '/target/reports/ParserAcceptanceTest.xml" "' . $WORK_DIR . '/generated/generated-tests/ParserAcceptanceTest.php"';

echo "Executing: $command \n";
$res = `$command`;
if (strpos($res, 'PHPUnit') === false && strpos($res, 'Time') === false) // PHPUnit не выполнялся вообще или не дошел до вывода результатов
	throw new Exception($res);
else
	echo 'PHPUnit OK ('.$res.")\n";
?>
