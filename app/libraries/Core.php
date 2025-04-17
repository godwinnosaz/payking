<?php
  
  /*
   * App Core Class
   * Creates URL & loads core controller
   * URL FORMAT - /controller/method/params
   */

  class Core {
     protected $currentController = 'Users';
    protected $currentMethod = 'index';
    protected $params = [];

    public function __construct(){

      try {
        $url = $this->getUrl();
        if(file_exists('../app/controllers/'. ucwords($url[0]). '.php')){
          $this->currentController = ucwords($url[0]);
          unset($url[0]);
        }
        require_once '../app/controllers/'. $this->currentController . '.php';
        $this->currentController = new $this->currentController;
  
        if(isset($url[1])){
          //Check to see if method exists in controller
          if(method_exists($this->currentController, $url[1])){
              $this->currentMethod = $url[1];
  
              //Unset
              unset($url[1]);
          }
        }
  
        
          //Get params
          $this->params = $url ? array_values($url) : [];
          call_user_func_array([$this->currentController, $this->currentMethod], $this->params); 
    } catch (Error $e) {
       
        $msg = "Caught error: " . $e->getMessage();
    http_response_code(404);
        $res = json_encode(["status" => 404,"msg"=> $msg]);
        print_r($res);
        
    } catch (Exception $e) {
      $msg = "Caught error: " . $e->getMessage();
      http_response_code(404);

      $res = json_encode(["status" => 404,"msg"=> $msg]);
      print_r($res);
    }
    
        
    }

    public function getUrl(){
      if(isset($_GET['url'])){
        $url = rtrim($_GET['url'], '/');
        $url = filter_var($url, FILTER_SANITIZE_URL);
        $url = explode('/', $url);
        return $url;
      }
    } 
  } 
  
?> 