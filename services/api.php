<?php
  require_once("Rest.class.php");
  
  class API extends REST {
  
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
    
    
    /*
     *  Get All Posts
     */ 
    private function posts(){
      if($this->get_request_method() != "GET"){
        $this->response('',406);
      }
      $query="SELECT title, img_filename, full_name
              FROM posts 
              JOIN users
              ON posts.user_id = users.id order by posts.id desc";

      $r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);

      if($r->num_rows > 0){
        $result = array();
        while($row = $r->fetch_assoc()){
          $result[] = $row;
        }
        $this->response($this->json($result), 200); // OK 
      }
      $this->response('',204);  // no content 
    }

    /*
     * Get a Post
     */
    private function post(){
      if($this->get_request_method() != "GET"){
        $this->response('',406);
      }
      $id = (int)$this->_request['id'];
      if($id > 0){  
        $query="SELECT title, img_filename, full_name
                FROM posts 
                JOIN users
                ON posts.user_id = users.id  where posts.id=$id";

        $r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
        if($r->num_rows > 0) {
          $result = $r->fetch_assoc();  
          $this->response($this->json($result), 200); // send user details
        }
      }
      $this->response('',204);  // If no records "No Content" status
    }

    /* 
     * Insert a Post
     */ 

    private function insertPost(){
      if($this->get_request_method() != "POST"){
        $this->response('',406);
      }

      $post = json_decode(file_get_contents("php://input"),true);
      $column_names = array('user_id', 'title', 'img_filename');
      $keys = array_keys($post);
      $columns = '';
      $values = '';
      foreach($column_names as $desired_key){ // Check the post received. If blank insert blank into the array.
         if(!in_array($desired_key, $keys)) {
            $$desired_key = '';
        }else{
          $$desired_key = $post[$desired_key];
        }
        $columns = $columns.$desired_key.',';
        $values = $values."'".$$desired_key."',";
      }
      $query = "INSERT INTO posts(".trim($columns,',').") VALUES(".trim($values,',').")";
      if(!empty($post)){
        $r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
        $success = array('status' => "Success", "msg" => "Post Created Successfully.", "data" => $post);
        $this->response($this->json($success),200);
      }else
        $this->response('',204);  //"No Content" status
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