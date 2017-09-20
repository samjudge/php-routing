<?php

require_once __DIR__ . "/../vendor/autoload.php";

use jamesiarmes\PhpEws\Client;
use Sootlib\WS\Holidays\Holiday_Calendar;
use Sootlib\WS\Holidays\Holiday_Query;
use Sootlib\WS\Structs\WS_Event_Query;

class UrlParameterIterator{

    private $iter;
    private $params;

    public function __construct($params){
        $this->params = $params;
        $this->iter = 0;
    }

    public function get_next_parameter(){
        if($this->iter < count($this->params)) {
            $result = $this->params[$this->iter];
            $this->iter++;
            return $result;
        } else {
            return FALSE;
        }
    }
}

//implement this over every controller class
interface Actionable {
    public function perform_action();
}

class GetHolidaysByEmail implements Actionable{

    private $email;

    public function __construct($email_value) {
        $this->email = $email_value;
    }

    //GET all the holdays for the given user for the next month
    public function perform_action() {
        //controller action...
    }

}

//WORK HERE DEFINE YOUR PATHS (will find a class of the given name at the end)

$url = $_SERVER['REQUEST_URI'];
$base_url = "/shinsekicalendar/HolidayTracker/back/api/";
$valid_url_paths = array(
    "get"=>array(
        "holidays"=>array(
            "for"=>"GetHolidaysByEmail",
            "between"=>"GetHolidaysBetween",
        ),
    ),
);

perform_request($base_url, $url, $valid_url_paths);

//RESOLVE REQUEST + GET VARIABLES
function perform_request($base_url, $url, $paths) {
    $request = str_replace($base_url, "", $url);
    $split_url_path  = explode("/", $request);
    $url_iterator = new UrlParameterIterator($split_url_path);
    $class_name = resolve_request($url_iterator, $paths);
    $args = array();
    while ($arg = $url_iterator->get_next_parameter()) {
        array_push($args, $arg);
    }
    $result = new $class_name(...$args);
    $result->perform_action();
}

function resolve_request($iter, $paths){
    $param = $iter->get_next_parameter();
    if($param != FALSE) {
        foreach ($paths as $k => $v) {
            if ($k == $param) {
                if(is_array($v)) {
                    return resolve_request($iter, $v);
                } else {
                    return $v;
                }
            }
        }
        //no valid path found
        throw new Exception("ERROR: Provded Path To Method Incomplete");
    } else {
        //uri path did not end appropraitely
        throw new Exception("ERROR: Provided Path Does Not Exist");
    }
}