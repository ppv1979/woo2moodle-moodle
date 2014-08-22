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
 * Version information
 *
 * @package    auth_woo2moodle
 * @copyright  2014 Pavel Pisklakov
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * Based on Tim St.Clair's wp2moodle plugin (http://timstclair.me)
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2014082200;        // The current plugin version (Date: YYYYMMDDXX)
$plugin->requires  = 2014051200;        // Requires Moodle 2.7 version
$plugin->component = 'auth_woo2moodle';       // Full name of the plugin (used for diagnostics)
$plugin->maturity = MATURITY_BETA;
$plugin->release = '0.2';
