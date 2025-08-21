<?php

namespace mod_quizgenerator\event;

defined('MOODLE_INTERNAL') || die();

/**
 * Event class for viewing a quizgenerator instance.
 */
class course_module_viewed extends \core\event\course_module_viewed
{

    /**
     * Init method.
     */
    protected function init()
    {
        parent::init();
        // Set nama tabel yang terkait dengan instance plugin ini.
        $this->data['objecttable'] = 'quizgenerator';
    }

    /**
     * Returns localised event name.
     *
     * @return string
     */
    public static function get_name()
    {
        return get_string('eventcoursemoduleviewed', 'mod_quizgenerator');
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description()
    {
        return "User with id {$this->userid} viewed the quizgenerator activity with instance id {$this->objectid} in the course module id {$this->contextinstanceid}.";
    }
}
