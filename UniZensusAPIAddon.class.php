<?php
/**
 * UniZensusAPIAddon.class.php
 *
 * Provides Method to extend the rest api with additonal data for courses
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @version 1.0
 */

class UniZensusAPIAddon extends StudIPPlugin implements SystemPlugin
{
    /**
     * Plugin constructor.
     */
    public function __construct()
    {
        parent::__construct();
        NotificationCenter::addObserver($this, 'getAdditionalAPIDataForCourses', 'restip.courses.get');
        NotificationCenter::addObserver($this, 'getAdditionalAPIDataForCourses', 'restip.courses-course_id.get');
        NotificationCenter::addObserver($this, 'getAdditionalAPIDataForCourses', 'restip.courses-semester-semester_id.get');
    }

    /**
     * Event handler for modifying course data
     */
    public function getAdditionalAPIDataForCourses()
    {
        $addon  = $this;
        $router = RestIP\Router::getInstance(null);
        $router->hook('restip.before.render', function () use ($router, $addon) {
            $result = $router->getRouteResult();

            if (key($result) === 'course') {
                if (empty($result['course']['course_id'])) {
                    return;
                }

                $unizensus = $addon->loadUniZensusPlugin($result['course']['course_id']);
                if (!$unizensus) {
                    return;
                }

                $result['course'] = $addon->extendCourse($result['course'], $unizensus);
            } elseif (key($result) === 'courses') {
                foreach ($result['courses'] as $index => $course) {
                    if (empty($course['course_id'])) {
                        continue;
                    }

                    $unizensus = $addon->loadUniZensusPlugin($course['course_id']);
                    if (!$unizensus) {
                        continue;
                    }

                    $result['courses'][$index] = $addon->extendCourse($course, $unizensus);
                }
            }

            $router->setRouteResult($result);
        });
    }

    /**
     * Extend given course array with unizensus data.
     *
     * @param Array           $course    Array with course info from rest.ip
     * @param UniZensusPlugin $unizensus Instance of unizensus plugin
     * @param Array Modified course arraya
     */
    public function extendCourse($course, $unizensus)
    {
        if ($course['modules']['unizensus'] = $unizensus->isVisible()) {
            if (!isset($course['additonal_data'])) {
                $course['additional_data'] = array();
            }
            $course['additional_data']['unizensus'] = array(
                'type' => $unizensus->course_status['pdfquestionnaire'] ? 'paper' : 'online',
                'url'  => $unizensus->course_status['pdfquestionnaire']
                              ? false
                              : $unizensus->RPC->getEvaluationURL('questionnaire',$unizensus->getZensusCourseId(),$GLOBALS['user']->id),
            );
        }

        return $course;
    }

    /**
     * Check for existence and activation of unizensus plugin.
     * Return initialized plugin for the given course.
     *
     * @param String $course_id Id of the course in question
     * @return mixed Either false if plugin is missing or not activated,
     *               initialized instance of UniZensusPlugin otherwise
     */
    public function loadUniZensusPlugin($course_id)
    {
        static $plugin_id = null;

        if ($plugin_id === null) {
            if (!class_exists('UniZensusPlugin')) {
                $filename = Config::get()->PLUGINS_PATH . '/data-quest/UniZensusPlugin/UniZensusPlugin.class.php';
                if (!file_exists($filename)) {
                    return false;
                }
                require_once $filename;
            }

            $query = "SELECT pluginid
                      FROM plugins
                      WHERE pluginclassname = 'UniZensusPlugin' AND enabled = 'yes'";
            $statement = DBManager::get()->query($query);
            $plugin_id = $statement->fetchColumn() ?: false;
        }

        if ($plugin_id === false) {
            return false;
        }

        $query = "SELECT 1
                  FROM plugins_activated
                  WHERE pluginid = :plugin_id AND poiid = CONCAT('sem', :course_id) AND state = 'on'";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':plugin_id', $plugin_id);
        $statement->bindValue(':course_id', $course_id);
        $statement->execute();
        $activated = $statement->fetchColumn();

        if (!$activated) {
            return false;
        }

        $plugin = new UniZensusPlugin();
        $plugin->setId($course_id);
        return $plugin;
    }
}
