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

namespace local_bird_mdm;

use external_api;
use webservice_base_server;

defined('MOODLE_INTERNAL') || die();


/**
 * BIRD REST service server implementation.
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class server extends webservice_base_server {
    public const EP_BIRD_ACADEMY = 'bird_academy';
    public const EP_BIRD_PROGRAM = 'bird_program';
    public const EP_BIRD_MODULE = 'bird_module';
    public const EP_BIRD_COURSE = 'bird_course';

    public const SUPPORTED_ENDPOINTS = [
        self::EP_BIRD_ACADEMY,
        self::EP_BIRD_PROGRAM,
        self::EP_BIRD_MODULE,
        self::EP_BIRD_COURSE,
    ];

    private $endpoint;


    public function __construct() {
        parent::__construct(WEBSERVICE_AUTHMETHOD_PERMANENT_TOKEN);
        $this->wsname = 'bird';
    }

    /**
     * Process request from client.
     *
     * @uses die
     */
    public function run() {
        raise_memory_limit(MEMORY_EXTRA);
        external_api::set_timeout();
        set_exception_handler(array($this, 'exception_handler'));

        $this->parse_request();

        $this->authenticate_user();

        $this->log_request();
        $this->send_response();

        die;
    }

    /**
     * @return void
     */
    protected function parse_request() {
        $headers = $this->get_headers();
        $this->token = $this->get_wstoken($headers);
        $this->endpoint = $this->get_endpoint();
    }

    /**
     * @return void
     */
    protected function send_response() {
        global $CFG;

        $response = file_get_contents(
            sprintf('%s/local/bird_mdm/resources/%s.xml', $CFG->dirroot, $this->endpoint)
        );

        $this->send_headers();
        echo $response;
    }

    /**
     * Send the error information to the WS client
     * formatted as XML document.
     * Note: the exception is never passed as null,
     *       it only matches the abstract function declaration.
     *
     * @param exception $ex the exception that we are sending.
     * @param integer $code The HTTP response code to return.
     */
    protected function send_error($ex=null, $code=403) {
        http_response_code($code);
        $this->send_headers($code);

        echo $this->generate_error($ex);
    }

    /**
     * Build the error information matching the REST returned value format (XML)
     * @param exception $ex the exception we are converting in the server rest format
     * @return string the error in the requested REST format
     */
    protected function generate_error($ex) {
        $error = '<?xml version="1.0" encoding="UTF-8" ?>'."\n";
        $error .= '<EXCEPTION class="'.get_class($ex).'">'."\n";
        $error .= '<ERRORCODE>' . htmlspecialchars($ex->errorcode, ENT_COMPAT, 'UTF-8')
            . '</ERRORCODE>' . "\n";
        $error .= '<MESSAGE>'.htmlspecialchars($ex->getMessage(), ENT_COMPAT, 'UTF-8').'</MESSAGE>'."\n";
        if (debugging() and isset($ex->debuginfo)) {
            $error .= '<DEBUGINFO>'.htmlspecialchars($ex->debuginfo, ENT_COMPAT, 'UTF-8').'</DEBUGINFO>'."\n";
        }
        $error .= '</EXCEPTION>'."\n";

        return $error;
    }

    /**
     * Internal implementation - sending of page headers.
     *
     * @param integer $code The HTTP response code to return.
     */
    protected function send_headers($code=200) {

        header('Content-Type: application/xml; charset=utf-8');
        header('Content-Disposition: inline; filename="response.xml"');

        header('X-PHP-Response-Code: '.$code, true, $code);
        header('Cache-Control: private, must-revalidate, pre-check=0, post-check=0, max-age=0');
        header('Expires: '. gmdate('D, d M Y H:i:s', 0) .' GMT');
        header('Pragma: no-cache');
        header('Accept-Ranges: none');
        // Allow cross-origin requests only for Web Services.
        // This allow to receive requests done by Web Workers or webapps in different domains.
        header('Access-Control-Allow-Origin: *');
    }

    protected function log_request() {
        $params = ['other' => ['function' => $this->endpoint]];
        $event = \core\event\webservice_function_called::create($params);
        $event->set_legacy_logdata(array(SITEID, 'webservice', $this->endpoint, '' , getremoteaddr() , 0, $this->userid));
        $event->trigger();
    }

    /**
     * Get headers from Apache websever.
     *
     * @return array $returnheaders The headers from Apache.
     */
    private function get_apache_headers() {
        $capitalizearray = array(
            'Content-Type',
            'Accept',
            'Authorization',
            'Content-Length',
            'User-Agent',
            'Host'
        );
        $headers = apache_request_headers();
        $returnheaders = array();

        foreach ($headers as $key => $value) {
            if (in_array($key, $capitalizearray)) {
                $header = 'HTTP_' . strtoupper($key);
                $header = str_replace('-', '_', $header);
                $returnheaders[$header] = $value;
            }
        }

        return $returnheaders;
    }

    /**
     * Extract the HTTP headers out of the request.
     *
     * @param array $headers Optional array of headers, to assist with testing.
     * @return array $headers HTTP headers.
     */
    private function get_headers($headers=null) {
        $returnheaders = array();

        if (!$headers) {
            if (function_exists('apache_request_headers')) {  // Apache websever.
                $headers = $this->get_apache_headers();
            } else {  // Nginx webserver.
                $headers = $_SERVER;
            }
        }

        foreach ($headers as $key => $value) {
            if (substr($key, 0, 5) == 'HTTP_') {
                $returnheaders[$key] = $value;
            }
        }

        return $returnheaders;
    }

    /**
     * @param array $headers The extracted HTTP headers.
     * @return string $wstoken The extracted webservice authorization token.
     */
    private function get_wstoken($headers) {
        if (!isset($headers['HTTP_AUTHORIZATION'])) {
            $ex = new \moodle_exception('noauthheader', 'local_bird_mdm', '');
            $this->send_error($ex, 403);

            die;
        }

        return $headers['HTTP_AUTHORIZATION'];
    }

    private function get_endpoint() {
        $endpoint = '';

        if (isset($_SERVER['PATH_INFO'])) { // Try path info from server super global.
            $endpoint = $_SERVER['PATH_INFO'];
        } else if (isset($_SERVER['REQUEST_URI']) && isset($_SERVER['SCRIPT_NAME'])) {
            $endpoint = substr(
                $_SERVER['REQUEST_URI'],
                strrpos($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME']) + strlen($_SERVER['SCRIPT_NAME']) + 1
            );
        }

        $endpoint = trim($endpoint, '/');

        if (!in_array($endpoint, self::SUPPORTED_ENDPOINTS)) {
            $ex = new \moodle_exception('invalid bird endpoint', 'local_bird_mdm', '', null, $endpoint);
            $this->send_error($ex, 400);

            die;
        }

        return $endpoint;
    }
}
