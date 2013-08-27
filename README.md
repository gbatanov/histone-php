Histone PHP
============

PHP implementation template engine Histone

[The project site](http://weblab.megafon.ru/histone/)  
[Documentation](http://weblab.megafon.ru/histone/documentation/)  
[For developers](http://weblab.megafon.ru/histone/contributors/#Tests)  

Folder structure
---------------
	histone-php
	|- src/ папка со всеми исходниками
	     |- main/ папка с исходниками реализации histone-php
	     |- test-generator/ папка с файлами необходимыми для запуска/генерации тестов
	     |- test/ папка с приемными тестами для PHP классов (скачивается отдельно, 
			в текущем репозитории не присутствует)
	     |- test-support/ папка со вспомогательными файлами для тестирования
	|- target/ папка с результатами выполнения скрипта сборки (создается автоматически)
	     |- reports/ папка с XML файлом junit.xml
	     |- distrib/ папка с запакованным дистрибутивом Histone-php
	     |- apidocs/ папка со сгенерированной API документацией
	|- generated/ папка с временными и сгенерированными файлами (создается автоматически)
	     |- test-cases-xml/ папка в которую будут распакованы приёмоные тесты Histone
	     |- generated-tests/ папка в которой будут сложены сгенерированные phpunit тесты Histone
	|- build.xml файл со скриптом тестирования и сборки Histone-php
	|- build.peoperties файл с настройками/свойствами проекта/скрипта сборки

PHP реализация шаблонного движка Histone

[Сайт проекта](http://weblab.megafon.ru/histone/)  
[Документация](http://weblab.megafon.ru/histone/documentation/)  
[Для разработчиков](http://weblab.megafon.ru/histone/contributors/#PHP)  


Структура папок
---------------
	histone-php
	|- src/ папка со всеми исходниками
	     |- main/ папка с исходниками реализации histone-php
	     |- test-generator/ папка с файлами необходимыми для запуска/генерации тестов
	     |- test/ папка с приемными тестами для PHP классов (скачивается отдельно, 
			в текущем репозитории не присутствует)
	     |- test-support/ папка со вспомогательными файлами для тестирования
	|- target/ папка с результатами выполнения скрипта сборки (создается автоматически)
	     |- reports/ папка с XML файлом junit.xml
	     |- distrib/ папка с запакованным дистрибутивом Histone-php
	     |- apidocs/ папка со сгенерированной API документацией
	|- generated/ папка с временными и сгенерированными файлами (создается автоматически)
	     |- test-cases-xml/ папка в которую будут распакованы приёмоные тесты Histone
	     |- generated-tests/ папка в которой будут сложены сгенерированные phpunit тесты Histone
	|- build.xml файл со скриптом тестирования и сборки Histone-php
	|- build.peoperties файл с настройками/свойствами проекта/скрипта сборки
