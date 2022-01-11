<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * External functions and services provided by the plugin are declared here.
 *
 * @package     local_bird_mdm
 * @category    external
 * @copyright   2022 Roland Hager <kontakt@roland-hager.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$services = [
    'BIRD Metadata Management' => [
        'functions' => [],
        'shortname' => 'bird',
        'enabled' => true,
        'downloadfiles' => false,
        'uploadfiles' => false,
    ],
];
