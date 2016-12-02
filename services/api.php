<?php
  require_once("Rest.class.php");
  
  class ForumApi extends REST {
  
    public $data = "";
    
    const DB_SERVER = "localhost";
    const DB_USER = "forum";
    const DB_PASSWORD = "";
    const DB = "forum";

    private $db = NULL;
    private $mysqli = NULL;
    public function __construct(){
      parent::__construct();        // Init parent contructor
      $this->dbConnect();         // Initiate Database connection
    }

    /*
     *  Connect to Database
     */
    private function dbConnect(){
      $this->mysqli = new mysqli(self::DB_SERVER,
                self::DB_USER,
                self::DB_PASSWORD,
                self::DB);
    }

    /*
     * Calls the method based on the query string
     */
    public function processApi(){
      $func = strtolower(trim(str_replace("/","",$_REQUEST['x'])));
      if((int)method_exists($this,$func) > 0)
        $this->$func();
      else
        $this->response('',404);
    }

    // returns json 
    private function json($data){
      if(is_array($data)){
        return json_encode($data);
      }
    }
  }

  // Initiate  
  $api = new API;
  $api->processApi();

?>