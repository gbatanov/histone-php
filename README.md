Histone PHP
============

PHP implementation template engine Histone

[The project site](http://weblab.megafon.ru/histone/)  
[Documentation](http://weblab.megafon.ru/histone/documentation/)  
[For developers](http://weblab.megafon.ru/histone/contributors/#Tests)  

Folder structure
---------------
	histone-php
	|- src/ folder with all the source code
	     |- main/ folder with the source of the histone-php
	     |- test-templates/ folder with the files necessary to run / test generation
	     |- test-support/ folder with supporting files for testing
	|- target/ folder with the results of the build script (created automatically)
	     |- reports/ folder with XML file ParserAcceptanceTest.xml
	     |- distrib/ folder with packed distribution Histone-php
	     |- apidocs/ folder with generated API documentation
	|- generated/ folder with the time and generated files (created automatically)
	     |- test-cases-xml/ folder in which to unpack the acceptance tests Histone
	     |- generated-tests/folder which will be folded generated phpunit tests Histone
	|- build.xml file with the script testing and assembly of Histone-php
	|- build.peoperties configuration file build script

PHP реализация шаблонного движка Histone

[Сайт проекта](http://weblab.megafon.ru/histone/)  
[Документация](http://weblab.megafon.ru/histone/documentation/)  
[Для разработчиков](http://weblab.megafon.ru/histone/contributors/#PHP)  


Структура папок
---------------
	histone-php
	|- src/ папка со всеми исходниками
	     |- main/ папка с исходниками реализации histone-php
	     |- test-templates/ папка с файлами необходимыми для запуска/генерации тестов
	     |- test-support/ папка со вспомогательными файлами для тестирования
	|- target/ папка с результатами выполнения скрипта сборки (создается автоматически)
	     |- reports/ папка с XML файлом ParserAcceptanceTest.xml
	     |- distrib/ папка с запакованным дистрибутивом Histone-php
	     |- apidocs/ папка со сгенерированной API документацией
	|- generated/ папка с временными и сгенерированными файлами (создается автоматически)
	     |- test-cases/ папка в которую будут распакованы приёмочные тесты Histone
	     |- generated-tests/ папка в которой будут сложены сгенерированные phpunit тесты Histone
	|- build.xml файл со скриптом тестирования и сборки Histone-php
	|- build.peoperties файл с настройками скрипта сборки
