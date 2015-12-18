# trusted-php

## Установка
- Скачать архив [trusted-php-master.zip](https://github.com/Digt/trusted-php/archive/master.zip)
- Распаковать архив `trusted-php-master.zip` на `WEB` сервере.

## Настройка
- Зарегистрировать сайт на сервисе [net.trusted.ru](https://net.trusted.ru)
  - Создать новый аккаунт
  - Зарегистрировать компанию (организацию)
  - Создать приложение (сайт)
- Настройка параметров работы модулей осуществляется в файле `settings.php` (trusted/settings.php)
  - Задать переменную `TRUSTED_MODULE_PATH`, если модуль размещается не в корневом каталоге
  - Задать `TRUSTED_LOGIN_CLIENT_ID` и `TRUSTED_LOGIN_CLIENT_SECRET` (создаеются при регистрации нового приложения)
  - Задать параметры для доступа к базе данных
- Выполнить `SQL` скрипты для формирования таблиц данных ([login.sql](https://github.com/Digt/trusted-php/blob/master/trusted/login/sql/install.sql), [esign.sql](https://github.com/Digt/trusted-php/blob/master/trusted/esign/sql/install.sql))

## Установка клиентского приложения

- Скачать дистрибутив [trusted](https://net.trusted.ru/trustedapp/app/trustednet-client)
- Установить клиентское приложение `trusted`

---

## Installing
- Download distribution [trusted-php-master.zip](https://github.com/Digt/trusted-php/archive/master.zip)
- Extract files from `trusted-php-master.zip` to your `WEB` server.

## Setting
- Register website on service [net.trusted.ru](https://net.trusted.ru)
  - Create new user account
  - Register company
  - Create application (website)
- Set parameters in the configuration file `settings.php` (trusted/settings.php)
  - Set parameter `TRUSTED_MODULE_PATH`
  - Set parameters `TRUSTED_LOGIN_CLIENT_ID` and `TRUSTED_LOGIN_CLIENT_SECRET`
  - Set Database connection parameters
- Run `SQL` script to create required Database tables ([login.sql](https://github.com/Digt/trusted-php/blob/master/trusted/login/sql/install.sql), [esign.sql](https://github.com/Digt/trusted-php/blob/master/trusted/esign/sql/install.sql))

## Installing client software

- Download distribution [trusted](https://net.trusted.ru/trustedapp/app/trustednet-client)
- Setup application `trusted` using Wizard

---

[WiKi](../../wiki)
