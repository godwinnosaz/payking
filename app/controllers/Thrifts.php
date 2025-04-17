<?php


class Thrifts extends Controller
{

  protected $thriftModel;
  protected $userModel;
  protected $serverKey;
  public function __construct()
  {

    $this->thriftModel = $this->model("Thrift");

    $this->userModel = $this->model("User");

    $this->serverKey  = 'secret_server_key' . date("H");
  }


  public function crowdfunding()
  {
    //   print_r(json_encode("$data"));exit;
    try {
      $userData = $this->RouteProtecion();
    } catch (UnexpectedValueException $e) {
      $res = [
        'status' => 401,
        'message' =>  $e->getMessage(),
      ];
      print_r(json_encode($res));
      exit;
    } catch (DomainException $e) {
      $res = [
        'status' => 401,
        'message' =>  $e->getMessage(),
      ];
      http_response_code(401);
      print_r(json_encode($res));
      exit;
    }


    $sentData = $this->getData();
   $user = $this->userModel->findUserByEmail_det2($userData->email);
    $data = array(

      "campaign_name" => trim($sentData["campaign_name"]),
      "category" => trim($sentData["category"]),
      "amount" => trim($sentData["amount"]),
      "desc" => trim($sentData["desc"]),
      "image" => $_FILES["image"],
      "email" => $userData->email,
      "user_id" =>  $userData->user_id,
      "campaign_id" =>  $this->generateUniqueId(),
      "user_name" => $user->fullname


    );
    
//  print_r(json_encode($data));exit;

    foreach ($data as $key => $value) {
      if (is_string($value) && $value === "") {
        $res = json_encode(array(
          "status" => false,
          "message" => "Incomplete params: " . $key . " is required."
        ));
http_response_code(404);
        print_r($res);
        exit;
      }
    }

    if (!filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
        http_response_code(404);
      print_r(json_encode(array(
        "status" => false,
        "message" => "Invalid email."
      )));
      exit;
    }

    if (!$this->userModel->findUserByEmail1($data['email'])) {
    http_response_code(404);
      
        print_r(json_encode(array(
        "status" => false,
        "message" => "User Not registered."
      )));
      exit;
    }
          $new_image_names = [];

      $extensions = ["jpeg", "png", "jpg"];
      $types = ["image/jpeg", "image/jpg", "image/png"];

      $image_fields = ['image'];

      foreach ($image_fields as $image_field) {
          if (isset($data[$image_field])) {
              $img_name = $data[$image_field]['name'];
              $img_type = $data[$image_field]['type'];
              $tmp_name = $data[$image_field]['tmp_name'];
              $img_explode = explode('.', $img_name);
              $img_ext = end($img_explode);

              if (in_array($img_ext, $extensions) === true) {
                  if (in_array($img_type, $types) === true) {
                      $time = time();
                      $new_img_name = $time . "_" . $img_name;
                      if (move_uploaded_file($tmp_name,  "assets/img/attachment/" . $new_img_name)) {
                          $new_image_names[$image_field] = (string)(URLROOT . "/assets/img/attachment/" . $new_img_name); 
                      } else {
                          $response = array(
                              'status' => 'false',
                              'message' => "Upload failed for $image_field",
                          );
                          http_response_code(404);
                          print_r(json_encode($response));
                          exit;
                      }
                  } else {
                      $response = array(
                          'status' => 'false',
                          'message' => "Invalid file type for $image_field. Allowed types are: " . implode(', ', $types),
                      );
                      http_response_code(404);
                      print_r(json_encode($response));
                      exit;
                  }
              } else {
                  $response = array(
                      'status' => 'false',
                      'message' => "Invalid file extension for $image_field. Allowed extensions are: " . implode(', ', $extensions),
                  );
                  http_response_code(404);
                  print_r(json_encode($response));
                  exit;
              }
          } else {
              $response = array(
                  'status' => 'false',
                  'message' => "$image_field not set",
              );
              http_response_code(404);
              print_r(json_encode($response));
              exit;
          }
      }

      foreach ($new_image_names as $key => $value) {
          $data[$key] = $value;
      }
     
   if($this->userModel->saveCrowdFunding($data)){
       
     print_r(json_encode(array(
        "status" => true,
        "message" => "crowd funding initiated."
      )));
      exit;
   
    }else{
         $res = json_encode(array(
          'status' => false,
          'message' => 'wrong approach fam'
        ));http_response_code(404);
        print_r($res);
        exit;
    }
  }
  
  
  
  
  public function createTicket()
  {
    try {
      $userData = $this->RouteProtecion();
    } catch (UnexpectedValueException $e) {
      $res = [
        'status' => 401,
        'message' =>  $e->getMessage(),
      ];
      print_r(json_encode($res));
      exit;
    } catch (DomainException $e) {
      $res = [
        'status' => 401,
        'message' =>  $e->getMessage(),
      ];
      print_r(json_encode($res));
      exit;
    }


    $sentData = $this->getData();

    $data = array(

      "event_name" => trim($sentData["event_name"]),
      "price" => trim($sentData["price"]),
      "no_of_ticket" => trim($sentData["no_of_ticket"]),
      "time" => trim($sentData["time"]),
      "date" => trim($sentData["date"]),
      "address" => trim($sentData["address"]),
      "state" => trim($sentData["state"]),
      "username" => trim($sentData["username"]),
      "desc" => trim($sentData["desc"]),
      "image" => $_FILES["image"],
      "email" => $userData->email,
      "user_id" =>  $userData->user_id,
      "event_id" =>  $this->generateUniqueId(),


    ); 
    
 

    foreach ($data as $key => $value) {
      if (is_string($value) && $value === "") {
        $res = json_encode(array(
          "status" => false,
          "message" => "Incomplete params: " . $key . " is required."
        ));
        http_response_code(404);
        print_r($res);
        exit;
      }
    }

    if (!filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
        http_response_code(404);
      print_r(json_encode(array(
        "status" => false,
        "message" => "Invalid email."
      )));
 
      exit;
    }

    if (!$this->userModel->findUserByEmail1($data['email'])) {
        http_response_code(404);
      print_r(json_encode(array(
        "status" => false,
        "message" => "User Not registered."
      )));
     
      exit;
    }
      $new_image_names = [];

      $extensions = ["jpeg", "png", "jpg"];
      $types = ["image/jpeg", "image/jpg", "image/png"];

      $image_fields = ['image'];

      foreach ($image_fields as $image_field) {
          if (isset($data[$image_field])) {
              $img_name = $data[$image_field]['name'];
              $img_type = $data[$image_field]['type'];
              $tmp_name = $data[$image_field]['tmp_name'];
              $img_explode = explode('.', $img_name);
              $img_ext = end($img_explode);

              if (in_array($img_ext, $extensions) === true) {
                  if (in_array($img_type, $types) === true) {
                      $time = time();
                      $new_img_name = $time . "_" . $img_name;
                      if (move_uploaded_file($tmp_name,  "assets/img/attachment/" . $new_img_name)) {
                          $new_image_names[$image_field] = (string)(URLROOT . "/assets/img/attachment/" . $new_img_name); 
                      } else {
                          $response = array(
                              'status' => 'false',
                              'message' => "Upload failed for $image_field",
                          );
                          http_response_code(404);
                          print_r(json_encode($response));
                          exit;
                      }
                  } else {
                      $response = array(
                          'status' => 'false',
                          'message' => "Invalid file type for $image_field. Allowed types are: " . implode(', ', $types),
                      );
                      http_response_code(404);
                      print_r(json_encode($response));
                      exit;
                  }
              } else {
                  $response = array(
                      'status' => 'false',
                      'message' => "Invalid file extension for $image_field. Allowed extensions are: " . implode(', ', $extensions),
                  );
                  http_response_code(404);
                  print_r(json_encode($response));
                  exit;
              }
          } else {
              $response = array(
                  'status' => 'false',
                  'message' => "$image_field not set",
              );
              http_response_code(404);
              print_r(json_encode($response));
              exit;
          }
      }

      foreach ($new_image_names as $key => $value) {
          $data[$key] = $value;
      }
      if($this->userModel->saveticket($data)){
       
     print_r(json_encode(array(
        "status" => true,
        "message" => "Ticket Created."
      )));
      exit;
   
    }else{
         $res = json_encode(array(
          'status' => false,
          'message' => 'failed to create a ticket'
        ));http_response_code(404);
        print_r($res);
        exit;
    }
   
  }
  
  
  public function createThrift()
  {
    try {
      $userData = $this->RouteProtecion();
    } catch (UnexpectedValueException $e) {
      $res = [
        'status' => 401,
        'message' =>  $e->getMessage(),
      ];
      print_r(json_encode($res));
      exit;
    } catch (DomainException $e) {
      $res = [
        'status' => 401,
        'message' =>  $e->getMessage(),
      ];
      print_r(json_encode($res));
      exit;
    }


    $sentData = $this->getData();

    $data = array(

      "thrift_name" => trim($sentData["thrift_name"]),
      "thrift_type" => trim($sentData["thrift_type"]),
      "interval" => trim($sentData["interval"]),
      "amount" => trim($sentData["amount"]),
      "collection_time" => "23:45",
      "duration" => trim($sentData["duration"]),
      "email" => $userData->email,
      "user_id" =>  $userData->user_id,
      "thrift_id" =>  $this->generateUniqueId(),


    );
    
 

    foreach ($data as $key => $value) {
      if (is_string($value) && $value === "") {
        $res = json_encode(array(
          "status" => false,
          "message" => "Incomplete params: " . $key . " is required."
        ));

        print_r($res);
        exit;
      }
    }

    if (!filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
      print_r(json_encode(array(
        "status" => false,
        "message" => "Invalid email."
      )));
      exit;
    }

    if (!$this->userModel->findUserByEmail1($data['email'])) {
      print_r(json_encode(array(
        "status" => false,
        "message" => "User Not registered."
      )));
      exit;
    }
    if ($data['interval'] === 'daily') {
      if ($this->thriftModel->createDailyThrift($data)) {
        $res = json_encode(array(
          'status' => true,
          'message' => 'successful'
        ));
        print_r($res);
        exit;
      } else {

        $res = json_encode(array(
          'status' => false,
          'message' => 'not able to create thrift'
        ));
        print_r($res);
        exit;
      }
    } elseif ($data['interval'] === 'weekly') {
      if ($this->thriftModel->createWeeklyThrift($data)) {
        $res = json_encode(array(
          'status' => true,
          'message' => 'successful'
        ));
        print_r($res);
        exit;
      } else {

        $res = json_encode(array(
          'status' => false,
          'message' => 'not able to create thrift'
        ));
        print_r($res);
        exit;
      }
    } elseif ($data['interval'] === 'monthly') {
      if ($this->thriftModel->createMonthlyThrift($data)) {
        $res = json_encode(array(
          'status' => true,
          'message' => 'successful'
        ));
        print_r($res);
        exit;
      } else {

        $res = json_encode(array(
          'status' => false,
          'message' => 'not able to create thrift'
        ));
        print_r($res);
        exit;
      }
    }else{
         $res = json_encode(array(
          'status' => false,
          'message' => 'wrong approach fam'
        ));
        print_r($res);
        exit;
    }
  }
  public function createOsusu()
  {
    try {
      $userData = $this->RouteProtecion();
    } catch (UnexpectedValueException $e) {
      $res = [
        'status' => 401,
        'message' =>  $e->getMessage(),
      ];
      print_r(json_encode($res));
      exit;
    } catch (DomainException $e) {
      $res = [
        'status' => 401,
        'message' =>  $e->getMessage(),
      ];
      print_r(json_encode($res));
      exit;
    }


    $sentData = $this->getData();

    $data = array(

      "osusu_name" => trim($sentData["osusu_name"]),
      "osusu_type" => "fixed",
      "interval" => trim($sentData["interval"]),
      "amount" => trim($sentData["amount"]),
      "collection_time" => "23:45",
      "duration" => trim($sentData["duration"]),
      "email" => $userData->email,
      "user_id" =>  $userData->user_id,
      "osusu_id" =>  $this->generateUniqueId(),


    );

    foreach ($data as $key => $value) {
      if (is_string($value) && $value === "") {
        $res = json_encode(array(
          "status" => false,
          "message" => "Incomplete params: " . $key . " is required."
        ));

        print_r($res);
        exit;
      }
    }

    if (!filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
      print_r(json_encode(array(
        "status" => false,
        "message" => "Invalid email."
      )));
      exit;
    }

    $data['player1'] = isset($sentData['player1']) && !empty($sentData['player1']) ? $sentData['player1'] : '0';
    $data['player2'] = isset($sentData['player2']) && !empty($sentData['player2']) ? $sentData['player2'] : '0';
    $data['player3'] = isset($sentData['player3']) && !empty($sentData['player3']) ? $sentData['player3'] : '0';
    $data['player4'] = isset($sentData['player4']) && !empty($sentData['player4']) ? $sentData['player4'] : '0';
    $data['player5'] = isset($sentData['player5']) && !empty($sentData['player5']) ? $sentData['player5'] : '0';

    // print_r(json_encode($data));exit;

    // Check if all product images are empty after the assignment
    if ($data['player1'] === '0' && $data['player2'] === '0' && $data['player3'] === '0' && $data['player4'] === '0' && $data['player5'] === '0') {
      print_r(json_encode(array(
        "status" => false,
        "message" => "There must be at least one person in this group"
      )));
      exit;
    }


    if (!$this->userModel->findUserByEmail1($data['email'])) {
      print_r(json_encode(array(
        "status" => false,
        "message" => "User Not registered."
      )));
      exit;
    }
    if ($data['interval'] === 'daily') {
      if ($this->thriftModel->createDailyOsusu($data) && $this->thriftModel->createOsusu($data)) {
        $res = json_encode(array(
          'status' => true,
          'message' => 'successful'
        ));
        print_r($res);
        exit;
      } else {

        $res = json_encode(array(
          'status' => false,
          'message' => 'not able to create Osusu'
        ));
        print_r($res);
        exit;
      }
    } elseif ($data['interval'] === 'weekly') {
      if ($this->thriftModel->createWeeklyOsusu($data) && $this->thriftModel->createOsusu($data)) {
        $res = json_encode(array(
          'status' => true,
          'message' => 'successful'
        ));
        print_r($res);
        exit;
      } else {

        $res = json_encode(array(
          'status' => false,
          'message' => 'not able to create Osusu'
        ));
        print_r($res);
        exit;
      }
    } elseif ($data['interval'] === 'monthly') {
      if ($this->thriftModel->createMonthlyOsusu($data) && $this->thriftModel->createOsusu($data)) {
        $res = json_encode(array(
          'status' => true,
          'message' => 'successful'
        ));
        print_r($res);
        exit;
      } else {

        $res = json_encode(array(
          'status' => false,
          'message' => 'not able to create Osusu'
        ));
        print_r($res);
        exit;
      }
    }
  }
  public function editThrift()
  {
    try {
      $userData = $this->RouteProtecion();
    } catch (UnexpectedValueException $e) {
      $res = [
        'status' => 401,
        'message' =>  $e->getMessage(),
      ];
      print_r(json_encode($res));
      exit;
    } catch (DomainException $e) {
      $res = [
        'status' => 401,
        'message' =>  $e->getMessage(),
      ];
      print_r(json_encode($res));
      exit;
    }


    $sentData = $this->getData();

    $data = [

      "thrift_name" => trim($sentData["thrift_name"]),
      "thrift_type" => trim($sentData["thrift_type"]),
      "interval" => trim($sentData["interval"]),
      "amount" => trim($sentData["amount"]),
      "collection_time" => trim($sentData["collection_time"]),
      "duration" => trim($sentData["duration"]),
      "email" => $userData->email,
      "user_id" => $userData->user_id,
      "thrift_id" => trim($sentData["thrift_id"]),


    ];

    foreach ($data as $key => $value) {
      if (is_string($value) && $value === "") {
        $res = json_encode(array(
          "status" => false,
          "message" => "Incomplete params: " . $key . " is required."
        ));

        print_r($res);
        exit;
      }
    }

    if (!filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
      print_r(json_encode(array(
        "status" => false,
        "message" => "Invalid email."
      )));
      exit;
    }

    if (!$this->userModel->findUserByEmail1($data['email'])) {
      print_r(json_encode(array(
        "status" => false,
        "message" => "User Not registered."
      )));
      exit;
    }
    if ($this->thriftModel->editThrift($data)) {
      $res = json_encode(array(
        'status' => true,
        'message' => 'successfully edited'
      ));
      print_r($res);
      exit;
    } else {

      $res = json_encode(array(
        'status' => false,
        'message' => 'not able to edit thrift'
      ));
      print_r($res);
      exit;
    }
  }


  public function activateThrift()
  {

    try {
      $userData = $this->RouteProtecion();
    } catch (UnexpectedValueException $e) {
      $res = [
        'status' => 401,
        'message' =>  $e->getMessage(),
      ];
      print_r(json_encode($res));
      exit;
    } catch (DomainException $e) {
      $res = [
        'status' => 401,
        'message' =>  $e->getMessage(),
      ];
      print_r(json_encode($res));
      exit;
    }
    $sentData = $this->getData();
    $data = [
      "email" => $userData->email,
      "user_id" => $userData->user_id,
      "thrift_id" => trim($sentData["thrift_id"]),
    ];

    if ($this->thriftModel->activateThrift($data)) {
      $response = json_encode(array(
        'status' => true,
        'message' => 'successfully activated'

      ));
      $this->handleResponseWithData($response);
    } else {
      $response = json_encode(array(
        'status' => false,
        'data' => []
      ));
      $this->handleResponse($response);
    }
  }
  public function deductThrift()
  {

    $allThrifts = $this->thriftModel->getAllThrifts();


    foreach ($allThrifts as $thrifts) {
    }
  }
  public function deactivateThrift()
  {

    try {
      $userData = $this->RouteProtecion();
    } catch (UnexpectedValueException $e) {
      $res = [
        'status' => 401,
        'message' =>  $e->getMessage(),
      ];
      print_r(json_encode($res));
      exit;
    } catch (DomainException $e) {
      $res = [
        'status' => 401,
        'message' =>  $e->getMessage(),
      ];
      print_r(json_encode($res));
      exit;
    }
    $sentData = $this->getData();
    $data = [
      "email" => $userData->email,
      "user_id" => $userData->user_id,
      "thrift_id" => trim($sentData["thrift_id"]),
    ];

    if ($this->thriftModel->deactivateThrift($data)) {
      $response = json_encode(array(
        'status' => true,
        'message' => 'successfully deactivated'

      ));
      $this->handleResponseWithData($response);
    } else {
      $response = json_encode(array(
        'status' => false,
        'data' => []
      ));
      $this->handleResponse($response);
    }
  }
  public function getAllThrift()
  {

    try {
      $userData = $this->RouteProtecion();
    } catch (UnexpectedValueException $e) {
      $res = [
        'status' => 401,
        'message' =>  $e->getMessage(),
      ];
      http_response_code(404);
      print_r(json_encode($res));
      exit;
    } catch (DomainException $e) {
      $res = [
        'status' => 401,
        'message' =>  $e->getMessage(),
      ];
      http_response_code(404);
      print_r(json_encode($res));
      exit;
    }

    $res = $this->thriftModel->getAllThrift($userData->user_id);

    if ($res) {
      $response = json_encode(array(
        'status' => true,
        'message' => 'success',
        'data' => $res
      ));
      print_r(($response));
      exit;
    } else {
      $response = json_encode(array(
        'status' => false,
        'data' => []
      ));
      http_response_code(200);
      print_r(($response));
      exit;
    }
  }


  public function getThriftDetails()
  {

    try {
      $userData = $this->RouteProtecion();
    } catch (UnexpectedValueException $e) {
      $res = [
        'status' => 401,
        'message' =>  $e->getMessage(),
      ];
      http_response_code(404);
      print_r(json_encode($res));
      exit;
    } catch (DomainException $e) {
      $res = [
        'status' => 401,
        'message' =>  $e->getMessage(),
      ];
      http_response_code(404);
      print_r(json_encode($res));
      exit;
    }
    $sentData = $this->getData();

    $thrift_id = $sentData['thrift_id'];

    $res = $this->thriftModel->getThriftDetails($userData->user_id, $thrift_id);

    if ($res) {
        
        // if($res->player1 != 0){
        //     $player1 = $this->userModel->getUser($res->player1);
        // }
        // if($res->player2 != 0){
        //     $player2 = $this->userModel->getUser($res->player2);
        // }
        // if($res->player3 != 0){
        //     $player3 = $this->userModel->getUser($res->player3);
        // }
        // if($res->player4 != 0){
        //     $player4 = $this->userModel->getUser($res->player4);
        // }
        // if($res->player5 != 0){
        //     $player5 = $this->userModel->getUser($res->player5);
        // }
        
        // $players = [
        //     'player1' => $player1,
        //     'player2' => $player2,
        //     'player3' => $player3,
        //     'player4' => $player4,
        //     'player5' => $player5,
        //     ];
      $response = json_encode(array(
        'status' => true,
        'message' => 'success',
        'data' => $res,
        // 'players' => $players
      ));
    //   http_response_code(404);
     print_r(($response));
      exit;
    } else {
      $response = json_encode(array(
        'status' => false,
        'data' => []
      ));
      http_response_code(200);
      print_r(($response));
      exit;
    }
  }
 public function getAllOsusu()
{
    try {
        $userData = $this->RouteProtecion();
    } catch (UnexpectedValueException | DomainException $e) {
        $res = [
            'status' => 401,
            'message' => $e->getMessage(),
        ];
        http_response_code(401);
        echo json_encode($res);
        exit;
    }

    $osusuList = $this->thriftModel->getAllOsusu($userData->user_id);
    $result = [];

    foreach ($osusuList as $osusu) {
        $players = [];
        for ($i = 1; $i <= 5; $i++) {
            $playerKey = "player$i";
            if (!empty($osusu->$playerKey) && $osusu->$playerKey != 0) {
                $players[$playerKey] = $this->userModel->getUser($osusu->$playerKey);
            } else {
                $players[$playerKey] = null; // Player slot is empty
            }
        }

        $result[] = [
            'osusu' => $osusu,
            'players' => $players,
        ];
    }

    if ($result) {
        $response = [
            'status' => true,
            'message' => 'success',
            'data' => $result,
        ];
        echo json_encode($response);
    } else {
        $response = [
            'status' => false,
            'message' => 'No data found',
            'data' => [],
        ];
        http_response_code(200);
        echo json_encode($response);
    }
    exit;
}


 public function getAllCampaign()
{
    try {
        $userData = $this->RouteProtecion();
    } catch (UnexpectedValueException | DomainException $e) {
        $res = [
            'status' => 401,
            'message' => $e->getMessage(),
        ];
        http_response_code(401);
        echo json_encode($res);
        exit;
    }

    $result = $this->thriftModel->getAllCampaign();

    if ($result) {
        $response = [
            'status' => true,
            'message' => 'success',
            'data' => $result,
        ];
        echo json_encode($response);
    } else {
        $response = [
            'status' => false,
            'message' => 'No data found',
            'data' => [],
        ];
        http_response_code(200);
        echo json_encode($response);
    }
    exit;
}
 public function getAllTicket()
{
    try {
        $userData = $this->RouteProtecion();
    } catch (UnexpectedValueException | DomainException $e) {
        $res = [
            'status' => 401,
            'message' => $e->getMessage(),
        ];
        http_response_code(401);
        echo json_encode($res);
        exit;
    }

    $result = $this->thriftModel->getAllTicket();

    if ($result) {
        $response = [
            'status' => true,
            'message' => 'success',
            'data' => $result,
        ];
        echo json_encode($response);
    } else {
        $response = [
            'status' => false,
            'message' => 'No data found',
            'data' => [],
        ];
        http_response_code(200);
        echo json_encode($response);
    }
    exit;
}
 public function getAllMyCampaign()
{
    try {
        $userData = $this->RouteProtecion();
    } catch (UnexpectedValueException | DomainException $e) {
        $res = [
            'status' => 401,
            'message' => $e->getMessage(),
        ];
        http_response_code(401);
        echo json_encode($res);
        exit;
    }

    $result = $this->thriftModel->getAllMyCampaign($userData->user_id);

    if ($result) {
        $response = [
            'status' => true,
            'message' => 'success',
            'data' => $result,
        ];
        echo json_encode($response);
    } else {
        $response = [
            'status' => false,
            'message' => 'No data found',
            'data' => [],
        ];
        http_response_code(200);
        echo json_encode($response);
    }
    exit;
}
 public function getAllMyTicket()
{
    try {
        $userData = $this->RouteProtecion();
    } catch (UnexpectedValueException | DomainException $e) {
        $res = [
            'status' => 401,
            'message' => $e->getMessage(),
        ];
        http_response_code(401);
        echo json_encode($res);
        exit;
    }

    $result = $this->thriftModel->getAllMyTicket($userData->user_id);

    if ($result) {
        $response = [
            'status' => true,
            'message' => 'success',
            'data' => $result,
        ];
        echo json_encode($response);
    } else {
        $response = [
            'status' => false,
            'message' => 'No data found',
            'data' => [],
        ];
        http_response_code(200);
        echo json_encode($response);
    }
    exit;
}
 public function getCampaignDetails()
{
    try {
        $userData = $this->RouteProtecion();
    } catch (UnexpectedValueException | DomainException $e) {
        $res = [
            'status' => 401,
            'message' => $e->getMessage(),
        ];
        http_response_code(401);
        echo json_encode($res);
        exit;
    }
 $sentData = $this->getData();

    $thrift_id = $sentData['campaign_id'];
    $result = $this->thriftModel->getCampaignDetails($thrift_id);

    if ($result) {
        $response = [
            'status' => true,
            'message' => 'success',
            'data' => $result,
        ];
        echo json_encode($response);
    } else {
        $response = [
            'status' => false,
            'message' => 'No data found',
            'data' => [],
        ];
        http_response_code(200);
        echo json_encode($response);
    }
    exit;
}
 public function endCampaign()
{
    try {
        $userData = $this->RouteProtecion();
    } catch (UnexpectedValueException | DomainException $e) {
        $res = [
            'status' => 401,
            'message' => $e->getMessage(),
        ];
        http_response_code(401);
        echo json_encode($res);
        exit;
    }
 $sentData = $this->getData();

    $thrift_id = $sentData['campaign_id'];
    $result = $this->thriftModel->endCampaign($thrift_id);

    if ($result) {
        $response = [
            'status' => true,
            'message' => 'campaign ended',
            // 'data' => $result,
        ];
        echo json_encode($response);
    } else {
        $response = [
            'status' => false,
            'message' => 'failed to end campaign',
            // 'data' => [],
        ];
        http_response_code(200);
        echo json_encode($response);
    }
    exit;
}

 public function endTicket()
{
    try {
        $userData = $this->RouteProtecion();
    } catch (UnexpectedValueException | DomainException $e) {
        $res = [
            'status' => 401,
            'message' => $e->getMessage(),
        ];
        http_response_code(401);
        echo json_encode($res);
        exit;
    }
 $sentData = $this->getData();

    $thrift_id = $sentData['event_id'];
    $result = $this->thriftModel->endTicket($thrift_id);

    if ($result) {
        $response = [
            'status' => true,
            'message' => 'ticket ended',
            // 'data' => $result,
        ];
        echo json_encode($response);
    } else {
        $response = [
            'status' => false,
            'message' => 'failed to end campaign',
            // 'data' => [],
        ];
        http_response_code(400);
        echo json_encode($response);
    }
    exit;
}
 public function getTicketDetails()
{
    try {
        $userData = $this->RouteProtecion();
    } catch (UnexpectedValueException | DomainException $e) {
        $res = [
            'status' => 401,
            'message' => $e->getMessage(),
        ];
        http_response_code(401);
        echo json_encode($res);
        exit;
    }
 $sentData = $this->getData();

    $thrift_id = $sentData['event_id'];
    $result = $this->thriftModel->getTicketDetails($thrift_id);

    if ($result) {
        $response = [
            'status' => true,
            'message' => 'success',
            'data' => $result,
        ];
        echo json_encode($response);
    } else {
        $response = [
            'status' => false,
            'message' => 'No data found',
            'data' => [],
        ];
        http_response_code(200);
        echo json_encode($response);
    }
    exit;
}
 public function transferTicket()
{
    try {
        $userData = $this->RouteProtecion();
    } catch (UnexpectedValueException | DomainException $e) {
        $res = [
            'status' => 401,
            'message' => $e->getMessage(),
        ];
        http_response_code(401);
        echo json_encode($res);
        exit;
    }
 $sentData = $this->getData();

    $thrift_id = $sentData['event_id'];
    $tr = $sentData['user'];
    
    $user = $this->userModel->getUserByid($tr);
    

    if ($this->thriftModel->transferTicket($thrift_id, $tr, $user->email, $userData->user_id)) {
        $response = [
            'status' => true,
            'message' => 'success',
            // 'data' => $result,
        ];
        echo json_encode($response);
    } else {
        $response = [
            'status' => false,
            'message' => 'transfer failed',
            // 'data' => [],
        ];
        http_response_code(200);
        echo json_encode($response);
    }
    exit;
}



  public function getOsusuDetails()
  {

    try {
      $userData = $this->RouteProtecion();
    } catch (UnexpectedValueException $e) {
      $res = [
        'status' => 401,
        'message' =>  $e->getMessage(),
      ];
      http_response_code(404);
      print_r(json_encode($res));
      exit;
    } catch (DomainException $e) {
      $res = [
        'status' => 401,
        'message' =>  $e->getMessage(),
      ];
      http_response_code(404);
      print_r(json_encode($res));
      exit;
    }
    $sentData = $this->getData();

    $thrift_id = $sentData['osusu_id'];

    $res = $this->thriftModel->getOsusuDetails($userData->user_id, $thrift_id);

    if ($res) {
            if($res->player1 != 0){
            $player1 = $this->userModel->getUser($res->player1);
        }
        if($res->player2 != 0){
            $player2 = $this->userModel->getUser($res->player2);
        }
        if($res->player3 != 0){
            $player3 = $this->userModel->getUser($res->player3);
        }
        if($res->player4 != 0){
            $player4 = $this->userModel->getUser($res->player4);
        }
        if($res->player5 != 0){
            $player5 = $this->userModel->getUser($res->player5);
        }
        
        $players = [
            'player1' => $player1,
            'player2' => $player2,
            'player3' => $player3,
            'player4' => $player4,
            'player5' => $player5,
            ];
      $response = json_encode(array(
        'status' => true,
        'message' => 'success',
        'data' => $res,
            'players' => $players
      ));
    //   http_response_code(404);
     print_r(($response));
      exit;
    } else {
      $response = json_encode(array(
        'status' => false,
        'data' => []
      ));
      http_response_code(200);
      print_r(($response));
      exit;
    }
  }

  public function thriftPay()
  {
    $dailythrifts = $this->thriftModel->getDailyThriftRecords();
    $weeklythrifts = $this->thriftModel->getWeeklyThriftRecords();
    $monthlythrifts = $this->thriftModel->getMonthlyThriftRecords();
    $currentTime = date('H:i');
    $lastDayOfMonth = date('Y-m-t');
    $currentDate = date('Y-m-d');
    foreach ($dailythrifts as $dailythrifts_item) {
      $daily = [
        "thrift_type" => ($dailythrifts_item->thrift_type),
        "amount" => ($dailythrifts_item->amount),
        "duration" => ($dailythrifts_item->duration),
        "email" => $dailythrifts_item->email,
        "user_id" => $dailythrifts_item->user_id,
        "thrift_id" => ($dailythrifts_item->thrift_id),

      ];
      if ($currentTime >= '23:45') {
        if ($dailythrifts_item->thrift_type === "fixed") {
          $this->thriftModel->payDailyThriftFixed($daily);
        } elseif ($dailythrifts_item->thrift_type === "liberal") {
          $this->thriftModel->payDailyThriftLiberal($daily);
        }
      }
    }
    foreach ($weeklythrifts as $weeklythrifts_item) {
      $weekly = [
        "thrift_type" => ($weeklythrifts_item->thrift_type),
        "amount" => ($weeklythrifts_item->amount),
        "duration" => ($weeklythrifts_item->duration),
        "email" => $weeklythrifts_item->email,
        "user_id" => $weeklythrifts_item->user_id,
        "thrift_id" => ($weeklythrifts_item->thrift_id),

      ];
      if (date('l', strtotime($currentDate)) === 'Sunday' && $currentTime >= '23:45') {
        if ($weeklythrifts_item->thrift_type === "fixed") {
          $this->thriftModel->payWeeklyThriftFixed($weekly);
        } elseif ($weeklythrifts_item->thrift_type === "liberal") {
          $this->thriftModel->payWeeklyThriftLiberal($weekly);
        }
      }
    }
    foreach ($monthlythrifts as $monthlythrifts_item) {
      $monthly = [
        "thrift_type" => ($monthlythrifts_item->thrift_type),
        "amount" => ($monthlythrifts_item->amount),
        "duration" => ($monthlythrifts_item->duration),
        "email" => $monthlythrifts_item->email,
        "user_id" => $monthlythrifts_item->user_id,
        "thrift_id" => ($monthlythrifts_item->thrift_id),

      ];
      if ($currentDate === $lastDayOfMonth && $currentTime >= '23:45') {
        if ($monthlythrifts_item->thrift_type === "fixed") {
          $this->thriftModel->payMonthlyThriftFixed($monthly);
        } elseif ($monthlythrifts_item->thrift_type === "liberal") {
          $this->thriftModel->payMonthlyThriftLiberal($monthly);
        }
      }
    }
  }
  public function osusuPay()
  {
    $dailyosusu = $this->thriftModel->getDailyOsusuRecords();
    $weeklyosusu = $this->thriftModel->getWeeklyOsusuRecords();
    $monthlyosusu = $this->thriftModel->getMonthlyOsusuRecords();
    $currentTime = date('H:i');
    $lastDayOfMonth = date('Y-m-t');
    $currentDate = date('Y-m-d');
    foreach ($dailyosusu as $dailyosusu_item) {
      $daily = [
        "osusu_type" => ($dailyosusu_item->osusu_type),
        "amount" => ($dailyosusu_item->amount),
        "duration" => ($dailyosusu_item->duration),
        "email" => $dailyosusu_item->email,
        "user_id" => $dailyosusu_item->user_id,
        "osusu_id" => ($dailyosusu_item->osusu_id),

      ];
      $data['player1'] = isset($dailyosusu_item->player1) && !empty($dailyosusu_item->player1) ? $dailyosusu_item->player1 : '0';
      $data['player2'] = isset($dailyosusu_item->player2) && !empty($dailyosusu_item->player2) ? $dailyosusu_item->player2 : '0';
      $data['player3'] = isset($dailyosusu_item->player3) && !empty($dailyosusu_item->player3) ? $dailyosusu_item->player3 : '0';
      $data['player4'] = isset($dailyosusu_item->player4) && !empty($dailyosusu_item->player4) ? $dailyosusu_item->player4 : '0';
      $data['player5'] = isset($dailyosusu_item->player5) && !empty($dailyosusu_item->player5) ? $dailyosusu_item->player5 : '0';
      if ($currentTime >= '23:45') {
        $validPlayers = array_filter($data, function ($player) {
          return $player !== '0';
      });
      $shuffledPlayers = array_values($validPlayers);
      shuffle($shuffledPlayers);
      if (!isset($_SESSION)) {
          session_start();
      }
      $usedPlayers = $_SESSION['usedPlayers'] ?? [];
      $nextPlayer = null;
      foreach ($shuffledPlayers as $player) {
          if (!in_array($player, $usedPlayers)) {
              $nextPlayer = $player;
              $usedPlayers[] = $player;
              break;
          }
      }
      if ($nextPlayer === null) {
          // $usedPlayers = [];
            // $nextPlayer = $shuffledPlayers[0];
            // $usedPlayers[] = $nextPlayer;
            exit;
      }
      $_SESSION['usedPlayers'] = $usedPlayers;
      
        $this->thriftModel->payDailyOsusuFixed($daily, $validPlayers, $nextPlayer);
      }
    }
    foreach ($weeklyosusu as $weeklyosusu_item) {
      $weekly = [
        "osusu_type" => ($weeklyosusu_item->osusu_type),
        "amount" => ($weeklyosusu_item->amount),
        "duration" => ($weeklyosusu_item->duration),
        "email" => $weeklyosusu_item->email,
        "user_id" => $weeklyosusu_item->user_id,
        "osusu_id" => ($weeklyosusu_item->osusu_id),

      ];
      
        $data['player1'] = isset($dailyosusu_item->player1) && !empty($dailyosusu_item->player1) ? $dailyosusu_item->player1 : '0';
        $data['player2'] = isset($dailyosusu_item->player2) && !empty($dailyosusu_item->player2) ? $dailyosusu_item->player2 : '0';
        $data['player3'] = isset($dailyosusu_item->player3) && !empty($dailyosusu_item->player3) ? $dailyosusu_item->player3 : '0';
        $data['player4'] = isset($dailyosusu_item->player4) && !empty($dailyosusu_item->player4) ? $dailyosusu_item->player4 : '0';
        $data['player5'] = isset($dailyosusu_item->player5) && !empty($dailyosusu_item->player5) ? $dailyosusu_item->player5 : '0';
        if (date('l', strtotime($currentDate)) === 'Sunday' && $currentTime >= '23:45') {
          $validPlayers = array_filter($data, function ($player) {
            return $player !== '0';
        });
        $shuffledPlayers = array_values($validPlayers);
        shuffle($shuffledPlayers);
        if (!isset($_SESSION)) {
            session_start();
        }
        $usedPlayers = $_SESSION['usedPlayers'] ?? [];
        $nextPlayer = null;
        foreach ($shuffledPlayers as $player) {
            if (!in_array($player, $usedPlayers)) {
                $nextPlayer = $player;
                $usedPlayers[] = $player;
                break;
            }
        }
        if ($nextPlayer === null) {
             // $usedPlayers = [];
            // $nextPlayer = $shuffledPlayers[0];
            // $usedPlayers[] = $nextPlayer;
            exit;
        }
        $_SESSION['usedPlayers'] = $usedPlayers;
        
        $this->thriftModel->payWeeklyOsusuFixed($weekly, $validPlayers, $nextPlayer);
      }
    }
    foreach ($monthlyosusu as $monthlyosusu_item) {
      $monthly = [
        "thrift_type" => ($monthlyosusu_item->thrift_type),
        "amount" => ($monthlyosusu_item->amount),
        "duration" => ($monthlyosusu_item->duration),
        "email" => $monthlyosusu_item->email,
        "user_id" => $monthlyosusu_item->user_id,
        "thrift_id" => ($monthlyosusu_item->thrift_id),

      ];

        $data['player1'] = isset($dailyosusu_item->player1) && !empty($dailyosusu_item->player1) ? $dailyosusu_item->player1 : '0';
        $data['player2'] = isset($dailyosusu_item->player2) && !empty($dailyosusu_item->player2) ? $dailyosusu_item->player2 : '0';
        $data['player3'] = isset($dailyosusu_item->player3) && !empty($dailyosusu_item->player3) ? $dailyosusu_item->player3 : '0';
        $data['player4'] = isset($dailyosusu_item->player4) && !empty($dailyosusu_item->player4) ? $dailyosusu_item->player4 : '0';
        $data['player5'] = isset($dailyosusu_item->player5) && !empty($dailyosusu_item->player5) ? $dailyosusu_item->player5 : '0';
        if ($currentDate === $lastDayOfMonth && $currentTime >= '23:45') {
          $validPlayers = array_filter($data, function ($player) {
            return $player !== '0';
        });
        $shuffledPlayers = array_values($validPlayers);
        shuffle($shuffledPlayers);
        if (!isset($_SESSION)) {
            session_start();
        }
        $usedPlayers = $_SESSION['usedPlayers'] ?? [];
        $nextPlayer = null;
        foreach ($shuffledPlayers as $player) {
            if (!in_array($player, $usedPlayers)) {
                $nextPlayer = $player;
                $usedPlayers[] = $player;
                break;
            }
        }
        if ($nextPlayer === null) {
            // $usedPlayers = [];
            // $nextPlayer = $shuffledPlayers[0];
            // $usedPlayers[] = $nextPlayer;
            exit;
        }
        $_SESSION['usedPlayers'] = $usedPlayers;
        
        $this->thriftModel->payMonthlyOsusuFixed($monthly, $validPlayers, $nextPlayer);
      }
    }
  }
}
