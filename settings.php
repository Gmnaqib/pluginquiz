<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_getdocs', get_string('pluginname', 'local_getdocs'));
    $ADMIN->add('localplugins', $settings);
}
