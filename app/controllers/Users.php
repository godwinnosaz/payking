<?php 


Class Users extends Controller 
{

  public function __construct()
  {
    $this->userModel = $this->model('User');
    $this->serverKey  = 'secret_server_key'.date("H");
  }
  
public function chats() {
    $sentData = $this->getData();

    $userId1 = $sentData['userId1'] ?? null;
    $userId2 = $sentData['userId2'] ?? null;

    if (!$userId1 || !$userId2) {
        http_response_code(400);
        echo json_encode([
            'status' => false,
            'message' => 'Missing user IDs',
        ]);
        return;
    }

    // Ensure both users exist in users table
    $this->userModel->ensureUserExists($userId1);
    $this->userModel->ensureUserExists($userId2);

    // echo "helo"; exit;
    // Get or create chat
    $chat = $this->userModel->getOrCreateChat($userId1, $userId2);

    if ($chat) {
        echo json_encode([
            'status' => true,
            'message' => 'success',
            'data' => $chat
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'status' => false,
            'message' => 'Failed to get or create chat'
        ]);
    }
}


public function storeUsers()
{
    $sentData = $this->getData();

    if (!isset($sentData['users']) || !is_array($sentData['users'])) {
        http_response_code(400);
        echo json_encode([
            'status' => false,
            'message' => 'Invalid or missing users data'
        ]);
        return;
    }

    $result = $this->userModel->saveUsers($sentData['users']);

    if ($result) {
        echo json_encode([
            'status' => true,
            'message' => 'Users saved successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'status' => false,
            'message' => 'Failed to save users'
        ]);
    }
}


public function chatsunread_count() {
    $data = $this->getData();
    $userId = $data['userId'] ?? null;

    if (!$userId) {
        http_response_code(400);
        echo json_encode([
            'status' => false,
            'message' => 'userId is required'
        ]);
        return;
    }

    $count = $this->userModel->getUnreadMessageCount($userId);

    echo json_encode([
        'status' => true,
        'message' => 'Unread count retrieved',
        'count' => $count
    ]);
}


public function messagesread() {
    $data = $this->getData();
    $chatId = $data['chatId'] ?? null;
    $userId = $data['userId'] ?? null;

    if (!$chatId || !$userId) {
        http_response_code(400);
        echo json_encode([
            'status' => false,
            'message' => 'chatId and userId are required'
        ]);
        return;
    }

    $this->userModel->markMessagesAsRead($chatId, $userId);

    echo json_encode([
        'status' => true,
        'message' => 'Messages marked as read'
    ]);
}



public function chatsuser() {
    $data = $this->getData();
    $userId = $data['userId'] ?? null;

    if (!$userId) {
        http_response_code(400);
        echo json_encode([
            'status' => false,
            'message' => 'userId is required'
        ]);
        return;
    }

    $chats = $this->userModel->getChatsByUserId($userId);

    echo json_encode([
        'status' => true,
        'message' => 'Chats retrieved',
        'data' => $chats
    ]);
}




// In User.php controller

public function messages() {
    $data = $this->getData(); // JSON from frontend

    // Call model function to store message
    $success = $this->userModel->savePersonalMessage($data);

    if ($success) {
        echo json_encode([
            'status' => true,
            'message' => 'Message saved successfully'
        ]);
    } else {
        http_response_code(400);
        echo json_encode([
            'status' => false,
            'message' => 'Failed to save message'
        ]);
    }
}

public function messagesget() {
    $data = $this->getData(); // get JSON body
    $chatId = $data['chatId'] ?? null;

    if (!$chatId) {
        http_response_code(400);
        echo json_encode([
            'status' => false,
            'message' => 'chatId is required'
        ]);
        return;
    }

    $messages = $this->userModel->getMessagesByChatId($chatId);

    echo json_encode([
        'status' => true,
        'message' => 'Messages retrieved',
        'data' => $messages
    ]);
}



  
    public function loginfunc()
  {
    session_start(); // Start a session if it's not started

    $jsonData = $this->getData();
    if (!isset($jsonData['email']) || !isset($jsonData['password'])) {
      $response = array(
        'status_code' => 404,
        'status' => 'false',
        'message' => 'Enter login details',
      );

      $this->handleResponse($response);
      exit;
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      $loginData = $this->getData();

      // Init data
      $data = [
        'email' => trim($jsonData['email']),
        'password' => trim($jsonData['password']),
        'email_err' => '',
        'msg' => '',
        'loginStatus' => '',
        'password_err' => '',
      ];

      // Validate Email and Password
      if (empty($data['email'])) $data['email_err'] = 'Please enter email';
      if (empty($data['password'])) $data['password_err'] = 'Please enter password';

      if (empty($data['email_err']) && empty($data['password_err'])) {
        if ($this->userModel->findUserByEmail1($data['email'])) {
          $loginDatax = $this->userModel->loginUser($data['email']);
          $loginStatus = $loginDatax->activationx;
          $postPassword = $data['password'];
          $hash_password = $loginDatax->password;
          $email = $loginDatax->email;
          $user_id = $loginDatax->user_id;

            if ($loginStatus < 1) {
            $response = array(
              'status' => 'false',
              'message' => 'login status disabled',
            );
            http_response_code(404);
            print_r(json_encode($response));
            exit;
          } else if (password_verify($postPassword, $hash_password)) {
            $tokenX = "token" . md5(date("dmyhis") . rand(1222, 89787));
            $this->userModel->updateToken($user_id, $tokenX);

            $loginData = $this->userModel->findLoginByToken($tokenX);
            $userData = $this->userModel->findUserByEmail_det2($loginData->email);
            $accountDetails = $this->userModel->getAccountDetails($userData->account_id, $user_id);
            
             $loginData = (array)$loginData;
        $loginData = array_filter($loginData, function ($value, $key) {
        return $key !== "nin_no" && $key !== "nin_img" && $key !== "bvn_no";
    }, ARRAY_FILTER_USE_BOTH);
        if(!empty($loginData->nin_no)){
        $loginData['ninVerified'] = 1;
    }else{
        $loginData['ninVerified'] = 0;
    }
    
    if(!empty($loginData->nin_img)){
        $loginData['nin_imgVerified'] = 1; 
    }else{
         $loginData['nin_imgVerified'] = 0;
    }
    
    
    if(!empty($loginData->bvn_no)){
        $loginData['bvnVerified'] = 1; 
    }else{
         $loginData['bvnVerified'] = 0;
    }
            
            $initData = [
              'loginData' => $loginData,
              'userAccount' => $accountDetails,
            ];

            $datatoken = [
              'user_id' => $user_id,
              'email' => $email,
              'appToken' => $loginData->token,
            ];
            $JWT_token = $this->getMyJsonID($datatoken, $this->serverKey);

            // Set session cookie with token
            setcookie("session_token", $JWT_token, [
              'expires' => time() + 3600,  // Expires in 1 hour
              'path' => '/',
              'httponly' => true,
              'samesite' => 'Strict',
            ]);

            $response = array(
              'status_code' => 200,
              'status' => true,
              'access_token' => $JWT_token,
              'message' => 'success',
              'data' => $initData,
            );

            $this->handleResponseWithData($response);
            exit;
          } else {
            $response = array(
              'status_code' => 404,
              'status' => 'false',
              'message' => 'Invalid password',
            );

            $this->handleResponse($response);
            exit;
          }
        } else {
          $response = array(
            'status_code' => 404,
            'status' => 'false',
            'message' => 'invalid user login detail',
            'data' => $data,
          );

          $this->handleResponse($response);
          exit;
        }
      } else {
        $response = array(
          'status_code' => 404,
          'status' => 'false',
          'message' => 'All input fields must be complete',
          'data' => $data,
        );

        $this->handleResponse($response);
        exit;
      }
    } else {
      $response = array(
        'status_code' => 404,
        'status' => 'false',
        'message' => 'Invalid server method',
      );

      $this->handleResponse($response);
      exit;
    }
  }
    public function loginfuncLock()
  {
    session_start(); // Start a session if it's not started

    $jsonData = $this->getData();
    if (!isset($jsonData['email']) || !isset($jsonData['code'])) {
      $response = array(
        'status_code' => 404,
        'status' => 'false',
        'message' => 'Enter login details',
      );

      $this->handleResponse($response);
      exit;
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      $loginData = $this->getData();

      // Init data
      $data = [
        'email' => trim($jsonData['email']),
        'password' => trim($jsonData['code']),
        'email_err' => '',
        'msg' => '',
        'loginStatus' => '',
        'password_err' => '',
      ];

      // Validate Email and Password
      if (empty($data['email'])) $data['email_err'] = 'Please enter email';
      if (empty($data['password'])) $data['password_err'] = 'Please enter code';

      if (empty($data['email_err']) && empty($data['password_err'])) {
        if ($this->userModel->findUserByEmail1($data['email'])) {
          $loginDatax = $this->userModel->loginUser($data['email']);
          $loginStatus = $loginDatax->activationx;
          $postPassword = $data['password'];
          $hash_password = $loginDatax->lock_pin;
          $email = $loginDatax->email;
          $user_id = $loginDatax->user_id;

            if ($loginStatus < 1) {
            $response = array(
              'status' => 'false',
              'message' => 'login status disabled',
            );
            http_response_code(404);
            print_r(json_encode($response));
            exit;
          } else if (password_verify($postPassword, $hash_password)) {
            $tokenX = "token" . md5(date("dmyhis") . rand(1222, 89787));
            $this->userModel->updateToken($user_id, $tokenX);

            $loginData = $this->userModel->findLoginByToken($tokenX);
            $userData = $this->userModel->findUserByEmail_det2($loginData->email);
            $accountDetails = $this->userModel->getAccountDetails($userData->account_id, $user_id);
            $initData = [
              'loginData' => $loginData,
              'userAccount' => $accountDetails,
            ];

            $datatoken = [
              'user_id' => $user_id,
              'email' => $email,
              'appToken' => $initData['loginData']->token,
            ];
            $JWT_token = $this->getMyJsonID($datatoken, $this->serverKey);

            // Set session cookie with token
            setcookie("session_token", $JWT_token, [
              'expires' => time() + 3600,  // Expires in 1 hour
              'path' => '/',
              'httponly' => true,
              'samesite' => 'Strict',
            ]);

            $response = array(
              'status_code' => 200,
              'status' => true,
              'access_token' => $JWT_token,
              'message' => 'success',
              'data' => $initData,
            );

            $this->handleResponseWithData($response);
            exit;
          } else {
            $response = array(
              'status_code' => 404,
              'status' => 'false',
              'message' => 'Invalid password',
            );

            $this->handleResponse($response);
            exit;
          }
        } else {
          $response = array(
            'status_code' => 404,
            'status' => 'false',
            'message' => 'invalid user login detail',
            'data' => $data,
          );

          $this->handleResponse($response);
          exit;
        }
      } else {
        $response = array(
          'status_code' => 404,
          'status' => 'false',
          'message' => 'All input fields must be complete',
          'data' => $data,
        );

        $this->handleResponse($response);
        exit;
      }
    } else {
      $response = array(
        'status_code' => 404,
        'status' => 'false',
        'message' => 'Invalid server method',
      );

      $this->handleResponse($response);
      exit;
    }
  }

  public function getUserBalance()
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
    }

    $user_id = $userData->user_id;
    $userDatax = $this->userModel->findUserByEmail_det2($userData->email);
    $accountDetails = $this->userModel->getAccountDetails($userDatax->account_id, $user_id);


    if ($accountDetails) {
      $res = json_encode(array(
        'status' => true,
        'message' => 'success',
        'data' => $accountDetails
      ));
      http_response_code(200);
      print_r($res);
    } else {
      $res = json_encode(array(
        'status' => false,
        'message' => 'faild'
      ));
      http_response_code(404);
      print_r($res);
    }
  }
  
  public function summarize_transactions($transactions) {
    //   $transactions = json_encode($transactions);
    $summary = [
        'total_airtime_amount' => 0,
        'total_airtime_count' => 0,
        'total_data_amount' => 0,
        'total_data_count' => 0,
        'total_electricity_amount' => 0,
        'total_electricity_count' => 0,
        'total_cabletv_amount' => 0,
        'total_cabletv_count' => 0,
    ];

    foreach ($transactions as $tx) {
        $package = strtolower($tx->vtupackage);
        $amount = abs($tx->amount); // Convert to positive number

        if (str_contains($package, 'airtime')) {
            $summary->total_airtime_amount += $amount;
            $summary->total_airtime_count += 1;
        } elseif (str_contains($package, 'data')) {
            $summary->total_data_amount += $amount;
            $summary->total_data_count += 1;
        } elseif (str_contains($package, 'electricity')) {
            $summary->total_electricity_amount += $amount;
            $summary->total_electricity_count += 1;
        } elseif (str_contains($package, 'tv') || str_contains($package, 'cable')) {
            $summary->total_cabletv_amount += $amount;
            $summary->total_cabletv_count += 1;
        }
    }

    return $summary;
}

 public function getVtuDetails()
{
    try {
        $userData = $this->RouteProtecion();
    } catch (UnexpectedValueException $e) {
        echo json_encode([
            'status' => 401,
            'message' => $e->getMessage()
        ]);
        exit;
    }

    $user_id = $userData->user_id;
    $email = $userData->email;

    // Get VTU transactions for this user only
    $vtu_services = $this->userModel->getVtu($email); // Assuming this returns only the user's VTU data

    $summary = [
        'total_airtime_amount' => 0,
        'total_airtime_count' => 0,
        'total_data_amount' => 0,
        'total_data_count' => 0,
        'total_electricity_amount' => 0,
        'total_electricity_count' => 0,
        'total_cabletv_amount' => 0,
        'total_cabletv_count' => 0,
    ];

    foreach ($vtu_services as $tx) {
        $package = strtolower($tx->vtupackage ?? '');
        $amount = abs($tx->amount ?? 0); // Make sure we don't work with null

        if (str_contains($package, 'airtime')) {
            $summary['total_airtime_amount'] += $amount;
            $summary['total_airtime_count'] += 1;
        } elseif (str_contains($package, 'data')) {
            $summary['total_data_amount'] += $amount;
            $summary['total_data_count'] += 1;
        } elseif (str_contains($package, 'electricity')) {
            $summary['total_electricity_amount'] += $amount;
            $summary['total_electricity_count'] += 1;
        } elseif (str_contains($package, 'tv') || str_contains($package, 'cable')) {
            $summary['total_cabletv_amount'] += $amount;
            $summary['total_cabletv_count'] += 1;
        }
    }

    echo json_encode([
        'status' => 200,
        'message' => 'VTU summary fetched successfully',
        'summary' => $summary
    ]);
    exit;
}


  public function setTransferPin()
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
    }

    $user_id = $userData->user_id;
    // $userDatax = $this->userModel->findUserByEmail_det2($userData->email);
    // $accountDetails = $this->userModel->getAccountDetails($userDatax->account_id, $user_id);
    
    $sentData = $this->getData();
    $pin = $sentData['transfer_pin'];

    if (isset($pin) && !empty($pin)) {
        $pinn = password_hash($pin, PASSWORD_DEFAULT);
        
        
        if($this->userModel->setTransferPin($pinn, $user_id)){
             $res = json_encode(array(
        'status' => true,
        'message' => 'transfer pin set successfully',
      ));
      http_response_code(200);
      print_r($res);
        }else{
             $res = json_encode(array(
        'status' => false,
        'message' => 'failed to set transfer pin'
      ));
      http_response_code(404);
      print_r($res);
        }
     
    } else {
      $res = json_encode(array(
        'status' => false,
        'message' => 'invalid pin'
      ));
      http_response_code(404);
      print_r($res);
    }
  }
  public function setLockPin()
  {
   
    
    $sentData = $this->getData();
    $userDatax = $this->userModel->findUserByEmail_det2($sentData['email']);
    $accountDetails = $this->userModel->getAccountDetails($userDatax->account_id, $userDatax->user_id);
    $pin = $sentData['pin'];

    if (isset($pin) && !empty($pin)) {
        $pinn = password_hash($pin, PASSWORD_DEFAULT);
        
        
        if($this->userModel->setLockPin($pinn, $userDatax->user_id)){
             $res = json_encode(array(
        'status' => true,
        'message' => 'Lock pin set successfully',
      ));
      http_response_code(200);
      print_r($res);
        }else{
             $res = json_encode(array(
        'status' => false,
        'message' => 'failed to set transfer pin'
      ));
      http_response_code(404);
      print_r($res);
        }
     
    } else {
      $res = json_encode(array(
        'status' => false,
        'message' => 'invalid pin'
      ));
      http_response_code(404);
      print_r($res);
    }
  }
  public function verifyTransferPin()
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
    }

    $user_id = $userData->user_id;
    $email = $userData->email;
     $data['otp'] = $this->generateNineDigitValue();
     
      $otp = password_hash($data['otp'], PASSWORD_DEFAULT);
      
     
    if($userData->transfer_pin == 0 || $userData->transfer_pin == "0"){
         $res = json_encode(array(
        'status' => false,
        'message' => 'Transfer pin not set'
      ));
      http_response_code(404);
      print_r($res);exit;
    }
    
    $sentData = $this->getData();
    $pin = $sentData['transfer_pin'];

    if (isset($pin) && !empty($pin)) {
       if (password_verify($pin, $userData->transfer_pin)) {
            $this->userModel->storeTransactionKey($otp, $user_id, $email);
             $res = json_encode(array(
        'status' => true,
        'message' => 'Pin validated',
        'secret_key' =>  $data['otp']
      ));
      http_response_code(200);
      print_r($res);
        }else{
             $res = json_encode(array(
        'status' => false,
        'message' => 'wrong pin'
      ));
      http_response_code(404);
      print_r($res);
        }
     
    } else {
      $res = json_encode(array(
        'status' => false,
        'message' => 'invalid pin'
      ));
      http_response_code(404);
      print_r($res);
    }
  }
  public function forgetPassword() {

    $sentData = $this->getData();

    $data = [
      'email'=> $sentData['email'],
    ];
  
    if ($this->userModel->findUserByEmail1($data['email'])) {
        

      $data['otp'] = $this->generateSixDigitValue();


      if($this->passresetemailer($data, "PASSWORD RESET")){
        $data['otp'] = password_hash($data['otp'], PASSWORD_DEFAULT);
        $this->userModel->updateResetToken($data);
        $res = (array(
            'status_code' => 200,
          'status'=> true,
          'message'=> 'otp sent to '.$data['email']
          ));
          $this->handleResponse($res);
        } else {
          $res = (array(
              'status_code' => 404,
            'status'=> false,
              'message'=> 'failed to send email'
              ));
              $this->handleResponse($res);
            }

    //   $otp = $data['otp'];

    //   if($this->emailSent($data)){
    //     $data['otp'] = password_hash($data['otp'], PASSWORD_DEFAULT);
    //     $this->userModel->updateResetToken($data);
    //     $res = json_encode(array(
    //       'status'=> true,
    //       'message'=> 'otp sent',
    //       'otp' => $otp

    //       ));
    //       http_response_code(200);
    //       print_r($res);
    //   }else {
    //     $res = json_encode(array(
    //       'status'=> false,
    //       'message'=> 'otp not sent'
    //       ));
    //       http_response_code(404);
    //       print_r($res);
    //   }





    
  }else{
       $res = (array(
              'status_code' => 404,
            'status'=> false,
              'message'=> 'failed to find email'
              ));
              $this->handleResponse($res);
  }
    }
    
     public function setNotifications()
  { ///method start

//  try {
//     $userData = $this->RouteProtecion();
// } catch (UnexpectedValueException $e) {
//     $res = [
//       'status' => 401,
//       'message' =>  $e->getMessage(),
//     ];
//     print_r(json_encode($res));
//     exit;
// }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') { ///post start

        //Sanitize POST data
        $loginData = $this->getData();
        // Process form


        // Init data
        $data = [

          'headers' => $loginData['headers'],
          'text' => $loginData['text'],
          'img' => $loginData['img'],
        ];

        //echo $data['email'];
        //exit;

        // Validate Name
        if (empty($data['headers'])) {
            $response = array(
                                   'status' => 'false',

                                   'message' => 'enter headers',

                                 );

            print_r(json_encode($response));
            exit;
        }
        if (empty($data['text'])) {
            $response = array(
                                   'status' => 'false',

                                   'message' => 'enter text',

                                 );

            print_r(json_encode($response));
            exit;
        }
        if (empty($data['img'])) {
            $response = array(
                                   'status' => 'false',

                                   'message' => 'enter img',

                                 );

            print_r(json_encode($response));
            exit;
        }


        if ($this->userModel->setNotifications($data)) {
            ////emal found on our data base

            $response = array(
                                 'status' => 'true',

                                 'message' => 'notification set successfully',

                               );

            print_r(json_encode($response));
            exit;
        } else {

            //email not found
            $response = array(
                               'status' => 'false',

                               'message' => 'an error occured',

                             );

            print_r(json_encode($response));
            exit;


        }
        ;



        //$resetPassword->
    } else {


       $response = [
                      'status' => 401,
                      'message' => 'Wrong method',

                    ];
                    print_r($response);


    } ///post end

}
    
    
  public function verifyPhone() {
    try {
      $userData = $this->RouteProtecion();
    } catch (UnexpectedValueException $e) {
      $res = [
        'status' => 401,
        'message' =>  $e->getMessage(),
      ];
      print_r(json_encode($res));
      exit;
    }
    

    $sentData = $this->getData();
    
     if($this->userModel->findUserByPhone($sentData['phone'])){
        http_response_code(404);
      print_r(json_encode(array(
        "status" => false,
        "message" => "phone number already in use ."
    )));
    exit;
    }
    $url = "https://v3.api.termii.com/api/sms/send";
    $senderID = "PAYKING";
    $senderIDazz = "".$this->generateSixDigitValue()."";

$messageBody = ''.$senderIDazz.'.  Do not share it with anyone.
 
';
      // Prepare the payload
        $payload = json_encode([
            "sms" => $messageBody,
           "to" => '234' . substr($sentData['phone'], 1),
            "senderID" => $senderID,
             "channel"=> "generic",
             "type"=> "plain",
              "from"=> "vel56",
              "api_key"=> "TLfVkhvyxKRpMwoOcnGKlMoSsCKNJuNdTDcJAwMBirpZZKZuQfSiLIOAURisIn"
        ]);
        $payload2 = array(
            "messageText" => $messageBody,
            "mobileNumber" => $sentData['phone'],
            "senderID" => $senderID
        );
  
      

 $response = $this->sendCustomMessage($url, $payload);
 $ress = json_decode($response);
 
      if($ress->message == "Successfully Sent" && $ress->code == "ok"){
        $payload2['otp'] = password_hash($senderIDazz, PASSWORD_DEFAULT);
        $this->userModel->updateResetToken234($payload2, $userData->email);
        $res = (array(
            'status_code' => 200,
          'status'=> true,
          'message'=> 'Code sent to Phone Successfully.'
          ));
          $this->handleResponse($res);
        } else {
          $res = (array(
              'status_code' => 404,
            'status'=> false,
              'message'=> 'failed to verifyPhone'
              ));
              $this->handleResponse($res);
            }


    }
    
  public function verifyPhoneCode() {
    try {
      $userData = $this->RouteProtecion();
    } catch (UnexpectedValueException $e) {
      $res = [
        'status' => 401,
        'message' =>  $e->getMessage(),
      ];
      print_r(json_encode($res));
      exit;
    }
    

    $sentData = $this->getData();
    
    $user = $this->userModel->findUserByEmail360x($userData->email);
   $otp = $sentData['phone_code'];
   $hash_otp = $user->email_reset_token;
      if(password_verify($otp, $hash_otp)){

        $this->userModel->updateResetToken23456($userData->email);
        $res = (array(
            'status_code' => 200,
          'status'=> true,
          'message'=> "Phone Number Validated Successfully."
          ));
          $this->handleResponse($res);
        } else {
            print_r(json_encode(array("otp" => $otp, "hash_otp" => $hash_otp)));
          $res = (array(
              'status_code' => 404,
            'status'=> false,
              'message'=> 'failed to validate number'
              ));
              $this->handleResponse($res);
            }


    }
    
    
  public function forgetTransferPin() {
      
    try {
      $userData = $this->RouteProtecion();
    } catch (UnexpectedValueException $e) {
      $res = [
        'status' => 401,
        'message' =>  $e->getMessage(),
      ];
      print_r(json_encode($res));
      exit;
    }

    $sentData = $this->getData();
   $user_id = $userData->user_id;
   $data['email'] = $userData->email;
    
    if($userData->transfer_pin == 0 || $userData->transfer_pin == "0"){
         $res = json_encode(array(
        'status' => false,
        'message' => 'Transfer pin not set'
      ));
      http_response_code(404);
      print_r($res);exit;
    }
  
    if ($this->userModel->findUserByEmail1($data['email'])) {
        

      $data['otp'] = $this->generateSixDigitValue();


      if($this->passresetemailer($data, "TRANSFER PIN RESET")){
        $data['otp'] = password_hash($data['otp'], PASSWORD_DEFAULT);
        $this->userModel->updateResetToken($data);
        $res = (array(
            'status_code' => 200,
          'status'=> true,
          'message'=> 'otp sent to '.$data['email']
          ));
          $this->handleResponse($res);
        } else {
          $res = (array(
              'status_code' => 404,
            'status'=> false,
              'message'=> 'failed to send email'
              ));
              $this->handleResponse($res);
            }

    //   $otp = $data['otp'];

    //   if($this->emailSent($data)){
    //     $data['otp'] = password_hash($data['otp'], PASSWORD_DEFAULT);
    //     $this->userModel->updateResetToken($data);
    //     $res = json_encode(array(
    //       'status'=> true,
    //       'message'=> 'otp sent',
    //       'otp' => $otp

    //       ));
    //       http_response_code(200);
    //       print_r($res);
    //   }else {
    //     $res = json_encode(array(
    //       'status'=> false,
    //       'message'=> 'otp not sent'
    //       ));
    //       http_response_code(404);
    //       print_r($res);
    //   }





    
  }else{
       $res = (array(
              'status_code' => 404,
            'status'=> false,
              'message'=> 'failed to find email'
              ));
              $this->handleResponse($res);
  }
    }
    
    
  public function changeTransferPin() {
      
      
    try {
      $userData = $this->RouteProtecion();
    } catch (UnexpectedValueException $e) {
      $res = [
        'status' => 401,
        'message' =>  $e->getMessage(),
      ];
      print_r(json_encode($res));
      exit;
    }

    $user_id = $userData->user_id;

    $sentData = $this->getData();

    $data = [
      'old_pin'=> $sentData['old_pin'],
      'new_pin'=> $sentData['new_pin'],
      'confirm_new_pin'=> $sentData['confirm_new_pin'],
    ];
       if($userData->transfer_pin == 0 || $userData->transfer_pin == "0"){
         $res = json_encode(array(
        'status' => false,
        'message' => 'Transfer pin not set'
      ));
      http_response_code(404);
      print_r($res);exit;
       }
       if($data['new_pin'] != $data['confirm_new_pin']){
         $res = json_encode(array(
        'status' => false,
        'message' => 'pin does not match'
      ));
      http_response_code(404);
      print_r($res);exit;
       }
       if (password_verify($data['old_pin'], $userData->transfer_pin)) {
              $data['new_pin'] = password_hash($data['new_pin'], PASSWORD_DEFAULT);
        if($this->userModel->setTransferPin($data['new_pin'], $user_id)){
             $res = json_encode(array(
        'status' => true,
        'message' => 'transfer pin set successfully',
      ));
      http_response_code(200);
      print_r($res);
        }else{
             $res = json_encode(array(
        'status' => false,
        'message' => 'failed to set transfer pin'
      ));
      http_response_code(404);
      print_r($res);
        }
        }else{
             $res = json_encode(array(
        'status' => false,
        'message' => 'wrong pin'
      ));
      http_response_code(404);
      print_r($res);
        }
  
 
     
    }
    
  public function changeLockPin() {
      
      
    try {
      $userData = $this->RouteProtecion();
    } catch (UnexpectedValueException $e) {
      $res = [
        'status' => 401,
        'message' =>  $e->getMessage(),
      ];
      print_r(json_encode($res));
      exit;
    }

    $user_id = $userData->user_id;

    $sentData = $this->getData();

    $data = [
      'old_pin'=> $sentData['old_pin'],
      'new_pin'=> $sentData['new_pin'],
      'confirm_new_pin'=> $sentData['confirm_new_pin'],
    ];
       if($userData->transfer_pin == 0 || $userData->transfer_pin == "0"){
         $res = json_encode(array(
        'status' => false,
        'message' => 'Transfer pin not set'
      ));
      http_response_code(404);
      print_r($res);exit;
       }
       if($data['new_pin'] != $data['confirm_new_pin']){
         $res = json_encode(array(
        'status' => false,
        'message' => 'pin does not match'
      ));
      http_response_code(404);
      print_r($res);exit;
       }
       if (password_verify($data['old_pin'], $userData->transfer_pin)) {
              $data['new_pin'] = password_hash($data['new_pin'], PASSWORD_DEFAULT);
        if($this->userModel->setTransferPin($data['new_pin'], $user_id)){
             $res = json_encode(array(
        'status' => true,
        'message' => 'transfer pin set successfully',
      ));
      http_response_code(200);
      print_r($res);
        }else{
             $res = json_encode(array(
        'status' => false,
        'message' => 'failed to set transfer pin'
      ));
      http_response_code(404);
      print_r($res);
        }
        }else{
             $res = json_encode(array(
        'status' => false,
        'message' => 'wrong pin'
      ));
      http_response_code(404);
      print_r($res);
        }
  
 
     
    }

  public function resetTransferPin() {
      
          try {
      $userData = $this->RouteProtecion();
    } catch (UnexpectedValueException $e) {
      $res = [
        'status' => 401,
        'message' =>  $e->getMessage(),
      ];
      print_r(json_encode($res));
      exit;
    }

    $user_id = $userData->user_id;

    $sentData = $this->getData();
    

    $data = [
      'otp' => $sentData['otp'],
      'transfer_pin'=> $sentData['transfer_pin'],
      'confirm_transfer_pin' => $sentData['confirm_transfer_pin'],
    ];

    foreach ($sentData as $key => $value) {
    
      if (!isset($key) && is_string($value) && $value === "") {
          $res = json_encode(array(
            
              "status" => false,
              "message" => "Incomplete params: " . $key . " is required."
          ));
    http_response_code(404);
          print_r($res);
          exit;
      }
  }

  if ($data['confirm_transfer_pin'] != $data['transfer_pin']) {
    $res = json_encode(array(
      'status'=> false,
      'message'=> 'password do not match'

    ));
    http_response_code(404);
    print_r($res);
  }


    $hash_otp = $userData->password_reset_token;
    if (password_verify($data['otp'], $hash_otp)) {
        $pinn = password_hash($data['transfer_pin'], PASSWORD_DEFAULT);
        $data['user_id'] = $user_id;
        $data['email'] = $userData->email;

        if($this->userModel->setTransferPin($pinn, $user_id)){
             $res = json_encode(array(
        'status' => true,
        'message' => 'transfer pin set successfully',
      ));
      http_response_code(200);
      print_r($res);
        }else{
             $res = json_encode(array(
        'status' => false,
        'message' => 'failed to set transfer pin'
      ));
      http_response_code(404);
      print_r($res);
        }
    }

  }
  
  public function resetPassword() {

    $sentData = $this->getData();
    

    $data = [
      'otp' => $sentData['otp'],
      'password'=> $sentData['password'],
      'confirm_password' => $sentData['confirm_password'],
    ];

    foreach ($sentData as $key => $value) {
    
      if (!isset($key) && is_string($value) && $value === "") {
          $res = json_encode(array(
            
              "status" => false,
              "message" => "Incomplete params: " . $key . " is required."
          ));
    http_response_code(404);
          print_r($res);
          exit;
      }
  }

  if ($data['confirm_password'] != $data['password']) {
    $res = json_encode(array(
      'status'=> false,
      'message'=> 'password do not match'

    ));
    http_response_code(404);
    print_r($res);
  }

    $usersData = $this->userModel->findAllUsers2();

    $otpMatched = false; // Flag to track OTP match

foreach ($usersData as $user) {
    $hash_otp = $user->password_reset_token;
    if (password_verify($data['otp'], $hash_otp)) {
        $otpMatched = true; // OTP match found
        ///// Send reset password email /////
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $data['user_id'] = $user->user_id;
        $data['email'] = $user->email;

        if ($this->userModel->updatePassword($data)) {
            $res = json_encode(array(
                'status' => true,
                'message' => 'Password reset successful'
            ));
            http_response_code(200);
            print_r($res);
        } else {
            $res = json_encode(array(
                'status' => false,
                'message' => 'Password reset failed'
            ));
            http_response_code(404);
            print_r($res);
        }
        break; // Exit the loop after successful OTP verification and processing
    }
}

// If no match was found, send a response indicating the OTP is incorrect
if (!$otpMatched) {
    $res = json_encode(array(
        'status' => false,
        'message' => 'Invalid OTP'
    ));
    http_response_code(400);
    print_r($res);
}

  }
  
  
  public function verifyLockPin() {

    $sentData = $this->getData();
    

    $data = [
      'otp' => $sentData['pin'],
    ];

    foreach ($sentData as $key => $value) {
    
      if (!isset($key) && is_string($value) && $value === "") {
          $res = json_encode(array(
            
              "status" => false,
              "message" => "Incomplete params: " . $key . " is required."
          ));
          http_response_code(404);
          print_r($res);
          exit;
      }
  }

    $usersData = $this->userModel->findAllUsers2();

    $otpMatched = false; // Flag to track OTP match

foreach ($usersData as $user) {
    $hash_otp = $user->password_reset_token;
    if (password_verify($data['otp'], $hash_otp)) {
        $otpMatched = true; // OTP match found
        ///// Send reset password email /////
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $data['user_id'] = $user->user_id;
        $data['email'] = $user->email;

        if ($this->userModel->updatePassword($data)) {
            $res = json_encode(array(
                'status' => true,
                'message' => 'Password reset successful'
            ));
            http_response_code(200);
            print_r($res);
        } else {
            $res = json_encode(array(
                'status' => false,
                'message' => 'Password reset failed'
            ));
            http_response_code(404);
            print_r($res);
        }
        break; // Exit the loop after successful OTP verification and processing
    }
}

// If no match was found, send a response indicating the OTP is incorrect
if (!$otpMatched) {
    $res = json_encode(array(
        'status' => false,
        'message' => 'Invalid OTP'
    ));
    http_response_code(400);
    print_r($res);
}

  }
  
  
  
  
  
  
  public function activateAccount() {

    $sentData = $this->getData();
    

    $data = [
      'otp' => $sentData['otp'],
        'email' => $sentData['email']
    ];

    foreach ($sentData as $key => $value) {
    
      if (!isset($key) && is_string($value) && $value === "") {
          $res = json_encode(array(
            
              "status" => false,
              "message" => "Incomplete params: " . $key . " is required."
          ));
    http_response_code(404);
          print_r($res);
          exit;
      }
  }


    $usersData = $this->userModel->findUserByEmail_dett($data['email']);
    
    //   if ($usersData->activationx = 1) {
    //         $response = array(
    //           'status' => 'false',
    //           'message' => 'account activated',
    //         );
    //         http_response_code(404);
    //         print_r(json_encode($response));
    //         exit;
    //   }
    //  print_r($usersData);
    //       exit;

      $hash_otp = $usersData->email_reset_token;
      if(password_verify($data['otp'], $hash_otp)){
        ///// send reset password email////
        
        $data['user_id'] = $usersData->user_id;
        $data['email'] = $usersData->email;
        $data['password'] = $usersData->password;
        if ($this->userModel->register_user($data) &&  $this->createAccount($data) && $this->userModel->activateAccount($data)) {
            $log = $this->loginRegisteredUser($data);
          $mail =  $this->welcome_emailer($data, "WELCOME TO PAYKING");
            
           $res = json_encode(array(
            'status'=> true,
            'message'=> 'Registration successful',
            'access_token' => $log
            ));
            http_response_code(200);
            print_r($res);
        }else {
          $res = json_encode(array(
            'status'=> false,
            'message'=> 'Registration failed'

          ));
          http_response_code(404);
          print_r($res);
        }
      }else{
          $res = json_encode(array(
            
              "status" => false,
              "message" => "failed to verify otp"
          ));
    http_response_code(404);
          print_r($res);
          exit;
      }
    }
    
  
  public function activateAccount_admin() {

    $sentData = $this->getData();
    

    $data = [
      'otp' => $sentData['otp'],
        'email' => $sentData['email']
    ];

    foreach ($sentData as $key => $value) {
    
      if (!isset($key) && is_string($value) && $value === "") {
          $res = json_encode(array(
            
              "status" => false,
              "message" => "Incomplete params: " . $key . " is required."
          ));
    http_response_code(404);
          print_r($res);
          exit;
      }
  }


   $usersData = $this->userModel->findUserByEmail_dett($data['email']);
    //  print_r($usersData);
    // //       exit;
    //   if ($usersData->activationx = 1) {
    //         $response = array(
    //           'status' => 'false',
    //           'message' => 'account activated',
    //         );
    //         http_response_code(404);
    //         print_r(json_encode($response));
    //         exit;
    //   }
    // 

      $hash_otp = $usersData->email_reset_token;
      if(password_verify($data['otp'], $hash_otp)){
        ///// send reset password email////
        
        $data['user_id'] = $usersData->user_id;
        $data['email'] = $usersData->email;
        $data['password'] = $usersData->password;
        $data['reg_id'] = $usersData->registerer_id;
        if ($this->userModel->register_admin($data) &&  $this->createAccount($data) && $this->userModel->activateAccount($data)) {
            $log = $this->loginRegisteredUser($data);
          $mail =  $this->welcome_emailer($data, "WELCOME TO PAYKING");
            
           $res = json_encode(array(
            'status'=> true,
            'message'=> 'Registration successful',
            'access_token' => $log
            ));
            http_response_code(200);
            print_r($res);
        }else {
          $res = json_encode(array(
            'status'=> false,
            'message'=> 'Registration failed'

          ));
          http_response_code(404);
          print_r($res);
        }
      }else{
          $res = json_encode(array(
            
              "status" => false,
              "message" => "failed to verify otp"
          ));
    http_response_code(404);
          print_r($res);
          exit;
      }
    }
    
    public function  resendotp(){
      $sentData =  $this->getData();
      
      $data['otp'] = $this->generateSixDigitValue();
$data['email'] = $sentData['email'];

if(empty($data['email'])){
     $res = (array(
              'status_code' => 404,
            'status'=> false,
              'message'=> 'enter email'
              ));
              $this->handleResponse($res);
}


      if($this->sendOTPEmail($data)){
        $data['otp'] = password_hash($data['otp'], PASSWORD_DEFAULT);
        $this->userModel->updateResetToken2($data);
        $res = (array(
            'status_code' => 200,
          'status'=> true,
          'message'=> 'otp sent to '.$data['email']
          ));
          $this->handleResponse($res);
        } else {
          $res = (array(
              'status_code' => 404,
            'status'=> false,
              'message'=> 'failed to send email'
              ));
              $this->handleResponse($res);
            }


    }
    
    
    
  public function verifyLogin() {
      
      
         try {
      $userData = $this->RouteProtecion();
    } catch (UnexpectedValueException $e) {
      $res = [
        'status' => 401,
        'message' =>  $e->getMessage(),
      ];
      print_r(json_encode($res));
      exit;
    }


            $response = array(
              'status' => true,
              'message' => 'login valid',

            );

            print_r(json_encode($response));
            exit;
      
  }
  
 public function loginRegisteredUser($data)
  {

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      // print_r(json_encode($data));
      
          $loginDatax = $this->userModel->loginUser($data['email']);
          
          $postPassword = $data['password'];
          $loginStatus = $loginDatax->activationx;
          $hash_password = $loginDatax->password;
          $email = $loginDatax->email;
          $user_id = $loginDatax->user_id;



          if ($loginStatus < 1) {
            // $data['msg'] = 'User access has Not Been activad, please contact or visit NIS secretariat !';


            $response = array(
              'status' => 'false',
              'message' => 'login status disabled',

            );

            print_r(json_encode($response));
            exit;
          } 



            $tokenX = $token = "token" . md5(date("dmyhis") . rand(1222, 89787)) . md5(date("dmyhis") . rand(1222, 89787)) . md5(date("dmyhis") . rand(1222, 89787)) . md5(date("dmyhis") . rand(1222, 89787)) . md5(date("dmyhis") . rand(1222, 89787));



            //echo $tokenX;
            $this->userModel->updateToken($user_id, $tokenX, $email);

            $loginData = $this->userModel->findLoginByToken($tokenX);
             
            $userData = $this->userModel->findUserByEmail_det2($loginData->email);

            $accountDetails = $this->userModel->getAccountDetails($userData->account_id, $user_id);
            $initData = [
              'loginData' => $loginData,
              'userAccount' => $accountDetails,
            ];
            
            
            
            // ///////end
            
            
            // $initData = [
            //   'loginData' => $loginData,
            //   'userData' => $userData,
            // ];

            // ////////////start

            // /* 
            // $userID = $initData['loginData']->user_id;
            // $appToken = $initData['loginData']->token; */
 $datatoken = [
              'user_id' => $user_id,
              'email' => $email,
              'appToken' => $initData['loginData']->token,
            ];
            $JWT_token = $this->getMyJsonID($datatoken, $this->serverKey);



            $response = array(
              'status' => true,
              'access_token' => $JWT_token,
            //   'datatoken' => $datatoken,
              'message' => 'success',
              'data' => $initData,

            );


           return $JWT_token;


            /////////////////end

          



          ///////////////////////////////end

        } else {


          $response = array(
            'status' => 'false',

            'message' => 'invalid user login detail',
            'data' => $data,
          );

          print_r(json_encode($response));
          exit;


          //$data['msg'] = 'Invalid Login Access !';

          // redirect('users/login/1');
        }
     
  }

  public function getuserDetails(){

      
    try {
      $userData = $this->RouteProtecion();
        } catch (UnexpectedValueException $e) {
      $res = [
        'status' => 401,
        'message' =>  $e->getMessage(),
      ];
      print_r(json_encode($res));
      exit;
      }

      $user_id = $userData->user_id;

    // $user = $this->userModel->getUser($user_id);
      $user = $this->userModel->getUser($user_id);
       
            $userData = $this->userModel->findUserByEmail_det2($userData->email);
            $accountDetails = $this->userModel->getAccountDetails($userData->account_id, $user_id);
    
    $user = (array)$user;
        $user = array_filter($user, function ($value, $key) {
        return $key !== "password" && $key !== "work" && $key !== "suffix" && $key !== "rank" && $key !== "memberType" && $key !== "maritalStatus" && $key !== "nextKin" && $key !== "activeCode" && $key !== "altEmail" && $key !== "nin_no" && $key !== "nin_img" && $key !== "bvn_no";
    }, ARRAY_FILTER_USE_BOTH);
   
    
    if(!empty($user->nin_no)){
        $user['ninVerified'] = 1;
    }else{
        $user['ninVerified'] = 0;
    }
    
    if(!empty($user->nin_img)){
        $user['nin_imgVerified'] = 1; 
    }else{
         $user['nin_imgVerified'] = 0;
    }
    
    
    if(!empty($user->bvn_no)){
        $user['bvnVerified'] = 1; 
    }else{
         $user['bvnVerified'] = 0;
    }
    
    
    if($user && $userData){
      $res = json_encode(array(
        'status'=> true,
        'message'=> 'success',
        'user'=> $user,
        "account" => $accountDetails
        ));
        http_response_code(200);
        print_r($res);
    }else {
      $res = json_encode(array(
        'status'=> false,
        'message'=> 'faild'
        ));
        http_response_code(404);
        print_r($res);
    }
  }

  public function getNotifications(){

      
    try {
      $userData = $this->RouteProtecion();
        } catch (UnexpectedValueException $e) {
      $res = [
        'status' => 401,
        'message' =>  $e->getMessage(),
      ];
      print_r(json_encode($res));
      exit;
      }
    $user = $this->userModel->getNotifications($userData->user_id);
    

    // if($user){
      $res = json_encode(array(
        'status'=> true,
        'message'=> 'success',
        'data'=> $user
        ));
        http_response_code(200);
        print_r($res);
    // }else {
    //   $res = json_encode(array(
    //     'status'=> false,
    //     'message'=> 'faild'
    //     ));
    //     http_response_code(404);
    //     print_r($res);
    // }
  }
  public function getallUsers(){

      
    try {
      $userData = $this->RouteProtecion();
        } catch (UnexpectedValueException $e) {
      $res = [
        'status' => 401,
        'message' =>  $e->getMessage(),
      ];
      print_r(json_encode($res));
      exit;
      }

    
    $user =    $usersData = $this->userModel->findAllUsers();
    

    if($user){
      $res = json_encode(array(
        'status'=> true,
        'message'=> 'success',
        'data'=> $user
        ));
        http_response_code(200);
        print_r($res);
    }else {
      $res = json_encode(array(
        'status'=> false,
        'message'=> 'faild'
        ));
        http_response_code(404);
        print_r($res);
    }
  }

  public function edit_user()
  {
    try {
      $userData = $this->RouteProtecion();
        } catch (UnexpectedValueException $e) {
      $res = [
        'status' => 401,
        'message' =>  $e->getMessage(),
      ];
      http_response_code(401);
      print_r(json_encode($res));
      exit;
      } catch(DomainException $e) {
        $res = [
          'status' => 401,
          'message' =>  $e->getMessage(),
        ];
        http_response_code(401);
        print_r(json_encode($res));
        exit;
      }

      
      $sentData = $this->getData();
      $data = array(
          "fullname" => trim($sentData["fullname"]),
          "uname" => trim($sentData["username"]),
          "dob" => trim($sentData["dob"]),
          "gender" => trim($sentData["gender"]),
          "address" => trim($sentData["address"]),
          "email" => $userData->email,
          "image" => $_FILES["image"],
          "nin" => trim($sentData["nin"]),
          "user_id" =>  $userData->user_id,

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

    if(!$this->userModel->findUserByEmail1($data['email'])){
        http_response_code(404);
      print_r(json_encode(array(
        "status" => false,
        "message" => "User Not registered."
    )));
    exit;
    }
    // if(!$this->userModel->findUserByNin($data['nin'])){
    //     http_response_code(404);
    //   print_r(json_encode(array(
    //     "status" => false,
    //     "message" => "NIN already Used registered."
    // )));
    // exit;
    // }

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
      if ($this->userModel->edit_user($data)) {
          $res = json_encode(array(
              'status' => true,
              'message' => 'edit profile successful'
          ));
          http_response_code(200);
          print_r($res);
          exit;


      } else {
          
          $res = json_encode(array(
              'status' => false,
              'message' => 'registeration failed'
          ));
          http_response_code(404);
          print_r($res);
          exit;
      }



  }
  
  
 
  public function register_user()
  {
      
      $sentData = $this->getData();
      $data = array(
          "email" => trim($sentData["email"]),
          "user_id" =>  $this->generateUniqueId(),
          "password"=> trim($sentData["password"]),
          "confirm_password"=> trim($sentData["confirm_password"]),
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

    if($this->userModel->findUserByEmail1($data['email'])){
        http_response_code(404);
      print_r(json_encode(array(
        "status" => false,
        "message" => "User Already registered ."
    )));
    exit;
    }




      $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
      

      
      if ($this->userModel->register_user2($data)) {
          
           $data['otp'] = $this->generateSixDigitValue();


      if($this->sendOTPEmail($data)){
        $data['otp'] = password_hash($data['otp'], PASSWORD_DEFAULT);
        $this->userModel->updateResetToken2($data);
        $res = (array(
            'status_code' => 200,
          'status'=> true,
          'message'=> 'otp sent to '.$data['email']
          ));
          $this->handleResponse($res);
        } else {
          $res = (array(
              'status_code' => 404,
            'status'=> false,
              'message'=> 'failed to send email'
              ));
              $this->handleResponse($res);
            }

        


      } else {
          
          $res = json_encode(array(
              'status' => false,
              'message' => 'registeration failed'
          ));
          http_response_code(404);
          print_r($res);
          exit;
      }



  }
  
  
  public function register_admin()
  {
       try {
      $userData = $this->RouteProtecion();
        } catch (UnexpectedValueException $e) {
      $res = [
        'status' => 401,
        'message' =>  $e->getMessage(),
      ];
      http_response_code(401);
      print_r(json_encode($res));
      exit;
      } catch(DomainException $e) {
        $res = [
          'status' => 401,
          'message' =>  $e->getMessage(),
        ];
        http_response_code(401);
        print_r(json_encode($res));
        exit;
      }
    //   echo json_encode($userData);exit;
      
      if($userData->roleID == 6  || $userData->roleID == 7 ){
          
      }else{$res = [
          'status' => 401,
          'message' =>  "you are not permitted",
        ];
        http_response_code(401);
        print_r(json_encode($res));
        exit;
      }
      $sentData = $this->getData();
      $data = array(
          "email" => trim($sentData["email"]),
          "user_id" =>  $this->generateUniqueId(),
          "password"=> trim($sentData["password"]),
          "confirm_password"=> trim($sentData["confirm_password"]),
          'reg_id' => $userData->user_id
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

    if($this->userModel->findUserByEmail1($data['email'])){
        http_response_code(404);
      print_r(json_encode(array(
        "status" => false,
        "message" => "User Already registered ."
    )));
    exit;
    }


      $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
      

      
      if ($this->userModel->register_admin2($data)) {
          
           $data['otp'] = $this->generateSixDigitValue();


      if($this->sendOTPEmail($data)){
        $data['otp'] = password_hash($data['otp'], PASSWORD_DEFAULT);
        $this->userModel->updateResetToken32($data);
        $res = (array(
            'status_code' => 200,
          'status'=> true,
          'message'=> 'otp sent to '.$data['email']
          ));
          $this->handleResponse($res);
        } else {
          $res = (array(
              'status_code' => 404,
            'status'=> false,
              'message'=> 'failed to send email'
              ));
              $this->handleResponse($res);
            }

        


      } else {
          
          $res = json_encode(array(
              'status' => false,
              'message' => 'registeration failed'
          ));
          http_response_code(404);
          print_r($res);
          exit;
      }



  }
  public function google_sign_up()
  {
      
      $sentData = $this->getData();
      $data = array(
          "email" => trim($sentData["email"]),
          "user_id" =>  $this->generateUniqueId(),
          "password"=> trim($sentData["password"]),
          "confirm_password"=> trim($sentData["confirm_password"]),
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

    if($this->userModel->findUserByEmail1($data['email'])){
        http_response_code(404);
      print_r(json_encode(array(
        "status" => false,
        "message" => "User Already registered ."
    )));
    exit;
    }


      $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
      

      
      if ($this->userModel->register_user($data) &&  $this->createAccount($data) && $this->userModel->activateAccount($data)) {
          
         $log = $this->loginRegisteredUser($data);
          $mail =  $this->welcome_emailer($data, "WELCOME TO PAYKING");
            
           $res = json_encode(array(
            'status'=> true,
            'message'=> 'Registration successful',
            'access_token' => $log
            ));
            http_response_code(200);
            print_r($res);
        }else {
          $res = json_encode(array(
            'status'=> false,
            'message'=> 'Registration failed'

          ));
          http_response_code(404);
          print_r($res);
        }


  }
  
    public function createAccount($data)
  {
    $data['account_id'] = 'ACC_'.$this->generateUniqueId();
   return $this->userModel->createAccount($data);
  }


}
