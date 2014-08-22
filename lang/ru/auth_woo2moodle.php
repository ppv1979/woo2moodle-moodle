<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for Woocommerce/Moodle Single Sign On plugin
 *
 * @package   auth_woo2moodle
 * @copyright 2014 onwards Pavel Pisklakov
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * Based on Tim St.Clair's wp2moodle plugin (http://timstclair.me)
 */

$string['auth_woo2moodle_secretkey'] = 'Ключ шифрования';
$string['auth_woo2moodle_secretkey_desc'] = 'Должен совпадать со значением, заданным в плагине для Wordpress/WooCommerce';

$string['auth_woo2moodledescription'] = 'Использует информацию о пользователе Wordpress/Woocommerce для создания пользователя и входа в Moodle';
$string['pluginname'] = 'WooCommerce 2 Moodle (SSO)';

$string['auth_woo2moodle_timeout'] = 'Время жизни ссылки';
$string['auth_woo2moodle_timeout_desc'] = 'Количество минут до того момента, как ссылка будет считаться некорректной (максимальное время прочтения пользователем страницы Wordpress или письма электронной почты)';

$string['auth_woo2moodle_logoffurl'] = 'URL выхода из системы';
$string['auth_woo2moodle_logoffurl_desc'] = 'URL, на который перенаправляется пользователь при нажатии «Выход»';

$string['auth_woo2moodle_autoopen_desc'] = 'Автоматически открывает первый курс глобальной группы после успешной авторизации';
$string['auth_woo2moodle_autoopen'] = 'Выполнять автоматическое открытие курса?';

$string['auth_woo2moodle_updateuser'] = 'Обновлять поля профиля пользователя, используя значения Wordpress/WooCommerce?';
$string['auth_woo2moodle_updateuser_desc'] = 'Если параметр включен, поля профиля пользователя (имя, фамилия и некоторые другие) будут перезаписаны каждый раз при использовании SSO. Выключите этот параметр, если вы хотите, чтобы пользователи управляли полями своего профиля независимо от значений Wordpress/WooCommerce.';
