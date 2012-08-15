Histone PHP
============

PHP реализация шаблонного движка Histone

[Сайт проекта](http://weblab.megafon.ru/histone/)  
[Документация](http://weblab.megafon.ru/histone/documentation/)  
[Для разработчиков](http://weblab.megafon.ru/histone/contributors/#PHP)  


Структура папок
---------------
|- src/ папка со всеми исходниками
     |- main/ папка с исходниками реализации histone-php
     |- test-generator/ папка с файлами необходимыми для запуска/генерации тестов
     |- test/ папка с самописными (не сгенерированными автоматически) тестами для PHP классов
     |- test-support/ папка со вспомогательными файлами для тестирования
|- target/ папка с результатами выполнения скрипта сборки
     |- reports/ папка с XML файлом junit.xml
     |- distrib/ папка с запакованным дистрибутивом Histone-php
     |- apidocs/ папка со сгенерированной API документацией
|- generated/ папка с временными и сгенерированными файлами
     |- test-cases-xml/ папка в которую будут распакованы приёмоные тесты Histone
     |- generated-tests/ папка в которой будут сложены сгенерированные phpunit тесты Histone
|- build.xml файл сос криптом сборки Histone-php
|- build.peoperties файл со настройками/свойствами проекта/скрипта сборки