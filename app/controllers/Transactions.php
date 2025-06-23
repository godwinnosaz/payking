<?php

class Transactions extends Controller
{

    protected $userModel;
    protected $transactionModel;
    public function __construct()
    {
        $this->userModel = $this->model("User");
        $this->transactionModel = $this->model("Transaction");

        $this->serverKey  = 'secret_server_key' . date("H");
    }

    
public function hydrogenToken()
{
    $url = 'https://api.hydrogenpay.com/walletservice/api/Auth/token';

    $data = json_encode([
    "username" => "hensley@paykingweb.com",
    "password" => "Hug@6411#"
]);
// Username:

// Password:

// hensley@paykingweb.com

// V^p7zIRwCU60
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'], // Send data as raw JSON
        CURLOPT_SSL_VERIFYPEER => true,  // Ensure secure SSL connection
        CURLOPT_TIMEOUT => 30,           // Prevent long waits
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);

    curl_close($ch);

    if ($error) {
        echo json_encode(['status' => false, 'message' => "cURL Error: $error"]);
    }

    if ($httpCode !== 200) {
        echo json_encode(['status' => false, 'message' => "Request failed with HTTP Code $httpCode", 'response' => json_decode($response, true)]);
    }

    $res = json_decode($response);
    $token = $res->data->token;
    return $token;
    // echo json_encode(['status' => true, 'response' => ($res)]);
}
    public function deposite()
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
        $loginData = $this->getData();
        
        print_r($loginData);
        
//     {
//     reference: "efc2-g2dd-fvvb",
//     session_id: "000015230313003808229026004700",
//     amount: 100,
//     fee: 1,
//     account_number: "4600577949",
//     originator_account_number: "4600000000",
//     originator_account_name: "Emeka Ajibade",
//     originator_bank: "0000014",
//     timestamp: "2021-06-30T23:48:49.197+00:00"
// }

        if (!isset($loginData['amount']) || !isset($loginData['session_id'])) {
            $response = [
                'status' => 'false',
                'message' => 'wrong parameters',
            ];
            print_r(json_encode($response));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $res = [
                'status' => 401,
                'message' => 'bad access'
            ];
            print_r(json_encode($res));
            exit;
        }

        $amount = $loginData['amount'];
        $transactionRef = $loginData['transactionref'];
        $tag = $userData->uname;

        // Calculate admin profit
        $calc = $amount * 1.02;
        $calc2 = $calc * 0.013;
        $adminProfit = $calc - ($calc2 + $amount);

        // Generate transaction ID
        $tr_id = 'payking' . md5(date('ymdihs') . rand(900, 3000));
         $userDatax = $this->userModel->findUserByEmail_det2($userData->email);
        $datax = [
            'tagname' => $userDatax->uname,
            'fulname' => $userDatax->fullname,
            'email' => $userData->email,
            's_id' => $userData->user_id,
            'tr_status' => 'successful',
            'tr_id' => $tr_id,
            't_ref' => $transactionRef,
            'amount' => $amount
        ];

        $dataAdmin = [
            'tagname' => "veluxpay",
            's_id' => "veluxef3a34902c79abd2f4838cdad5872312",
            'amount' => $adminProfit
        ];
//  &&
//             $this->transactionModel->creditAdmin($dataAdmin)
        if (
            $this->transactionModel->creditUser($datax) &&
            $this->transactionModel->deposite($datax)
        ) {

            $res = [
                'status' => 200,
                'message' => 'transaction successful',
                'receiver' => $datax['fulname'],
                'receiver_tag' => $datax['tagname'],
                'amount' => $amount,
                'data' => $datax
            ];
            print_r(json_encode($res));
            // Format the amount with commas based on its length
            $formattedAmount = number_format($amount);
            $title = "Deposit Successful 💰";
            $body = "Hi " . $userData->full_name . "! Your deposit of ₦" . $formattedAmount . " was successful. Check your veluxite account for the funds.";
            // $this->sendPushToUser($title, $body, $userData->fcmtoken);
            $data = [
                'header' => $title,
                'text' => $body,
                'user_id' => $datax['s_id'],
                'img' => ''
                ];
            $this->setNotificationsxx($data);
            exit;
        } else {
            $res = [
                'status' => 401,
                'message' => 'failed to complete transaction'
            ];
            print_r(json_encode($res));
            // Format the amount with commas based on its length
            $formattedAmount = number_format($amount);
            $title = "Deposit Failed 💰";
            $body = "Hi " . $userData->full_name . "! We regret to inform you that your deposit of ₦" . $formattedAmount . " was unsuccessful. Please verify your details and try again. If the issue persists, kindly contact support for assistance.";
            $this->sendPushToUser($title, $body, $userData->fcmtoken);
              $data = [
                'header' => $title,
                'text' => $body,
                'user_id' => $datax['s_id'],
                'img' => ''
                ];
            $this->setNotificationsxx($data);

            exit;
        }
    }
    
    public function searchbank(){
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
        $loginData = $this->getData();
        // Define the search query
        $search = $loginData['acc']; // You can change this dynamically
        $token = $this->hydrogenToken();
        // Construct the URL
        $url = "https://api.hydrogenpay.com/walletservice/api/v1/FundsTransfer/get-bank-details?search=" . urlencode($search);
        
        // Initialize cURL
        $ch = curl_init();
        
        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'XAppKey: SK_TEST_749d490144105f572938a7ea27aff8b3',
            'Authorization: Bearer '.$token,
          
        ]);
        
        // Execute the request
        $response = curl_exec($ch);
        
        // Check for errors
        if (curl_errno($ch)) {
            echo 'Curl error: ' . curl_error($ch);
        } else {
            // Display the response
            echo $response;
        }
        
        // Close cURL
        curl_close($ch);

    }
    
      public function getBankList(){
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
       // print_r(json_encode($userData));
        //exit;
        $urli =  "https://services2.vpay.africa/api/service/v1/query/merchant/login";
        $urle =  "https://services2.vpay.africa/api/service/v1/query/bank/list/show";
        $key2 = "dfaae40b-6457-4c77-932c-6b0ac6733e8a";
      
        $password = "Wesson1234$";
        $names = "samueldickson06@gmail.com" ;
        $ydata =  [
          "username"=> $names,
          "password"=> $password,
        ];
            
          $header = [
            "Content-Type: application/json",
            "publicKey: ".$key2,
          ];
    
        // $token = $this->reportModel->getVpayToken();
        $token = $this->getLogin($ydata, $urli, $header);
        $headerx = [
          "Content-Type: application/json",
          "publicKey: ".$key2,
          "b-access-token: ".$token
        ];
          $string = (json_encode($ydata));
            $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $urle);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        
        curl_setopt($ch,CURLOPT_CUSTOMREQUEST,'GET');
        
        curl_setopt($ch, CURLOPT_POSTFIELDS, $string);
       
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerx);
   
        
       
        $response = curl_exec($ch);
        curl_close($ch);
        $res = (($response));
         print_r( $res);

    }
    //  public function getBankName()
    // {

    //   try {
    //     $userData = $this->RouteProtecion();
    //       } catch (UnexpectedValueException $e) {
    //     $res = [
    //       'status' => 401,
    //       'message' =>  $e->getMessage(),
    //     ];
    //     print_r(json_encode($res));
    //     exit;
    //     }
    //     if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   
    //       $loginData = $this->getData();
      
      
    //       $data = [
    //         'bankcode'=> $loginData['bank_code'],
    //         'accountNo'=> $loginData['account'],  
    //         'email_err'=> '',
    //         'amount_err'=> '',
    //         'payfor_err'=> ''
    //       ];
      
      
    //       if (empty($data['bankcode'])) {
    //         $response = array(
    //           'status' => 'false',
        
    //           'message' => 'Enter bank code details',
        
    //         );
        
    //         print_r(json_encode($response));
    //         exit;
    //       } else {
    //         if (strlen($data['bankcode']) != 6) {
    //           $response = array(
    //             'status' => 'false',
          
    //             'message' => 'Enter bank code details',
          
    //           );
          
    //           print_r(json_encode($response));
    //           exit;
    //         }
    //       }
    //       if (empty($data['accountNo'])) {
    //         $response = array(
    //           'status' => 'false',
        
    //           'message' => 'Enter payfor details',
        
    //         );
        
    //         print_r(json_encode($response));
    //         exit;
    //       } else {
    //         if (strlen($data['accountNo']) != 10) {
    //           $response = array(
    //             'status' => 'false',
          
    //             'message' => 'Enter bank account number',
          
    //           );
          
    //           print_r(json_encode($response));
    //           exit;
    //         }
    //       }

    //         $url = "https://api.hydrogenpay.com/walletservice/api/v1/FundsTransfer/name-enquiry";
    //       $token = $this->hydrogenToken();
        
    //       $ydatax =  [
    //         "beneficiaryAccount"=> $data['accountNo'],
    //         "bankCode"=> $data['bankcode'],
    //       ];

    //       $string = (json_encode($ydatax));
    //      $curl = curl_init();

    // curl_setopt_array($curl, array(
    //     CURLOPT_URL => $url,
    //     CURLOPT_RETURNTRANSFER => true,
    //     CURLOPT_POST => true,
    //     CURLOPT_POSTFIELDS => ($string),
    //     CURLOPT_HTTPHEADER => array(
    //         'XAppKey: SK_TEST_749d490144105f572938a7ea27aff8b3',
    //         'Authorization: Bearer '.$token,
    //     )
    // ));

    // $response = curl_exec($curl);
    // curl_close($curl);
    // echo $response;exit;
    //       $res = json_decode($response);
    //     //   $name = $res->data->data->name;
    //     //   if($res->status == "true" && isset($name)){
    //     //       $value = [
    //     //           "status" => true,
    //     //           'accountName' => $name,
    //     //           ];
    //     //           print_r(json_encode($value));
    //     //   }else{
    //     //       $value = [
    //     //           "status" => false,
    //     //           'message' => "account does not exist",
    //     //           ];
    //     //           print_r(json_encode($value));
    //     //   }
          
    //         }
      
    // }

public function getBankName()
{
    try {
        $userData = $this->RouteProtecion();
    } catch (UnexpectedValueException $e) {
        $res = [
            'status' => 401,
            'message' => $e->getMessage(),
        ];
        print_r(json_encode($res));
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $loginData = $this->getData();

        $bankCode = trim($loginData['bank_code'] ?? '');
        $accountNo = trim($loginData['account'] ?? '');

        if (empty($bankCode) || strlen($bankCode) != 6) {
            echo json_encode([
                'status' => false,
                'message' => 'Enter valid 6-digit bank code',
            ]);
            exit;
        }

        if (empty($accountNo) || strlen($accountNo) != 10) {
            echo json_encode([
                'status' => false,
                'message' => 'Enter valid 10-digit account number',
            ]);
            exit;
        }

        // Prepare JSON payload
        $payload = json_encode([
            "beneficiaryAccount" => $accountNo,
            "bankCode" => $bankCode,
        ]);
        $token = $this->hydrogenToken();
        // Updated cURL block with fixed headers
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.hydrogenpay.com/walletservice/api/v1/FundsTransfer/name-enquiry',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'XAppKey: SK_TEST_749d490144105f572938a7ea27aff8b3',
                'Content-Type: application/json',
                'Authorization: Bearer '.$token,
                
            ],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);
        $res = json_decode($response);
        echo json_encode(["status"=>true, "beneficiaryName" => $res->data->beneficiaryName]);
        exit;
    }
}

    
     public function getLogin($ydata, $urli, $header){
      $y_string = (json_encode($ydata));
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $urli);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        
        curl_setopt($ch, CURLOPT_POST, TRUE);
        
        curl_setopt($ch, CURLOPT_POSTFIELDS, $y_string);
       
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
   
        
        $response = curl_exec($ch);
        curl_close($ch);
        $res = (json_decode($response));
        $token = ($res->token);
        return $token;

    }
    
    public function verifyNIN2() {
         $sentData = $this->getData();
        $bvnId = $sentData['nin'];
    $url2 = 'https://api.korapay.com/merchant/api/v1/identities/ng/nin';
    $url = 'https://sandbox.dojah.io/api/v1/kyc/nin';

    $payload = json_encode([
        "id" => $bvnId,
        "verification_consent" => true
    ]);
// echo $payload;exit;
    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer sk_test_pWX6VFsZGttymWaaCrEtxF5NqzNP3ZAujuYaLC1Q'
    ];

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => $headers,
    ]);

    $response = curl_exec($curl);
    $error = curl_error($curl);

    curl_close($curl);

    if ($error) {
        return (object)[
            'status' => 'error',
            'message' => $error
        ];
    }

    $decoded = json_decode($response);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return (object)[
            'status' => 'error',
            'message' => 'Invalid JSON response',
            'raw' => $response
        ];
    }

    echo $response;
}

public function verifyNIN() {
    $sentData = $this->getData();
    $nin = $sentData['nin'];

    $url = 'https://api.dojah.io/api/v1/kyc/nin?nin=' . urlencode($nin);

    $headers = [
        'AppId: 684c19f37793ffd7fc7292cf',
        'Authorization: prod_sk_FkMYag6OzfVGrOEOOSAwjN4mk'
    ];

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => $headers,
    ]);

    $response = curl_exec($curl);
    $error = curl_error($curl);

    curl_close($curl);

    if ($error) {
        return (object)[
            'status' => 'error',
            'message' => $error
        ];
    }

    $decoded = json_decode($response);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return (object)[
            'status' => 'error',
            'message' => 'Invalid JSON response',
            'raw' => $response
        ];
    }

    echo $response;
}

    
    public function verifyBVN2($bvnId) {
    $url = 'https://api.korapay.com/merchant/api/v1/identities/ng/bvn';

    $payload = json_encode([
        "id" => $bvnId,
        "verification_consent" => true
    ]);

    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer sk_test_pWX6VFsZGttymWaaCrEtxF5NqzNP3ZAujuYaLC1Q'
    ];

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => $headers,
    ]);

    $response = curl_exec($curl);
    $error = curl_error($curl);

    curl_close($curl);

    if ($error) {
        return (object)[
            'status' => 'error',
            'message' => $error
        ];
    }

    $decoded = json_decode($response);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return (object)[
            'status' => 'error',
            'message' => 'Invalid JSON response',
            'raw' => $response
        ];
    }

    return $decoded;
}


public function verifyBVN($bvnId) {
    $url = 'https://api.dojah.io/api/v1/kyc/bvn/full?bvn=' . urlencode($bvnId);

    $headers = [
        'AppId: 6856f7b57349ef8c33b5a6e4',
        'Authorization: prod_sk_FkMYag6OzfVGrOEOOSAwjN4mk'
    ];

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => $headers,
    ]);

    $response = curl_exec($curl);
    $error = curl_error($curl);

    curl_close($curl);

    if ($error) {
        return (object)[
            'status' => 'error',
            'message' => $error
        ];
    }

    $decoded = json_decode($response);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return (object)[
            'status' => 'error',
            'message' => 'Invalid JSON response',
            'raw' => $response
        ];
    }

    return $decoded;
}


 public function validIdentity()
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
    //   echo json_encode($sentData);exit;
      $data = array(
     
          "bvn" => trim($sentData["bvn"]),
          "email" => $userData->email,
          "user_id" =>  $userData->user_id

      );
      
    //   echo json_encode($data);exit;
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
    $bvnData = $this->verifyBVN($data['bvn']);
  
  $user = $this->userModel->getUser($userData->user_id);
 
//   echo json_encode($bvnData);exit;
//   echo json_encode($user);exit;
  
  
  $fname = strtolower($bvnData->entity->first_name);
  $lname = strtolower($bvnData->entity->last_name);
  $mname = strtolower($bvnData->entity->middle_name);
  
  $fname2 = strtolower($user->firstName);
  $lname2 = strtolower($user->lastName);
  $mname2 = strtolower($user->middleName);
  
//   echo $fname;
//   echo $fname2; 
  
  
  
  if($fname === $fname2 || $lname === $lname2 || $mname === $mname2)
  {
       if ($this->userModel->validAccountBvn($data)) {
          
       $res = json_encode(array(
              'status' => true,
              'message' => 'BVN validated'
          ));
          http_response_code(200);
          print_r($res);
          exit;
       


      } else {
          
          $res = json_encode(array(
              'status' => false,
              'message' => 'validation failed'
          ));
          http_response_code(404);
          print_r($res);
          exit;
      }
  }else{
        $res = json_encode(array(
              'status' => false,
              'message' =>"bvn name dosn't match"
          ));
          http_response_code(404);
          print_r($res);
          exit;
  }

     



  }
  
  
  
  
  
 public function validaccountx()
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
    //   echo json_encode($sentData);exit;
      $data = array(
          "nin" => $userData->nin_no,
          "email" => $userData->email,
          "user_id" =>  $userData->user_id

      );
      
    //   echo json_encode($data);exit;
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


   $ress2 = $this->createVirtualAcc();
$ress = json_decode($ress2);

// Defensive check
if (
    isset($ress->statusCode, $ress->message, $ress->data) &&
    $ress->statusCode === "90000" &&
    strtolower($ress->message) === "success" &&
    isset($ress->data->account, $ress->data->accountName, $ress->data->bankName)
) {
    if ($this->userModel->virtualUpdate($ress->data, $data)) {
        $res = json_encode([
            'status' => true,
            'message' => 'edit profile successful',
            'data' => $ress->data
        ]);
        http_response_code(200);
        echo $res;
        exit;
    } else {
        $res = json_encode([
            'status' => false,
            'message' => 'failed to edit profile'
        ]);
        http_response_code(400);
        echo $res;
        exit;
    }
} else {
    $response = [
        'status' => false,
        'message' => 'virtual account failed',
        'data' => $ress
    ];
    http_response_code(404);
    echo json_encode($response);
    exit;
}

       



  
      


  }
  
    
      public function implementTransfer2()
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
  $loginData = $this->getData();
  if (!isset($loginData['bank_code']) || !isset($loginData['amount']) || !isset($loginData['account'])) {
    $response = array(
      'status' => 'false',

      'message' => 'Enter post fields',

    );

    print_r(json_encode($response));
    exit;
  }
   
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   
    


    $data = [
      'amount'=> $loginData['amount'],
      'payfor'=> $loginData['payfor'],
      'accountname'=> $loginData['accountName'],
      'bankcode'=> $loginData['bank_code'],
      'bankname'=> $loginData['bank_name'],
      'accountNo'=> $loginData['account'],  
      'email_err'=> '',
      'amount_err'=> '',
      'payfor_err'=> ''
    ];
    // print_r($data);
    //  print_r(json_encode($data));
    if (empty($data['bankcode'])) {
      $response = array(
        'status' => 'false',
  
        'message' => 'Enter bank code details',
  
      );
  
      print_r(json_encode($response));
      exit;
    } else {
      if (strlen($data['bankcode']) != 6) {
        $response = array(
          'status' => 'false',
    
          'message' => 'Enter bank code details',
    
        );
    
        print_r(json_encode($response));
        exit;
      }
    }
    if (empty($data['accountNo'])) {
      $response = array(
        'status' => 'false',
  
        'message' => 'Enter payfor details',
  
      );
  
      print_r(json_encode($response));
      exit;
    } else {
      if (strlen($data['accountNo']) != 10) {
        $response = array(
          'status' => 'false',
    
          'message' => 'Enter bank account number',
    
        );
    
        print_r(json_encode($response));
        exit;
      }
    }
    if (empty($data['amount'])) {
      $response = array(
        'status' => 'false',
  
        'message' => 'Enter amount details',
  
      );
  
      print_r(json_encode($response));
      exit;
    }else {
      if ($data['amount'] <= 9) {
        $response = array(
          'status' => 'false',
    
          'message' => 'Enter valid amount ',
    
        );
        print_r(json_encode($response));
        exit;
      }
    }

  if (empty($data['email_err']) && empty($data['amount_err'] )) {
    
 
      $amount = $data['amount'];
      $charges = 50;
      $apicharges = 30;
      $admincharges = 20;
      $settlement = [
        'amount'=> $admincharges,
        'r_tag'=> "veluxpay",
        'r_id' =>"veluxef3a34902c79abd2f4838cdad5872312",
      ];
      
      
        $amount += $charges ;
    
      if ($this->reportModel->findUserByEmail($userData->email)) {
        $url =  "https://services2.vpay.africa/api/service/v1/query/transfer/outbound";
        $urli =  "https://services2.vpay.africa/api/service/v1/query/merchant/login";
        $urle =  "https://services2.vpay.africa/api/service/v1/query/bank/list/show";
        $urlu =  "https://services2.vpay.africa/api/service/v1/query/lookup/nuban";
        $key2 = "dfaae40b-6457-4c77-932c-6b0ac6733e8a";
    
        $password = "Wesson1234$";
        $names = "samueldickson06@gmail.com" ;
        $email = $userData->email;
        $ref = "velux1-".time().mt_rand(0,9999999);
        $name = $userData->full_name;
        $tag = $userData->user_tag;
        $userId = $userData->veluxite_id;
        $ydata =  [
          "username"=> $names,
          "password"=> $password,
        ];
        
        $header = [
          "Content-Type: application/json",
          "publicKey: ".$key2,
        ];
   
        $token = $this->reportModel->getVpayToken();
       $paydata =  [
          "amount"=> $data['amount'],
          "transaction_ref"=> $ref,
          "remark"=> $data['payfor'],
          "bank_code"=> $data['bankcode'],
          "nuban"=>$data['accountNo'],
        ];
     
         $headers = [
          "Content-Type: application/json",
          "publicKey: ".$key2,
          "b-access-token: ".$token
        ]; 
        
        $pay_string = (json_encode($paydata));
        
        // Full names to compare
$fullName1 = strtolower($userData->full_name);
$fullName2 = strtolower($data['accountname']);
// Split each full name into parts
$parts1 = explode(' ', $fullName1);
$parts2 = explode(' ', $fullName2);
// $parts3 = explode(' ', $fullName3);

// Count variable to keep track of the number of matches
$matches = 0;

// Check if each part of the first full name exists in the other full names
foreach ($parts1 as $part) {
    if (in_array($part, $parts2)) {
        $matches++;
    }
}

// $text = [
//     "username" => $fullName1,
//     "bankname" => $fullName2,
//     "data" => $matches
//     ];
    
// print_r(json_encode($text));
// exit;
                    if($matches >= 2){
        if( $this->reportModel->checkAccount($tag, $userId, $amount)){
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        
        curl_setopt($ch, CURLOPT_POST, TRUE);
        
        curl_setopt($ch, CURLOPT_POSTFIELDS, $pay_string);
       
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        curl_close($ch);
        }else{
              $response = array(
            'status' => 'false',
      
            'message' => 'insuficient funds',
      
          );
          print_r(json_encode($response));
          exit;
        }
        
        
                    }else{
                          $response = array(
            'status' => 'false',
      
            'message' => "bank name dosn't match",
      
          );
          print_r(json_encode($response));
              $title = "Withdrawal Unsuccessful";
        $body = "Hi " . $userData->full_name . "! Your withdrawal was unsuccessful because two or more parts of your full name do not match. Please verify your details and try again.";
   
        $this->sendPushToUser($title, $body, $userData->fcmtoken);
          exit;
                        
                    }
         $transferData = [
           "fullname" => $name,
           "Tag" => $tag,
           "veluxite_id" => $userId,
           "bank_code" => $data['bankcode'],
           "bank_name" => $data['bankname'],
           "accname" => $data['accountname'],
           "amount"=> $amount,
           "tr_id" => $ref
         ];
        $res = json_decode($response);
        
         if ($res->status === true) {
          $this->reportModel->payVeluxiteAdmin($settlement);
               
         if ($this->reportModel->withdrawFunds($transferData)) {
             
          $response = array(
            'status' => 'true',
            'message' => 'Transaction successful', 
            'message2' =>($transferData), 
          );
          print_r(json_encode($response));
          // Format the amount with commas based on its length
$formattedAmount = number_format($amount);
        $title = "Withdrawal Successful 💰";
$body = "Hi " . $userData->full_name . "! Your withdrawal of ₦" . $formattedAmount . " was successful. Check your bank account for the funds.";
$this->sendPushToUser($title, $body, $userData->fcmtoken);
          exit;
        }else {
            $response = array(
              'status' => 'false',
        
              'message' => 'transaction successful',
        
            );
            print_r(json_encode($response));
            exit;
          }
        }else {
          $response = array(
            'status' => 'false',
      
            'message' => 'Transaction Failed',
            "data"=> $transferData
      
          );
          print_r(json_encode($response));
          // Format the amount with commas based on its length
$formattedAmount = number_format($amount);
             $title = "Withdrawal Failed ðŸ’°";
        $body = "Hi " . $userData->full_name . "! Your withdrawal of â‚¦" . $formattedAmount . " failed. Please check back later, Thank you.";
        $this->sendPushToUser($title, $body, $userData->fcmtoken);

          exit;
        }
               }else {
                    $res = [
                      'status' => 401,
                      'message' => 'Wrong email',
                      'data' => $data
                    ];
                    print_r(json_encode($res));
                    exit;
                  }
                  } else {
                    $res = [
                      'status' => 401,
                      'message' => 'incomplete form',
                      'data' => $data
                    ];
                    print_r(json_encode($res));
                  }
            }else {
              $res = [
                'status' => 404,
                'message' => 'Bad Request method',
              ];
              print_r(json_encode($res));
            }
}
// public function respondAndExit($data)
// {
//     print_r($data);
//     exit;
// }


public function implementTransfer()
{
    try {
        $userData = $this->RouteProtecion();
    } catch (UnexpectedValueException $e) {
        echo json_encode([
            'status' => false,
            'message' => $e->getMessage()
        ]);
        exit;
    }

    $loginData = $this->getData();

    // Validate input
    $requiredFields = ['bank_code', 'amount', 'account', 'accountName', 'payfor'];
    foreach ($requiredFields as $field) {
        if (empty($loginData[$field])) {
            echo json_encode([
                'status' => false,
                'message' => "Missing field: $field"
            ]);
            exit;
        }
    }

    if (strlen($loginData['bank_code']) !== 6) {
        echo json_encode([
            'status' => false,
            'message' => 'Invalid bank code length'
        ]);
        exit;
    }

    if (strlen($loginData['account']) !== 10) {
        echo json_encode([
            'status' => false,
            'message' => 'Invalid account number length'
        ]);
        exit;
    }

    if ($loginData['amount'] <= 9) {
        echo json_encode([
            'status' => false,
            'message' => 'Enter a valid amount (greater than ₦9)'
        ]);
        exit;
    }

    // Setup transfer details
    $amount = (int) $loginData['amount'];
    // $charges = 50;
    $totalAmount = $amount ;

    $ref = "payking-" . time() . mt_rand(1000, 9999);

    $token = $this->hydrogenToken();

    $balance = $this->getUserBalance2();
            if ($balance->savings < $amount ) {
                $res = (array(
                    "status_code" => 401,
                    "status" => false,
                    "message" => "Insufficient Balance"
                ));

                $this->handleResponse($res);
                exit;
            }

    // Prepare HydrogenPay payload
    $payload = [
        "amount" => $amount,
        "narration" => $loginData['payfor'],
        "beneficiaryAccount" => $loginData['account'],
        "beneficiaryName" => $loginData['accountName'],
        "clientReference" => $ref,
        "beneficiaryBankCode" => $loginData['bank_code'],
        "callBack" => "https://webhook-test.com/d62bb766a9c9df7965d7a763e35997e4"
    ];

    $headers = [
        "XAppKey: SK_TEST_749d490144105f572938a7ea27aff8b3",
        "Content-Type: application/json",
        "Authorization: Bearer ".$token // Replace this dynamically from token function
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => "https://api.hydrogenpay.com/walletservice/api/v1/FundsTransfer/initiate-transfer",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => $headers,
    ]);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        echo json_encode([
            'status' => false,
            'message' => 'cURL Error: ' . $error
        ]);
        exit;
    }

    // $res = json_decode($response);
    
    $res = [
    "statusCode" => 90000,
    "message" => "Fund transfer was successful",
    "data" => [
        "id" => "00000000-0000-0000-0000-000000000000",
        "amount" => 30000.00000,
        "transactionId" => "07b86138-c819-4586-b5a0-a5a10869ffdb",
        "otpNumber" => "07033102077",
        "debitWalletId" => "f6a7096f-ff7a-4bff-4b95-08dcd88a49bb",
        "currency" => "NGN",
        "transactionDate" => "2024-10-02T11:50:16.5419623Z",
        "transactionStatus" => "Successful",
        "clientReference" => "NewBANKesmo"
    ]
];
$user = $this->userModel->getUser($userData->user_id);
   

    if ($res['message'] == "Fund transfer was successful" && $res['statusCode'] === 90000) {
       
        $transferData = [
            "fullname" => $user->fullname,
            "username" => $userData->uname,
            "user_id" => $userData->user_id,
            "email"=> $userData->email,
            "bank_code" => $loginData['bank_code'],
            "bank_name" => $loginData['bank_name'],
            "accname" => $loginData['accountName'],
            "accountNo" => $loginData['account'],
            "amount" => $totalAmount,
            "tr_id" => $ref,
            "message" => $res['message']
        ];

        if ($this->transactionModel->withdrawFunds($transferData)) {
           
 $formattedAmount = number_format($amount);
            $title = "Withdrawal Successful 💰";
            $body =  "Hi {$user->fullname}! Your withdrawal of ₦{$formattedAmount} was successful.";
               
            // $this->sendPushToUser($title, $body, $userData->fcmtoken);
            $data = [
                'header' => $title,
                'text' => $body,
                'user_id' => $transferData['user_id'],
                'img' => ''
                ];
            $this->setNotificationsxx($data);
            echo json_encode([
                'status' => true,
                'message' => 'Transaction successful',
                'details' => $transferData
            ]);
            exit;
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Transfer successful, but failed to log transaction'
            ]);
            exit;
        }
    } else {
        echo json_encode([
            'status' => false,
            'message' => 'Transfer failed',
            'apiResponse' => $res
        ]);
        exit;
    }
}



public function lohinvpay(){
     $password = "Wesson1234$";
        $names = "samueldickson06@gmail.com";
         $key2 = "dfaae40b-6457-4c77-932c-6b0ac6733e8a";
        $urli = "https://services2.vpay.africa/api/service/v1/query/merchant/login";
      $ydata = [
            "username" => $names,
            "password" => $password,
        ];

        $header = [
            "Content-Type: application/json",
            "publicKey: " . $key2,
        ];
        $token = $this->reportModel->getVpayToken();
        echo $token;
}
public function loginV(){
$url = 'https://services2.vpay.africa/api/service/v1/query/merchant/login';

$headers = array(
    'Content-Type: application/json',
    'publicKey: dfaae40b-6457-4c77-932c-6b0ac6733e8a',
);

$data = array(
    'username' => 'samueldickson06@gmail.com',
    'password' => 'Wesson1234$',
);

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // You may need to remove this line in a production environment

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo 'cURL Error: ' . curl_error($ch);
} else {
    echo $response;
}

curl_close($ch);

}

public function getBillers($billerName) {
    $url = 'https://api.hydrogenpay.com/sb/resellerservice/api/Biller/reseller-my-billers?ResellerId=2e994141-c7ca-44fa-70b3-08dd6ce0ffd5';
    // echo 'hello';exit;
    $token = $this->hydrogenLogin();

    $ch = curl_init();

    // Set cURL options
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => [
            //  'XAppKey: SK_TEST_749d490144105f572938a7ea27aff8b3',
            "Authorization: Bearer ".$token
        ],
        CURLOPT_TIMEOUT => 30,  // Timeout to prevent long waits
    ]);

    // Execute request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    $res = json_decode($response);
// echo $response ;exit;
    // Close cURL session
    curl_close($ch);

    // Handle errors
    if ($error) {
        return ['status' => false, 'message' => "cURL Error: $error"];
    }

    if ($httpCode !== 200) {
        return ['status' => false, 'message' => "Request failed with HTTP Code $httpCode", 'response' => json_decode($response, true)];
    }

    foreach ($res->data->result as $biller) {
        // print_r(json_encode($res->data->result[0])); exit;
        if (strcasecmp($biller->billerName, $billerName) === 0) {
            // return (['status' => true, 'billerSystemId' => $biller->billerSystemId]) ;
            return $biller->billerSystemId;
        }
    }

    // If no match found
    return ['status' => false, 'message' => "Biller not found"];
}


public function getElectBillers($billerName) {
    $token = $this->hydrogenLogin();

    // Build URL with dynamic $billerName
    $query = http_build_query([
        'ResellerId' => '2e994141-c7ca-44fa-70b3-08dd6ce0ffd5',
        'currentpage' => 1,
        'pageSize' => 100,
        'Search' => "Electricity",
        'BillerCategorySystemIds' => '06304920-e368-4a49-90b9-08dc96f03362'
    ]);

    $url = "https://api.hydrogenpay.com/sb/resellerservice/api/Biller/reseller-my-billers?$query";

    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer $token"
        ],
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    echo $response;exit;
    curl_close($ch);

    if ($error) {
        return ['status' => false, 'message' => "cURL Error: $error"];
    }

    if ($httpCode !== 200) {
        return ['status' => false, 'message' => "Request failed with HTTP Code $httpCode", 'response' => json_decode($response, true)];
    }

    $res = json_decode($response);

    if (!isset($res->data->result) || !is_array($res->data->result)) {
        return ['status' => false, 'message' => "Invalid API response", 'response' => $res];
    }

    foreach ($res->data->result as $biller) {
        if (strcasecmp($biller->billerName, $billerName) === 0) {
            return $biller->billerSystemId;
        }
    }

    return ['status' => false, 'message' => "Biller not found"];
}


public function hydrogenLogin()
{
    $url = 'https://api.hydrogenpay.com/sb/resellerservice/api/ResellerAuth/login';

    $data = json_encode([
        'username' => 'mail@paykingweb.com',
        // 'username' => 'hensley@paykingweb.com',
        'Password' => 'Passw0rd###',
        // 'Password' => 'V^p7zIRwCU60',
    ]);
// Username:

// Password:

// hensley@paykingweb.com

// V^p7zIRwCU60
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'], // Send data as raw JSON
        CURLOPT_SSL_VERIFYPEER => true,  // Ensure secure SSL connection
        CURLOPT_TIMEOUT => 30,           // Prevent long waits
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);

    curl_close($ch);

    if ($error) {
        echo json_encode(['status' => false, 'message' => "cURL Error: $error"]);
    }

    if ($httpCode !== 200) {
        echo json_encode(['status' => false, 'message' => "Request failed with HTTP Code $httpCode", 'response' => json_decode($response, true)]);
    }

    $res = json_decode($response);
    $token = $res->data->token;
    return $token;
    // echo json_encode(['status' => true, 'response' => ($res)]);
}
public function getCableTvProductCodes($billerSystemId, $token) {
    $url2= "https://api.hydrogenpay.com/sb/resellerservice/api/BillPayment/cabletv/product-codes?BillerSystemId=$billerSystemId";
    $url = "https://api.hydrogenpay.com/sb/resellerservice/api/BillPayment/cabletv/{$billerSystemId}/product-codes?page=1";

    $headers = [
        "Authorization: Bearer $token",
        "Content-Type: application/json"
    ];

    $response = $this->makeGetRequest($url2, $headers);
    return json_decode($response, true);
}
public function getCableTvAddonCodes($billerSystemId, $token) {
    $url = "https://api.hydrogenpay.com/sb/resellerservice/api/BillPayment/cabletv/{$billerSystemId}/addon-codes?page=1";

    $headers = [
        "Authorization: Bearer $token",
        "Content-Type: application/json"
    ];

    $response = $this->makeGetRequest($url, $headers);
    return json_decode($response, true);
}



public function processCableTvSubscription() {
    
      try {
            $userData = $this->RouteProtecion();
        } catch (UnexpectedValueException $e) {
            $res = [
                'status' => 401,
                'message' => $e->getMessage(),
            ];
            http_response_code(404);
            print_r(json_encode($res));
            exit;
        }
    
    // echo "heller";exit;
$loginData = $this->getData();
     $serviceID = $jsonData['serviceID'] ?? '';
        $tkn = $this->hydrogenToken();
        $amount = $jsonData['amount'] ?? '';
        $phone = $jsonData['phone'] ?? '';
        $requestID = $this->generateNineDigitValue();
        $url = 'https://api.hydrogenpay.com/sb/resellerservice/api/VtuPayment/purchase';
        $network = strtoupper($this->getPhoneNetworkProvider($phone, $tkn)).' AIRTIME';
        $biller = $this->getBillers($network);
        
        
        
        
        
         $biller = $this->getBillers('GLO');
        $productCode;
        $addonCode;
        
    $payload = [
        "billerSystemId" => $billerSystemId,
        "smartCardNumber" => $loginData['card_no'],
        "totalAmount" => $loginData['amount'],
        "productCode" => $productCode,
        "addonCode" => $addonCode,
        "productMonthsPaidFor" => $loginData['duration'],
        "clientReference" => $this->generateNineDigitValue()
    ];

    return $this->purchaseCableTv($payload, $token);
}


public function makeGetRequestd($url, $headers) {
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

public function makePostRequestd($url, $headers, $body) {
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

public function purchaseCableTv($payload, $token) {
    $url = "https://api.hydrogenpay.com/sb/resellerservice/api/BillPayment/cabletv/purchase";

    $headers = [
        "Authorization: Bearer $token",
        "Content-Type: application/json"
    ];

    $response = $this->makePostRequest($url, $headers, json_encode($payload));
    return json_decode($response, true);
}






    public function fetchCryptoTransactions() {
        
          try {
            $userData = $this->RouteProtecion();
        } catch (UnexpectedValueException $e) {
            $res = [
                'status' => 401,
                'message' => $e->getMessage(),
            ];
            http_response_code(404);
            print_r(json_encode($res));
            exit;
        }

    $url = "https://veluxswap.onrender.com/transaction/ad7bfebc-1347-4465-aedc-0c804484e734";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getHeaders());
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    print_r(($response)) ;
}
    public function getCryptoRates() {
        
  
    $url = "https://veluxswap.onrender.com/rates";

    $ch = curl_init($url);
   curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getHeaders());
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    
      $data = json_decode($response, true);
            $fData = array_filter($data, function ($value) {
                return $value !== null;
            });
    $paykingCharge = $this->transactionModel->appsettings();
    $fData['sellRate'] = $fData['sellRate'] - $paykingCharge;
    print_r(json_encode($fData)) ;
}



    public function fetchCryptotr() {
        
          try {
            $userData = $this->RouteProtecion();
            } catch (UnexpectedValueException $e) {
                $res = [
                    'status' => 401,
                    'message' => $e->getMessage(),
                ];
                http_response_code(404);
                print_r(json_encode($res));
                exit;
            }
$loginData = $this->getData();
    $url = "https://veluxswap.onrender.com/transaction/".$loginData['tr_id'];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getHeaders());
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

  $data = json_decode($response, true);
            $fData = array_filter($data['transaction'], function ($value) {
                return $value !== null;
            });
            $paykingCharge = $this->transactionModel->appsettings();
            
            $fData['email'] = $userData->email;
            $fData['user_id'] = $userData->user_id;
            $fData['payking_rate'] = $fData['rate_at_trade'] - $paykingCharge;
            $fData['payking_amount_to_pay'] = $fData['payking_rate'] * $fData['amount_in_dollar'];
            // echo json_encode($fData);exit;
      if($response)
            {
                  $res = (array(
                    "payment_status" => $fData['status'],
                    "pay_amount" => $fData['payking_amount_to_pay'],
                    "payment_id" => $fData['transaction_id'],
                    "pay_address" => $fData['wallet_address'],
                    "message" => $fData['message'],
                    
                ));

                print_r(json_encode($res));
                exit;
            }else{
                  $res = (array(
                    "status_code" => 401,
                    "status" => false,
                    "message" => "failed to store record"
                ));

                $this->handleResponse($res);
                exit;
            }
            
}

private function getHeaders() {
    $apiKey = VELUXSWAPKEY;
    return [
        "x-api-key: $apiKey",
        "Content-Type: application/json"
    ];
}


public function createSellTransaction() {
    
      try {
            $userData = $this->RouteProtecion();
        } catch (UnexpectedValueException $e) {
            $res = [
                'status' => 401,
                'message' => $e->getMessage(),
            ];
            http_response_code(404);
            print_r(json_encode($res));
            exit;
        }

    $loginData = $this->getData();
    $data = [
    "bank_account" => "3158707407",
    "bank_name" => "First Bank",
    "bank_account_name" => "Osaro Godwin",
    "amount_in_dollar" => $loginData['amount'],
    "currency" => "usdt"
];
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
    
    $url = "https://veluxswap.onrender.com/transaction/create/sell";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getHeaders());
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_POST, true);

    $response = curl_exec($ch);
    curl_close($ch);

    // echo ($response);
    
    // return json_decode($response, true);
     $data = json_decode($response, true);
            $fData = array_filter($data['transaction'], function ($value) {
                return $value !== null;
            });
            $paykingCharge = $this->transactionModel->appsettings();
            
            $fData['email'] = $userData->email;
            $fData['user_id'] = $userData->user_id;
            $fData['payking_rate'] = $fData['rate_at_trade'] - $paykingCharge;
            $fData['payking_amount_to_pay'] = $fData['payking_rate'] * $fData['amount_in_dollar'];
            // echo json_encode($fData);exit;
            
            if($this->transactionModel->creatPayment($fData))
            {
                   $res = (array(
                    "payment_status" => $fData['status'],
                    "pay_amount" => $fData['payking_amount_to_pay'],
                    "payment_id" => $fData['transaction_id'],
                    "pay_address" => $fData['wallet_address'],
                    "amount_in_dollar" => $loginData['amount']
                    
                ));

                print_r(json_encode($res));
                exit;
            }else{
                  $res = (array(
                    "status_code" => 401,
                    "status" => false,
                    "message" => "failed to store record"
                ));

                $this->handleResponse($res);
                exit;
            }
            
           

}

public function fetchGiftcardTransactions() {
      try {
            $userData = $this->RouteProtecion();
        } catch (UnexpectedValueException $e) {
            $res = [
                'status' => 401,
                'message' => $e->getMessage(),
            ];
            http_response_code(404);
            print_r(json_encode($res));
            exit;
        }

    $url = "https://veluxswap.onrender.com/giftcard";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getHeaders());
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    echo ($response);
}
public function fetchGiftcardtr() {
      try {
            $userData = $this->RouteProtecion();
        } catch (UnexpectedValueException $e) {
            $res = [
                'status' => 401,
                'message' => $e->getMessage(),
            ];
            http_response_code(404);
            print_r(json_encode($res));
            exit;
        }
        
        $loginData = $this->getData();

    $url = "https://veluxswap.onrender.com/giftcard/".$loginData['tr_id'];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getHeaders());
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    // echo ($response);
    
     $data = json_decode($response, true);
            $fData = array_filter($data['transaction'], function ($value) {
                return $value !== null;
            });
            $paykingCharge = $this->transactionModel->appsettings();
            
            $fData['email'] = $userData->email;
            $fData['user_id'] = $userData->user_id;
            $fData['payking_rate'] = $fData['rate_at_trade'] - $paykingCharge;
            $fData['payking_amount_to_pay'] = $fData['payking_rate'] * $fData['amount_in_dollar'];
            // echo json_encode($fData);exit;
      if($response)
            {
                  $res = (array(
                    "payment_status" => $fData['status'],
                    "pay_amount" => $fData['payking_amount_to_pay'],
                    "payment_id" => $fData['transaction_id'],
                    "type" => $fData['type'],
                    "message" => $fData['message'],
                    
                ));

                print_r(json_encode($res));
                exit;
            }else{
                  $res = (array(
                    "status_code" => 401,
                    "status" => false,
                    "message" => "failed to store record"
                ));

                $this->handleResponse($res);
                exit;
            }
}

public function createGiftcardTransaction() {
      try {
            $userData = $this->RouteProtecion();
        } catch (UnexpectedValueException $e) {
            $res = [
                'status' => 401,
                'message' => $e->getMessage(),
            ];
            http_response_code(404);
            print_r(json_encode($res));
            exit;
        }
$loginData = $this->getData();
    $url = "https://veluxswap.onrender.com/giftcard";
    $data = [
    "bank_account" => "0123456789",
    "bank_name" => "Bank Name",
    "bank_account_name" => "Account Name",
    "amount_in_dollar" => $loginData['amount'],
    "cardType" => $loginData['type'],
    "image_1_url" => $_FILES['image_1_url'],
    "image_2_url" => $_FILES['image_2_url']
];

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

      $new_image_names = [];

      $extensions = ["jpeg", "png", "jpg"];
      $types = ["image/jpeg", "image/jpg", "image/png"];

      $image_fields = ['image_1_url', 'image_2_url'];

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
      
      
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getHeaders());
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_POST, true);

    $response = curl_exec($ch);
    curl_close($ch);

    // echo ($response);
    
      $data = json_decode($response, true);
            $fData = array_filter($data['gift'], function ($value) {
                return $value !== null;
            });
            $paykingCharge = $this->transactionModel->appsettings();
            
            $fData['email'] = $userData->email;
            $fData['user_id'] = $userData->user_id;
            $fData['payking_rate'] = $fData['rate_at_trade'] - $paykingCharge;
            $fData['payking_amount_to_pay'] = $fData['payking_rate'] * $fData['amount_in_dollar'];
            // echo json_encode($fData);exit;
            
              if($this->transactionModel->creatPayment2($fData))
            {
                   $res = (array(
                    "payment_status" => $fData['status'],
                    "pay_amount" => $fData['payking_amount_to_pay'],
                    "payment_id" => $fData['transaction_id'],
                    // "pay_address" => $fData['wallet_address'],
                    "type" => $fData['type'],
                    // "pay_address" => $fData['wallet_address'],
                    
                ));

                print_r(json_encode($res));
                exit;
            }else{
                  $res = (array(
                    "status_code" => 401,
                    "status" => false,
                    "message" => "failed to store record"
                ));

                $this->handleResponse($res);
                exit;
            }
            
            
}


public function cardDeposite()
{
     try {
            $userData = $this->RouteProtecion();
        } catch (UnexpectedValueException $e) {
            $res = [
                'status' => 401,
                'message' => $e->getMessage(),
            ];
            http_response_code(404);
            print_r(json_encode($res));
            exit;
        }

        $loginData = $this->getData();
        
        // echo 'hello';
        
        
    $url = 'https://api.hydrogenpay.com/bepay/api/v1/merchant/initiate-payment';

    $data = json_encode([
        'customerName' => $userData->uname,
        'amount' => $loginData['amount'],
        'email' => $userData->email,
        'callback' => 'https://hydrogenpay.com',
    ]);
    // echo $data;exit;
$token = "SK_TEST_749d490144105f572938a7ea27aff8b3";

$curl = curl_init();


curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://api.hydrogenpay.com/bepay/api/v1/merchant/initiate-payment',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>$data,
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json',
    'Authorization: Bearer '.$token,
   
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;exit;
   $res = $this->makePostReques992($url, $data, $token);
    echo $res;exit;
    // return $token;
    echo json_encode(['status' => true, 'response' => ($res)]);
}

public function hydrogenToken_x()
{
    $url = 'https://api.hydrogenpay.com/walletservice/api/Auth/token';

    // $data = ([
    //     'username' => 'mail@paykingweb.com',
    //     // 'username' => 'hensley@paykingweb.com',
    //     'Password' => 'Passw0rd###ggg',
    //     // 'Password' => 'V^p7zIRwCU60',
    // ]);
$loginPayload = json_encode([
    "username" => "hensley@paykingweb.com",
    "password" => "Hug@6411#"
]);

$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => 'https://api.hydrogenpay.com/walletservice/api/Auth/token',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => $loginPayload,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
    ],
]);

$response = curl_exec($curl);

if (curl_errno($curl)) {
    echo 'Curl error: ' . curl_error($curl);
    curl_close($curl);
    return;
}

echo $response;
}


public function hydrogenLogin_x()
{
    $url = 'https://api.hydrogenpay.com/sb/resellerservice/api/ResellerAuth/login';

    $data = json_encode([
        'username' => 'mail@paykingweb.com',
        // 'username' => 'hensley@paykingweb.com',
        'Password' => 'Passw0rd###',
        // 'Password' => 'V^p7zIRwCU60',
    ]);
// Username:

// Password:

// hensley@paykingweb.com

// V^p7zIRwCU60
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'], // Send data as raw JSON
        CURLOPT_SSL_VERIFYPEER => true,  // Ensure secure SSL connection
        CURLOPT_TIMEOUT => 30,           // Prevent long waits
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);

    curl_close($ch);

    if ($error) {
        echo json_encode(['status' => false, 'message' => "cURL Error: $error"]);
    }

    if ($httpCode !== 200) {
        echo json_encode(['status' => false, 'message' => "Request failed with HTTP Code $httpCode", 'response' => json_decode($response, true)]);
    }

    $res = json_decode($response);
    $token = $res->data->token;
    // return $token;
    echo json_encode(['status' => true, 'response' => ($res)]);
}


    public function inAppTransfer()
    {
        try {
            $userData = $this->RouteProtecion();
        } catch (UnexpectedValueException $e) {
            $res = [
                'status' => 401,
                'message' => $e->getMessage(),
            ];
            http_response_code(404);
            print_r(json_encode($res));
            exit;
        }

        $loginData = $this->getData();

        if (!isset($loginData['username']) || !isset($loginData['amount']) || !isset($loginData['secret_key'])) {
            $response = [
                'status' => 'false',
                'message' => 'Enter input details',
            ];
            http_response_code(404);
            print_r(json_encode($response));
            exit;
        }

        $tagname = $loginData['username'];
        $amount = $loginData['amount'];
        // $key = $loginData['secret_key'];
        //  if(password_verify($key, $userData->security_key)){
             
        //      if($userData->used_key >= 1){
        //           $response = [
        //         'status' => false,
        //         'message' => "key already used",
        //     ];
        //     http_response_code(404);
        //     print_r(json_encode($response));
        //     exit;
        //      }
        //     $this->userModel->security_use($userData->user_id, $userData->email);
        // }else{
        //      $response = [
        //         'status' => false,
        //         'message' => "can't go through this point",
        //     ];
        //     http_response_code(404);
        //     print_r(json_encode($response));
        //     exit;
        // }
        

        if (empty($tagname)) {
            $response = [
                'status' => 'false',
                'message' => 'Please enter username',
            ];
            http_response_code(404);
            print_r(json_encode($response));
            exit;
        }

        if (empty($amount) || $amount < 10) {
            $response = [
                'status' => false,
                'message' => 'Invalid amount (minimum amount is 10)',
            ];
            http_response_code(404);
            print_r(json_encode($response));
            exit;
        }


        // Check if it's a main user
        $receiver = $this->transactionModel->findUserByTagName($tagname);
        if (!$receiver) {
            $response = [
                'status' => 401,
                'message' => 'No user found with username ' . $tagname,
            ];
            http_response_code(404);
            print_r(json_encode($response));
            exit;
        }
        $userDatax = $this->userModel->findUserByEmail_det2($userData->email);
        $r_userDatax = $this->userModel->findUserByEmail_det2($receiver->email);
        $userid = $userData->user_id;
        $tag = $userData->uname;
        $charges = 2;
        $tr_id = 'VTRIP' . md5(date('ymdihs') . rand(900, 3000));

        if ($this->transactionModel->checkAccount($userData->email, $userid, $amount)) {
            // $settlement = [
            //     'amount' => $charges,
            //     'tagname' => "adminn"
            // ];

            $datax = [
                's_tag' => $userData->uname,
                's_name' => $userDatax->fullname,
                'r_tag' => $tagname,
                's_id' => $userid,
                'r_id' => $receiver->user_id,
                'r_name' => $r_userDatax->fullname,
                'tr_status' => 'successful',
                'tr_id' => $tr_id,
                'amount' => $amount,
                's_e' => $userData->email,
                'r_e' => $receiver->email
            ];
// print_r(json_encode($datax));exit;
         
            // Define notification messages
            // Format the amount with commas based on its length
            $formattedAmount = number_format($amount);
            // Construct the payment sent message
            $title1 = "Payment Sent 🚀";
            $body1 = "Hi " . $userData->uname . "! You've successfully sent ₦" . $formattedAmount . " to " . $tagname;

            // Construct the payment received message
            $title2 = "Payment Received 🎉";
            $body2 = "Hey " . $tagname . "! You've received ₦" . $formattedAmount . " from " . $userData->uname . ". Check it out!";

            // Send push notification for the sender
            // $this->sendPushToUser($title1, $body1, $userData->fcmtoken);
                  $data = [
                'header' => $title1,
                'text' => $body1,
                'user_id' => $userData->user_id,
                'img' => ''
                ];
            $this->setNotificationsxx($data);
              $data = [
                'header' => $title2,
                'text' => $body2,
                'user_id' => $receiver->user_id,
                'img' => ''
                ];
            $this->setNotificationsxx($data);
            // $this->sendPushToUser($title2, $body2, $receiverFCMToken);

            // Both notifications were sent successfully

            if ($this->transactionModel->inAppTransfer($datax)) {
                $this->transactionModel->accountUpdate($datax);
                $res = [
                    'status' => 200,
                    'message' => 'Transaction successful',
                    'sender' =>  $userDatax->fullname,
                    'receiver' =>  $r_userDatax->fullname,
                    'receiver_tag' => $tagname,
                    'amount' => $amount,
                ];
                print_r(json_encode($res));
                ////adddded


            } else {
                $res = [
                    'status' => 401,
                    'message' => 'Incomplete params',
                ];
                http_response_code(404);
                print_r(json_encode($res));
            }
        } else {
            $res = [
                'status' => 401,
                'message' => 'Insufficient funds',
            ];
            http_response_code(404);
            print_r(json_encode($res));
            // Format the amount with commas based on its length
            // $formattedAmount = number_format($amount);
            $title1 = "Payment Failed ❌";
            $body = "Hi " . $userData->uname . "! Your payment of ₦" . $formattedAmount . " to " . $tagname . " failed due to insufficient funds.";
        $data = [
                'header' => $title1,
                'text' => $body,
                'user_id' => $userData->user_id,
                'img' => ''
                ];
            $this->setNotificationsxx($data);
            // Send push notification for the sender
            // $this->sendPushToUser($title1, $body, $userData->fcmtoken);
        }
    }

    public function campaignTransfer()
    {
        try {
            $userData = $this->RouteProtecion();
        } catch (UnexpectedValueException $e) {
            $res = [
                'status' => 401,
                'message' => $e->getMessage(),
            ];
            http_response_code(404);
            print_r(json_encode($res));
            exit;
        }

        $loginData = $this->getData();

        if (!isset($loginData['campaign_id']) || !isset($loginData['amount']) ) {
            $response = [
                'status' => 'false',
                'message' => 'Enter input details',
            ];
            http_response_code(404);
            print_r(json_encode($response));
            exit;
        }

        $tagname = $loginData['campaign_id'];
        $amount = $loginData['amount'];
        // $key = $loginData['secret_key'];
        //  if(password_verify($key, $userData->security_key)){
             
        //      if($userData->used_key >= 1){
        //           $response = [
        //         'status' => false,
        //         'message' => "key already used",
        //     ];
        //     http_response_code(404);
        //     print_r(json_encode($response));
        //     exit;
        //      }
        //     $this->userModel->security_use($userData->user_id, $userData->email);
        // }else{
        //      $response = [
        //         'status' => false,
        //         'message' => "can't go through this point",
        //     ];
        //     http_response_code(404);
        //     print_r(json_encode($response));
        //     exit;
        // }
        

        if (empty($tagname)) {
            $response = [
                'status' => 'false',
                'message' => 'Please enter campaign id',
            ];
            http_response_code(404);
            print_r(json_encode($response));
            exit;
        }

        if (empty($amount) || $amount < 10) {
            $response = [
                'status' => false,
                'message' => 'Invalid amount (minimum amount is 10)',
            ];
            http_response_code(404);
            print_r(json_encode($response));
            exit;
        }


        // Check if it's a main user
        $receiver = $this->transactionModel->findCampaignById($tagname);
        if (!$receiver) {
            $response = [
                'status' => 401,
                'message' => 'No Campaign found with Id ' . $tagname,
            ];
            http_response_code(404);
            print_r(json_encode($response));
            exit;
        }
        $userDatax = $this->userModel->findUserByEmail_det2($userData->email);
        $r_userDatax = $this->userModel->findUserByEmail_det2($receiver->email);
        $userid = $userData->user_id;
        $tag = $userData->uname;
        $charges = 2;
        $tr_id = 'VTRIP' . md5(date('ymdihs') . rand(900, 3000));

        if ($this->transactionModel->checkAccount($userData->email, $userid, $amount)) {
            // $settlement = [
            //     'amount' => $charges,
            //     'tagname' => "adminn"
            // ];

            $datax = [
                's_tag' => $userData->uname,
                's_name' => $userDatax->fullname,
                'r_tag' => $r_userDatax->uname,
                's_id' => $userid,
                'r_id' => $receiver->user_id,
                'r_name' => $r_userDatax->fullname,
                'tr_status' => 'successful',
                'tr_id' => $tr_id,
                'amount' => $amount,
                's_e' => $userData->email,
                'r_e' => $receiver->email,
                'campaign_id' => $tagname
            ];
// print_r(json_encode($datax));exit;
         
            // Define notification messages
            // Format the amount with commas based on its length
            // $formattedAmount = number_format($amount);
            // Construct the payment sent message
            $title1 = "Payment Sent 🚀";
            $body1 = "Hi " . $userData->uname . "! You've successfully sent ₦" . $formattedAmount . " to " . $r_userDatax->uname;

            // Construct the payment received message
            $title2 = "Payment Received 🎉";
            $body2 = "Hey " . $r_userDatax->uname . "! You've received ₦" . $formattedAmount . " from " . $userData->uname . ". Check it out!";

            // Send push notification for the sender
            // $this->sendPushToUser($title1, $body1, $userData->fcmtoken);
                  $data = [
                'header' => $title1,
                'text' => $body1,
                'user_id' => $userData->user_id,
                'img' => ''
                ];
          
              $data2 = [
                'header' => $title2,
                'text' => $body2,
                'user_id' => $receiver->user_id,
                'img' => ''
                ];
         
            // $this->sendPushToUser($title2, $body2, $receiverFCMToken);

            // Both notifications were sent successfully

            if ($this->transactionModel->campaignTransfer($datax)) {
                $this->transactionModel->campaignUpdate($datax);
                  $this->setNotificationsxx($data);
                     $this->setNotificationsxx($data2);
                $res = [
                    'status' => 200,
                    'message' => 'Transaction successful',
                    'sender' =>  $userDatax->fullname,
                    'receiver' =>  $r_userDatax->fullname,
                    'receiver_tag' => $tagname,
                    'amount' => $amount,
                ];
                print_r(json_encode($res));
                ////adddded


            } else {
                $res = [
                    'status' => 401,
                    'message' => 'Incomplete params',
                ];
                http_response_code(404);
                print_r(json_encode($res));
            }
        } else {
            $res = [
                'status' => 401,
                'message' => 'Insufficient funds',
            ];
            http_response_code(404);
            print_r(json_encode($res));
            // Format the amount with commas based on its length
            $formattedAmount = number_format($amount);
            $title1 = "Payment Failed ❌";
            $body = "Hi " . $userData->uname . "! Your payment of ₦" . $formattedAmount . " to " . $tagname . " failed due to insufficient funds.";
        $data = [
                'header' => $title1,
                'text' => $body,
                'user_id' => $userData->user_id,
                'img' => ''
                ];
            $this->setNotificationsxx($data);
            // Send push notification for the sender
            // $this->sendPushToUser($title1, $body, $userData->fcmtoken);
        }
    }
    
    
    
   public function ticketTransfer()
{
    try {
        $userData = $this->RouteProtecion(); // Logged-in user
    } catch (UnexpectedValueException $e) {
        http_response_code(401);
        echo json_encode(['status' => 401, 'message' => $e->getMessage()]);
        exit;
    }

    $loginData = $this->getData();

    if (
        !isset($loginData['event_id']) ||
        !isset($loginData['amount']) ||
        !isset($loginData['recipient_ids']) ||
        !is_array($loginData['recipient_ids'])
    ) {
        http_response_code(400);
        echo json_encode(['status' => false, 'message' => 'event_id, amount, and recipient_ids are required']);
        exit;
    }

    $eventId = $loginData['event_id'];
    $amount = (int)$loginData['amount'];
    $recipientIds = $loginData['recipient_ids'];

    if (empty($eventId)) {
        http_response_code(400);
        echo json_encode(['status' => false, 'message' => 'event_id is required']);
        exit;
    }

    if ($amount < 10) {
        http_response_code(400);
        echo json_encode(['status' => false, 'message' => 'Minimum amount is ₦10']);
        exit;
    }

    if (count($recipientIds) < 1 || count($recipientIds) > 5) {
        http_response_code(400);
        echo json_encode(['status' => false, 'message' => 'You can send to between 1 and 5 users']);
        exit;
    }

    // Check event existence
    $event = $this->transactionModel->findEventById($eventId);
    if (!$event) {
        http_response_code(404);
        echo json_encode(['status' => 404, 'message' => "No event found with ID $eventId"]);
        exit;
    }

    $totalAmount = $amount * count($recipientIds);
    $senderEmail = $userData->email;
    $senderId = $userData->user_id;
    $senderTag = $userData->uname;
    $senderDetails = $this->userModel->findUserByEmail_det2($senderEmail);

    if (!$this->transactionModel->checkAccount($senderEmail, $senderId, $totalAmount)) {
        $formatted = number_format($totalAmount);
        $this->setNotificationsxx([
            'header' => 'Payment Failed ❌',
            'text' => "Hi $senderTag! Your payment of ₦$formatted failed due to insufficient funds.",
            'user_id' => $senderId,
            'img' => ''
        ]);
        http_response_code(402);
        echo json_encode(['status' => false, 'message' => 'Insufficient funds']);
        exit;
    }

    $success = [];
    $failed = [];

    foreach ($recipientIds as $recipientId) {
        $recipient = $this->transactionModel->findUserById($recipientId);
        if (!$recipient) {
            $failed[] = $recipientId;
            continue;
        }

        $tr_id = 'VTRIP' . md5(date('ymdihs') . rand(900, 3000));

        $datax = [
            's_tag' => $senderTag,
            's_name' => $senderDetails->fullname,
            'r_tag' => $recipient->uname,
            's_id' => $senderId,
            'r_id' => $recipient->user_id,
            'r_name' => $recipient->fullname,
            'tr_status' => 'successful',
            'tr_id' => $tr_id,
            'amount' => $amount,
            's_e' => $senderEmail,
            'r_e' => $recipient->email,
            'campaign_id' => $eventId
        ];

        if ($this->transactionModel->ticketTransfer($datax)) {
            $this->transactionModel->ticketUpdate($datax);

            $formattedAmount = number_format($amount);

            $this->setNotificationsxx([
                'header' => 'Ticket Paid 🚀',
                'text' => "Hi $senderTag! You paid ₦$formattedAmount for a ticket for {$recipient->uname}",
                'user_id' => $senderId,
                'img' => ''
            ]);

            $this->setNotificationsxx([
                'header' => 'Ticket Received 🎟',
                'text' => "Hi {$recipient->uname}, $senderTag paid for your ticket to $eventId 🎉",
                'user_id' => $recipient->user_id,
                'img' => ''
            ]);

            $success[] = $recipient->uname;
        } else {
            $failed[] = $recipient->uname;
        }
    }

    echo json_encode([
        'status' => 200,
        'message' => 'Ticket transactions processed',
        'event_id' => $eventId,
        'successful' => $success,
        'failed' => $failed
    ]);
}


    
    
    
    
    public function ticketTransferSelf()
    {
        try {
            $userData = $this->RouteProtecion();
        } catch (UnexpectedValueException $e) {
            $res = [
                'status' => 401,
                'message' => $e->getMessage(),
            ];
            http_response_code(404);
            print_r(json_encode($res));
            exit;
        }

        $loginData = $this->getData();

        if (!isset($loginData['event_id']) || !isset($loginData['amount']) ) {
            $response = [
                'status' => 'false',
                'message' => 'Enter input details',
            ];
            http_response_code(404);
            print_r(json_encode($response));
            exit;
        }

        $tagname = $loginData['event_id'];
        $amount = $loginData['amount'];
        // $key = $loginData['secret_key'];
        //  if(password_verify($key, $userData->security_key)){
             
        //      if($userData->used_key >= 1){
        //           $response = [
        //         'status' => false,
        //         'message' => "key already used",
        //     ];
        //     http_response_code(404);
        //     print_r(json_encode($response));
        //     exit;
        //      }
        //     $this->userModel->security_use($userData->user_id, $userData->email);
        // }else{
        //      $response = [
        //         'status' => false,
        //         'message' => "can't go through this point",
        //     ];
        //     http_response_code(404);
        //     print_r(json_encode($response));
        //     exit;
        // }
        

        if (empty($tagname)) {
            $response = [
                'status' => 'false',
                'message' => 'Please enter event id',
            ];
            http_response_code(404);
            print_r(json_encode($response));
            exit;
        }

        if (empty($amount) || $amount < 10) {
            $response = [
                'status' => false,
                'message' => 'Invalid amount (minimum amount is 10)',
            ];
            http_response_code(404);
            print_r(json_encode($response));
            exit;
        }


        // Check if it's a main user
        $receiver = $this->transactionModel->findEventById($tagname);
        if (!$receiver) {
            $response = [
                'status' => 401,
                'message' => 'No event found with Id ' . $tagname,
            ];
            http_response_code(404);
            print_r(json_encode($response));
            exit;
        }
        $userDatax = $this->userModel->findUserByEmail_det2($userData->email);
        $r_userDatax = $this->userModel->findUserByEmail_det2($receiver->email);
        $userid = $userData->user_id;
        $tag = $userData->uname;
        $charges = 2;
        $tr_id = 'VTRIP' . md5(date('ymdihs') . rand(900, 3000));

        if ($this->transactionModel->checkAccount($userData->email, $userid, $amount)) {
            // $settlement = [
            //     'amount' => $charges,
            //     'tagname' => "adminn"
            // ];

            $datax = [
                's_tag' => $userData->uname,
                's_name' => $userDatax->fullname,
                'r_tag' => $r_userDatax->uname,
                's_id' => $userid,
                'r_id' => $receiver->user_id,
                'r_name' => $r_userDatax->fullname,
                'tr_status' => 'successful',
                'tr_id' => $tr_id,
                'amount' => $amount,
                's_e' => $userData->email,
                'r_e' => $receiver->email,
                'campaign_id' => $tagname
            ];
// print_r(json_encode($datax));exit;
         
            // Define notification messages
            // Format the amount with commas based on its length
            $formattedAmount = number_format($amount);
            // Construct the payment sent message
            $title1 = "Payment Sent 🚀";
            $body1 = "Hi " . $userData->uname . "! You've successfully sent ₦" . $formattedAmount . " to " . $r_userDatax->uname;

            // Construct the payment received message
            $title2 = "Payment Received 🎉";
            $body2 = "Hey " . $r_userDatax->uname . "! You've received ₦" . $formattedAmount . " from " . $userData->uname . ". Check it out!";

            // Send push notification for the sender
            // $this->sendPushToUser($title1, $body1, $userData->fcmtoken);
                  $data = [
                'header' => $title1,
                'text' => $body1,
                'user_id' => $userData->user_id,
                'img' => ''
                ];
          
              $data2 = [
                'header' => $title2,
                'text' => $body2,
                'user_id' => $receiver->user_id,
                'img' => ''
                ];
         
            // $this->sendPushToUser($title2, $body2, $receiverFCMToken);

            // Both notifications were sent successfully

            if ($this->transactionModel->ticketTransfer($datax)) {
                $this->transactionModel->ticketUpdate($datax);
                  $this->setNotificationsxx($data);
                     $this->setNotificationsxx($data2);
                $res = [
                    'status' => 200,
                    'message' => 'Transaction successful',
                    'sender' =>  $userDatax->uname,
                    'receiver' =>  $r_userDatax->uname,
                    'receiver_tag' => $tagname,
                    'amount' => $amount,
                ];
                print_r(json_encode($res));
                ////adddded


            } else {
                $res = [
                    'status' => 401,
                    'message' => 'Incomplete params',
                ];
                http_response_code(404);
                print_r(json_encode($res));
            }
        } else {
            $res = [
                'status' => 401,
                'message' => 'Insufficient funds',
            ];
            http_response_code(404);
            print_r(json_encode($res));
            // Format the amount with commas based on its length
            $formattedAmount = number_format($amount);
            $title1 = "Payment Failed ❌";
            $body = "Hi " . $userData->uname . "! Your payment of ₦" . $formattedAmount . " to " . $tagname . " failed due to insufficient funds.";
        $data = [
                'header' => $title1,
                'text' => $body,
                'user_id' => $userData->user_id,
                'img' => ''
                ];
            $this->setNotificationsxx($data);
            // Send push notification for the sender
            // $this->sendPushToUser($title1, $body, $userData->fcmtoken);
        }
    }



        



    
        public function getElectricity()
    {
      $data =  $this->transactionModel->getElectricity();

        print_r(json_encode($data));
        exit;
    }
  
        public function getUserBalance2()
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
        // print_r( $userData);
      $user_id = $userData->user_id;
      $userDatax = $this->userModel->findUserByEmail_det2($userData->email);
      $accountDetails = $this->userModel->getAccountDetails($userDatax->account_id, $user_id);
      return  $accountDetails ;
    }
    
    public function getAirtel()
    {
      $data =  $this->transactionModel->getAirtelDataPlan();

        print_r(json_encode($data));
        exit;
    }
 public function getUserTransactions(){
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
            http_response_code(404);
            $res = [
                'status' => 401,
                'message' =>  $e->getMessage(),
            ];
            print_r(json_encode($res));
            exit;
        }
        $userid = $userData->user_id;
        $tag = $userData->uname;
        
       $trn =  $this->transactionModel->getUserTransactions($userid, $tag);
        print_r(json_encode($trn));
        exit;
       
        }
 public function getAllTransactions(){
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
            http_response_code(404);
            $res = [
                'status' => 401,
                'message' =>  $e->getMessage(),
            ];
            print_r(json_encode($res));
            exit;
        }
 if($userData->roleID == 6  || $userData->roleID == 7 ){
          
      }else{$res = [
          'status' => 401,
          'message' =>  "you are not permitted",
        ];
        http_response_code(401);
        print_r(json_encode($res));
        exit;
      }
        
       $trn =  $this->transactionModel->getAllTransactions();
        print_r(json_encode($trn));
        exit;
       
        }
 public function getCryptoTransactions(){
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
            http_response_code(404);
            $res = [
                'status' => 401,
                'message' =>  $e->getMessage(),
            ];
            print_r(json_encode($res));
            exit;
        }
        $userid = $userData->user_id;
        $tag = $userData->email;
        
       $trn =  $this->transactionModel->getCryptoTransactions($userid, $tag);
        print_r(json_encode($trn));
        exit;
       
        }
 public function getUserTransactionsByID(){
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
            http_response_code(404);
            $res = [
                'status' => 401,
                'message' =>  $e->getMessage(),
            ];
            print_r(json_encode($res));
            exit;
        }
          $loginData = $this->getData();
        $tr_id = $loginData['tr_id'];
        $userid = $userData->user_id;
        $tag = $userData->uname;
        
       $trn =  $this->transactionModel->getUserTransactionsxx($userid, $tag, $tr_id);
        print_r(json_encode($trn));
        exit;
       
        }
 public function getCryptoTransactionsByID(){
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
            http_response_code(404);
            $res = [
                'status' => 401,
                'message' =>  $e->getMessage(),
            ];
            print_r(json_encode($res));
            exit;
        }
          $loginData = $this->getData();
        $tr_id = $loginData['p_id'];
        $userid = $userData->user_id;
        $tag = $userData->email;
        
       $trn =  $this->transactionModel->getCryptoTransactionsByID($userid, $tag, $tr_id);
        print_r(json_encode($trn));
        exit;
       
        }
    public function getEti()
    {
      $data =  $this->transactionModel->getEtisalatDataPlans();

        print_r(json_encode($data));
        exit;
    }
    public function getGlo()
    {
      $data =  $this->transactionModel->getGloDataPlan();

        print_r(json_encode($data));
        exit;
    }
    public function getMtn()
    {
      $data =  $this->transactionModel->getMtnDataPlan();

        print_r(json_encode($data));
        exit;
    }
  

    public function saveMtn()
    {
         $url = "https://api.hydrogenpay.com/sb/resellerservice/api/Bouquet";
        $token = $this->hydrogenToken();
        $res = $this->makeGetRequestJWT($url, $token);
        
        // echo $res;
       $data = json_decode($res, true);

        // Check if JSON decoding was successful and if 'data' key exists
        // if (json_last_error() !== JSON_ERROR_NONE) {
        //     echo json_encode(['status' => false, 'message' => 'Invalid JSON response']);
        //     exit;
        // }
        
        if (!isset($data['data']) || !is_array($data['data'])) {
            echo json_encode(['status' => false, 'message' => 'No data found']);
            exit;
        }
        
        // Filter items where billerId is "9Mobile-DATA"
        $filteredData = array_values(array_filter($data['data'], function ($item) {
            return isset($item['billerId']) && $item['billerId'] === 'MTN-DATA';
        }));
        
        // Return filtered JSON response
        echo json_encode(['status' => true, 'plan_type' => 'MTN', 'data' => $filteredData], JSON_PRETTY_PRINT);
        // foreach ($urls as $type => $url) {
        //     $res = $this->makeGetRequest($url);
        //     $decodedResponse = json_decode($res, true);

        //     // Check if the response contains 'plans'
        //     if (isset($decodedResponse['plans'])) {
        //         foreach ($decodedResponse['plans'] as $plan) {
        //             $dataToSave = [
        //                 'dataType' => $type,
        //                 'displayName' => $plan['displayName'],
        //                 'value' => $plan['value'],
        //                 'price' => $plan['price']
        //             ];

        //             // Save each plan using the saveMtnDataPlan function
        //             if (!$this->transactionModel->saveMtnDataPlan($dataToSave)) {
        //                 $allSaved = false; // Track failure if any insert/update fails
        //             }
        //         }
        //     }
        // }

        // // Set the response based on the outcome of all operations
        // $response = [
        //     'status_code' => $allSaved ? 200 : 404,
        //     'status' => $allSaved,
        //     'message' => $allSaved ? 'MTN data plans saved' : 'Failed to save some MTN data plans'
        // ];

        // $this->handleResponse($response); // Send the response once
    }

    public function saveGlo()
    {
        $url = "https://api.hydrogenpay.com/sb/resellerservice/api/Bouquet";
        $token = $this->hydrogenToken();
        $res = $this->makeGetRequestJWT($url, $token);
        // echo $res; exit;
        // echo $res;
       $data = json_decode($res, true);

        // Check if JSON decoding was successful and if 'data' key exists
        // if (json_last_error() !== JSON_ERROR_NONE) {
        //     echo json_encode(['status' => false, 'message' => 'Invalid JSON response']);
        //     exit;
        // }
        
        if (!isset($data['data']) || !is_array($data['data'])) {
            echo json_encode(['status' => false, 'message' => 'No data found']);
            exit;
        }
        
        // Filter items where billerId is "9Mobile-DATA"
        $filteredData = array_values(array_filter($data['data'], function ($item) {
            return isset($item['billerId']) && $item['billerId'] === 'GLO-DATA';
        }));
        
        // Return filtered JSON response
        echo json_encode(['status' => true, 'plan_type' => 'Glo', 'data' => $filteredData], JSON_PRETTY_PRINT);
        // foreach (['sme', 'data'] as $type) {
        //     if (isset($data[$type]['plans'])) {
        //         foreach ($data[$type]['plans'] as $plan) {
        //             $dataToSave = [
        //                 'dataType' => $type,
        //                 'displayName' => $plan['displayName'],
        //                 'value' => $plan['value'],
        //                 'price' => $plan['price']
        //             ];

        //             // Insert or update the database with each plan
        //             $this->transactionModel->saveGloDataPlan($dataToSave);
        //         }
        //     }
        // }

        // $response = [
        //     'status_code' =>  200,
        //     'status' => true,
        //     'message' => 'Glo data plans saved'
        // ];
        // $this->handleResponse($response);
    }

    public function saveAirtel()
    {
  
        $url = "https://api.hydrogenpay.com/sb/resellerservice/api/Bouquet";
        $token = $this->hydrogenToken();
        $res = $this->makeGetRequestJWT($url, $token);
        
        // echo $res;
       $data = json_decode($res, true);

        // Check if JSON decoding was successful and if 'data' key exists
        // if (json_last_error() !== JSON_ERROR_NONE) {
        //     echo json_encode(['status' => false, 'message' => 'Invalid JSON response']);
        //     exit;
        // }
        
        if (!isset($data['data']) || !is_array($data['data'])) {
            echo json_encode(['status' => false, 'message' => 'No data found']);
            exit;
        }
        
        // Filter items where billerId is "9Mobile-DATA"
        $filteredData = array_values(array_filter($data['data'], function ($item) {
            return isset($item['billerId']) && $item['billerId'] === 'Airtel-DATA';
        }));
        
        // Return filtered JSON response
        echo json_encode(['status' => true, 'plan_type' => 'Airtel', 'data' => $filteredData], JSON_PRETTY_PRINT);
        // Process both sets of plans
        // foreach (['sme', 'cg'] as $type) {
        //     if (isset($data[$type]['plans'])) {
        //         foreach ($data[$type]['plans'] as $plan) {
        //             $dataToSave = [
        //                 'dataType' => $type,
        //                 'displayName' => $plan['displayName'],
        //                 'value' => $plan['value'],
        //                 'saveEti' => $plan['price']
        //             ];

        //             // Insert or update the database with each plan
        //             $this->transactionModel->saveAirtelDataPlan($dataToSave);
        //         }
        //     }
        // }

        // $response = [
        //     'status_code' => 200,
        //     'status' => true,
        //     'message' => 'Airtel data plans saved'
        // ];
        // $this->handleResponse($response);
    }

    public function saveEti()
    {
        $url = "https://api.hydrogenpay.com/sb/resellerservice/api/Bouquet";
        $token = $this->hydrogenToken();
        $res = $this->makeGetRequestJWT($url, $token);
        
        // echo $res;
       $data = json_decode($res, true);

        // Check if JSON decoding was successful and if 'data' key exists
        // if (json_last_error() !== JSON_ERROR_NONE) {
        //     echo json_encode(['status' => false, 'message' => 'Invalid JSON response']);
        //     exit;
        // }
        
        if (!isset($data['data']) || !is_array($data['data'])) {
            echo json_encode(['status' => false, 'message' => 'No data found']);
            exit;
        }
        
        // Filter items where billerId is "9Mobile-DATA"
        $filteredData = array_values(array_filter($data['data'], function ($item) {
            return isset($item['billerId']) && $item['billerId'] === '9Mobile-DATA';
        }));
        
        // Return filtered JSON response
        echo json_encode(['status' => true, 'plan_type' => '9mobile', 'data' => $filteredData], JSON_PRETTY_PRINT);
        // // Check if the response contains 'etisalat_data' and 'plans'
        // if (isset($response) && isset($response['plans'])) {
        //     $dataType = "etisalat_data"; // Set the data type
        //     $allSaved = true; // Variable to track if all inserts were successful


        //     $response = [
        //         'status_code' => $allSaved ? 200 : 404,
        //         'status' => $allSaved,
        //         'message' => $allSaved ? 'Data saved' : 'Failed to save some data'
        //     ];
        // } else {
        //     $response = [
        //         'status_code' => 404,
        //         'status' => false,
        //         'message' => 'No plans data found'
        //     ];
        // }

        // $this->handleResponse($response); // Call handleResponse once at the end
    }
      public function saveGotv()
    {
        $url = "https://gsubz.com/api/plans?service=gotv";
        $res = $this->makeGetRequest($url);
        $response = json_decode($res, true);
        
        
        if (isset($response) && isset($response['list'])) {
            // print_r($res);exit;
            $allSaved = true; // Variable to track if all inserts were successful

            foreach ($response['list'] as $plan) {
                $data = [
                    'displayName' => $plan['display_name'],
                    'value' => $plan['value'],
                    'price' => $plan['price']
                ];

                // Track failure if any insert fails
                if (!$this->transactionModel->saveGotv($data)) {
                    $allSaved = false;
                    break; // Exit loop on first failure if needed
                }
            }

            // Set the final response based on success/failure of all operations
            $response = [
                'status_code' => $allSaved ? 200 : 404,
                'status' => $allSaved,
                'message' => $allSaved ? 'Data saved' : 'Failed to save some data'
            ];
        } else {
            $response = [
                'status_code' => 404,
                'status' => false,
                'message' => 'No plans data found'
            ];
        }

        $this->handleResponse($response); // Call handleResponse once at the end
    }
    public function saveDstv()
    {
        $url = "https://gsubz.com/api/plans?service=dstv";
        $res = $this->makeGetRequest($url);
        
        $response = json_decode($res, true);
        
        
        if (isset($response) && isset($response['list'])) {
          
            $allSaved = true; // Variable to track if all inserts were successful

            foreach ($response['list'] as $plan) {
                $data = [
             
                    'displayName' => $plan['display_name'],
                    'value' => $plan['value'],
                    'price' => $plan['price']
                ];

                // Track failure if any insert fails
                if (!$this->transactionModel->saveDstv($data)) {
                    $allSaved = false;
                    break; // Exit loop on first failure if needed
                }
            }

            // Set the final response based on success/failure of all operations
            $response = [
                'status_code' => $allSaved ? 200 : 404,
                'status' => $allSaved,
                'message' => $allSaved ? 'Data saved' : 'Failed to save some data'
            ];
        } else {
            $response = [
                'status_code' => 404,
                'status' => false,
                'message' => 'No plans data found'
            ];
        }

        $this->handleResponse($response); // Call handleResponse once at the end
    }
     public function getGotv()
    {
      $data =  $this->transactionModel->getGotvPlans();

        print_r(json_encode($data));
        exit;
    }
    public function getDstv()
    {
      $data =  $this->transactionModel->getDstvPlans();

        print_r(json_encode($data));
        exit;
    }
    public function getCryptoCharge()
    {
      $data =  $this->transactionModel->appsettings();

        print_r(json_encode(['status' => true, 'data' =>$data]));
        exit;
    }
    public function getStartime()
    {
      $data =  $this->transactionModel->getStartimePlans();

        print_r(json_encode($data));
        exit;
    }
        public function queryNowPaymentransaction($reqID)
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
            http_response_code(404);
            $res = [
                'status' => 401,
                'message' =>  $e->getMessage(),
            ];
            print_r(json_encode($res));
            exit;
        }
        $url = 'https://api.nowpayments.io/v1/payment/'.$reqID;

            $response =  $this->makeGetRequest($url);

           return $response;
    }
    public function saveStartime()
    {
        $url = "https://gsubz.com/api/plans?service=startimes";
        $res = $this->makeGetRequest($url);

        $response = json_decode($res, true);
        
        
        if (isset($response) && isset($response['list'])) {
         
            $allSaved = true; // Variable to track if all inserts were successful

            foreach ($response['list'] as $plan) {
                $data = [
                    'displayName' => $plan['display_name'],
                    'value' => $plan['value'],
                    'price' => $plan['price']
                ];

                // Track failure if any insert fails
                if (!$this->transactionModel->saveStartime($data)) {
                    $allSaved = false;
                    break; // Exit loop on first failure if needed
                }
            }

            // Set the final response based on success/failure of all operations
            $response = [
                'status_code' => $allSaved ? 200 : 404,
                'status' => $allSaved,
                'message' => $allSaved ? 'Data saved' : 'Failed to save some data'
            ];
        } else {
            $response = [
                'status_code' => 404,
                'status' => false,
                'message' => 'No plans data found'
            ];
        }

        $this->handleResponse($response); // Call handleResponse once at the end
    }
    
    
    
    public function getPhoneNetworkProvider($customerId, $token) {
    $url = "https://api.hydrogenpay.com/sb/resellerservice/api/VtuPayment/get-phone-network-provider?customerId=$customerId";

    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 30, // Set timeout to prevent long waits
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer $token"
        ]
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);

    curl_close($ch);

    if ($error) {
        return json_encode(['status' => false, 'message' => "cURL Error: $error"]);
    }

    if ($httpCode !== 200) {
        return json_encode(['status' => false, 'message' => "Request failed with HTTP Code $httpCode", 'response' => json_decode($response, true)]);
    }
    $res = json_decode($response, true);

    echo ($response);exit;
    
    
}
    
    
    
           public function buyAirtime()
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
            http_response_code(404);
            $res = [
                'status' => 401,
                'message' =>  $e->getMessage(),
            ];
            print_r(json_encode($res));
            exit;
        }
        $balance = $this->getUserBalance2();

    

        $jsonData = $this->getData();
         $key = $jsonData['secret_key'];
         if(password_verify($key, $userData->security_key)){
             
             if($userData->used_key >= 1){
                  $response = [
                'status' => false,
                'message' => "key already used",
            ];
            http_response_code(404);
            print_r(json_encode($response));
            exit;
             }
            $this->userModel->security_use($userData->user_id, $userData->email);
        }else{
             $response = [
                'status' => false,
                'message' => "can't go through this point",
            ];
            http_response_code(404);
            print_r(json_encode($response));
            exit;
        }
        $serviceID = $jsonData['serviceID'] ?? '';
        $tkn = $this->hydrogenToken();
        $amount = $jsonData['amount'] ?? '';
        $phone = $jsonData['phone'] ?? '';
        $requestID = $this->generateNineDigitValue();
        $url = 'https://api.hydrogenpay.com/sb/resellerservice/api/VtuPayment/purchase';
        $network = strtoupper($this->getPhoneNetworkProvider($phone, $tkn)).' AIRTIME';
        $biller = $this->getBillers($network);
        // print_r($network) ;exit;
            $data = [
                // "resellerId" => "2e994141-c7ca-44fa-70b3-08dd6ce0ffd5",
                "billerSystemId" => $biller,
                "amount" => $amount,
                "phoneNumber" => $phone,
                "productType" => "AIRTIME",
                "clientReference" => $requestID
            ];
            
            // echo json_encode($data); exit;


        foreach ($data as $key => $value) {
            if (is_string($value) && $value === "") {
                $res = (array(
                    "status_code" => 401,
                    "status" => false,
                    "message" => "Incomplete params: " . $key . " is required."
                ));

                $this->handleResponse($res);
                exit;
            }
        }
        
            if ($balance->savings < $data['amount'] ) {
                $res = (array(
                    "status_code" => 401,
                    "status" => false,
                    "message" => "Insufficient Balance"
                ));

                $this->handleResponse($res);
                exit;
            }
            $response =  $this->makePostReques992($url, $data, $tkn);
            $res = json_decode($response);
// echo ($response); exit;
            //           {
            //     "statusCode": 90000,
            //     "message": "success",
            //     "description": "successful",
            //     "data": {
            //         "status": "200",
            //         "message": "successful",
            //         "clientReference": "553464746"
            //     },
            //     "transactionStatus": "SUCCESSFUL",
            //     "transactionRef": "553464746"
            // }
            // $resx = json_encode($responsex);
            // $res = json_decode($resx);
            $datetime = new DateTime();
            $date = $datetime->format('m-d-Y g:i A'); 
            if ($res->data->status == "200" && $res->message == "success") {
               $saveData = [
                'user_id' => $userData->user_id,
                'email' => $userData->email,
                'tr_id' => $res->transactionRef,
                'amount' => $data['amount'],
                'tr_type' => 'Airtime',
                'phone' => $phone,
                'serviceID' => $network,
                'date' => $date,
                'response' => $res->transactionStatus
               ];

               if ($this->transactionModel->buyAirtime($saveData)) {
                $saveData = array_filter($saveData, function ($value, $key) {
                  return $key !== "response";
                }, ARRAY_FILTER_USE_BOTH);
                $res2 = (array(
                    "status_code" => 200,
                    "status" => true,
                    "message" => $res->message,
                    'data' => $saveData
                ));

                $this->handleResponseWithData($res2);
                exit;
               }else {
                $res = (array(
                    "status_code" => 401,
                    "status" => false,
                    "message" => "not successful"
                ));

                $this->handleResponse($res);
                exit;
               }
            }else {
                $res = (array(
                    "status_code" => 401,
                    "status" => false,
                    "message" => "not successful external"
                ));

                $this->handleResponse($res);
                exit;
               }
       
        
    }

       public function getGsubBalance()
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
            http_response_code(404);
            $res = [
                'status' => 401,
                'message' =>  $e->getMessage(),
            ];
            print_r(json_encode($res));
            exit;
        }
        $api = APIKEY;
        $url = 'https://gsubz.com/api/balance/';
        $data = [
            'api' => $api
        ];


        foreach ($data as $key => $value) {
            if (is_string($value) && $value === "") {
                $res = (array(
                    "status_code" => 401,
                    "status" => false,
                    "message" => "Incomplete params: " . $key . " is required."
                ));

                $this->handleResponse($res);
                exit;
            }

            $response =  $this->makePostRequest2($url, $data);

            print_r($response);
            exit;
        }
    }
       public function queryGsubTransaction($reqID)
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
            http_response_code(404);
            $res = [
                'status' => 401,
                'message' =>  $e->getMessage(),
            ];
            print_r(json_encode($res));
            exit;
        }
        $api = APIKEY;
        $url = 'https://gsubz.com/api/verify/';
        $data = [
            'api' => $api,
            'requestID' => $reqID
        ];


        foreach ($data as $key => $value) {
            if (is_string($value) && $value === "") {
                $res = (array(
                    "status_code" => 401,
                    "status" => false,
                    "message" => "Incomplete params: " . $key . " is required."
                ));

                $this->handleResponse($res);
                exit;
            }

            $response =  $this->makePostRequest2($url, $data);

           return $response;
        }
    }
            public function buyData()
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
            http_response_code(404);
            $res = [
                'status' => 401,
                'message' =>  $e->getMessage(),
            ];
            print_r(json_encode($res));
            exit;
        }
        $balance = $this->getUserBalance2();

        $jsonData = $this->getData();
        //  $key = $jsonData['secret_key'];
        //  if(password_verify($key, $userData->security_key)){
             
        //      if($userData->used_key >= 1){
        //           $response = [
        //         'status' => false,
        //         'message' => "key already used",
        //     ];
        //     http_response_code(404);
        //     print_r(json_encode($response));
        //     exit;
        //      }
        //     $this->userModel->security_use($userData->user_id, $userData->email);
        // }else{
        //      $response = [
        //         'status' => false,
        //         'message' => "can't go through this point",
        //     ];
        //     http_response_code(404);
        //     print_r(json_encode($response));
        //     exit;
        // }
         $serviceID = $jsonData['serviceID'] ?? '';
        $tkn = $this->hydrogenToken();
        $amount = $jsonData['amount'] ?? '';
        $bouquetCode = $jsonData['bouquetCode'] ?? '';
        $phone = $jsonData['phone'] ?? '';
        $requestID = $this->generateNineDigitValue();
        $url = 'https://api.hydrogenpay.com/sb/resellerservice/api/VtuPayment/purchase';
        $network = strtoupper($this->getPhoneNetworkProvider($phone, $tkn)).' DATA';
        $biller = $this->getBillers($network);
      $data = [
                // "resellerId" => "2e994141-c7ca-44fa-70b3-08dd6ce0ffd5",
                "billerSystemId" => $biller,
                "amount" => $amount,
                "phoneNumber" => $phone,
                "productType" => "DATA",
                "bouquetCode" => $bouquetCode,
                "clientReference" => $requestID
            ];
            
        // echo json_encode($data); exit;

        foreach ($data as $key => $value) {
            if (is_string($value) && $value === "") {
                $res = (array(
                    "status_code" => 401,
                    "status" => false,
                    "message" => "Incomplete params: " . $key . " is required."
                ));

                $this->handleResponse($res);
                exit;
            }
        }
            if ($balance->savings < $data['amount'] ) {
                $res = (array(
                    "status_code" => 401,
                    "status" => false,
                    "message" => "Insufficient Balance"
                ));

                $this->handleResponse($res);
                exit;
            }

            $response =  $this->makePostReques992($url, $data, $tkn);
            // echo ($response); exit;
            //  $responsex = [
            //     "code" => 200,
            //     "status" => "successful",
            //     "transactionID" => 3375170496,
            //     "amount" => "70",
            //     "phone" => "08055406977",
            //     "serviceID" => "glo_data",
            //     "amountPaid" => 69.45945945946,
            //     "initialBalance" => "401.75",
            //     "finalBalance" => 332.29054054054,
            //     "date" => "2024-11-18T01:38:46+01:00",
            //     "api_response" => "Dear Customer, You have successfully gifted 08055406977 with 200.0MB of Data. Thank you. Sponsor Balance: 763732.7GB."
            // ];
            // $resx = json_encode($responsex);
            $res = json_decode($resx);
            if ($res->code === 200 && $res->status === "successful") {
                $saveData = [
                 'user_id' => $userData->user_id,
                 'email' => $userData->email,
                 'tr_id' => $res->transactionID,
                 'amount' => $res->amount,
                 'tr_type' => 'Data',
                 'phone' => $res->phone,
                 'serviceID' => $res->serviceID,
                 'date' => $res->date,
                 'response' => $res->api_response
                ];
 
                if ($this->transactionModel->buyData($saveData)) {
                 $saveData = array_filter($saveData, function ($value, $key) {
                   return $key !== "response";
                 }, ARRAY_FILTER_USE_BOTH);
                 $res2 = (array(
                     "status_code" => 200,
                     "status" => true,
                     "message" => $res->api_response,
                     'data' => $saveData
                 ));
 
                 $this->handleResponseWithData($res2);
                 exit;
                }else {
                 $res = (array(
                     "status_code" => 401,
                     "status" => false,
                     "message" => "not successful"
                 ));
 
                 $this->handleResponse($res);
                 exit;
                }
             }else{
                  $res = (array(
                     "status_code" => 401,
                     "status" => false,
                     "message" => "not successful external"
                 ));
 
                 $this->handleResponse($res);
                 exit;
             }
        
    }
       public function verifyTr()
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
            http_response_code(404);
            $res = [
                'status' => 401,
                'message' =>  $e->getMessage(),
            ];
            print_r(json_encode($res));
            exit;
        }
        $jsonData = $this->getData();
        $requestID = $jsonData['reqID'] ?? '';


      $response =  $this->queryGsubTransaction($requestID);

            print_r($response);
            exit;
    }
        public function electricityBill()
{
    try {
        // Ensure user authentication
        $userData = $this->RouteProtecion();
    } catch (UnexpectedValueException $e) {
        $res = [
            'status' => 401,
            'message' => $e->getMessage(),
        ];
        http_response_code(401);
        print_r(json_encode($res));
        exit;
    } catch (DomainException $e) {
        http_response_code(401);
        $res = [
            'status' => 401,
            'message' => $e->getMessage(),
        ];
        print_r(json_encode($res));
        exit;
    }
 $balance = $this->getUserBalance2();
    // Extract and validate JSON payload
    $jsonData = $this->getData();
    //  $key = $jsonData['secret_key'];
    //      if(password_verify($key, $userData->security_key)){
             
    //          if($userData->used_key >= 1){
    //               $response = [
    //             'status' => false,
    //             'message' => "key already used",
    //         ];
    //         http_response_code(404);
    //         print_r(json_encode($response));
    //         exit;
    //          }
    //         $this->userModel->security_use($userData->user_id, $userData->email);
    //     }else{
    //          $response = [
    //             'status' => false,
    //             'message' => "can't go through this point",
    //         ];
    //         http_response_code(404);
    //         print_r(json_encode($response));
    //         exit;
    //     }
   $serviceID = $jsonData['serviceID'] ?? '';
        $tkn = $this->hydrogenLogin();
        $amount = $jsonData['amount'] ?? '';
        $bouquetCode = $jsonData['bouquetCode'] ?? '';
        $phone = $jsonData['phone'] ?? '';
        $requestID = $this->generateNineDigitValue();
        $url = 'https://api.hydrogenpay.com/sb/resellerservice/api/BillPayment/electricity/purchase';
        // $network = strtoupper($this->getPhoneNetworkProvider($phone, $tkn)).' DATA';
        // $biller = $this->getElectBillers($network);
        $biller = $jsonData['billerID'];
        $accNo = $jsonData['meterNo'] ?? '';

      $data = [
                // "resellerId" => "2e994141-c7ca-44fa-70b3-08dd6ce0ffd5",
                // "billerSystemId" => "e22168f0-7e0f-4b68-5399-08dc651c6771",
                "billerSystemId" => $biller,
                "amount" => $amount,
                "phoneNumber" => $phone,
                "accountNumber" => $accNo,
                // "bouquetCode" => $bouquetCode,
                "clientReference" => $requestID
            ];
            
        // echo json_encode($data); exit;
    // Validate input data
    foreach ($data as $key => $value) {
        if (is_string($value) && trim($value) === '') {
            $res = [
                "status_code" => 400,
                "status" => false,
                "message" => "Incomplete params: $key is required."
            ];
             echo json_encode($res); exit;
        }
    }
  if ($balance->savings < $data['amount'] ) {
                $res = (array(
                    "status_code" => 401,
                    "status" => false,
                    "message" => "Insufficient Balance"
                ));

                $this->handleResponse($res);
                exit;
            }

            $response =  $this->makePostReques992($url, $data, $tkn);
            
//             {
//     "statusCode": 90000,
//     "message": "Electricity bill payment was successful",
//     "data": {
//         "transactionRef": "830920449",
//         "transactionStatus": "SUCCESSFUL",
//         "data": {
//             "statusCode": "0",
//             "transactionStatus": "success",
//             "transactionReference": "830920449",
//             "transactionMessage": "Topup successful on 39745754943094",
//             "provider_message": "Topup successful on 39745754943094",
//             "tokenCode": "65547325743738458534",
//             "tokenAmount": 0,
//             "amountOfPower": "2.959400620349201 Kwh",
//             "rawOutput": {
//                 "token": "65547325743738458534",
//                 "resetToken": "",
//                 "configureToken": null,
//                 "tokenAmount": 0,
//                 "amountOfPower": "2.959400620349201 Kwh",
//                 "status": "ACCEPTED",
//                 "freeUnits": "0",
//                 "taxAmount": null,
//                 "debtAmount": null,
//                 "totalAmount": "0.0",
//                 "responseCode": "200",
//                 "receiptNumber": "AGWqmb5MwqaZtRZBP1Htjb",
//                 "generatedAmount": "0.0",
//                 "responseMessage": "successful",
//                 "discoExchangeReference": "AGWqmb5MwqaZtRZBP1Htjb",
//                 "vendAmount": "0.0",
//                 "accountBalance": null,
//                 "meterNumber": "12345678911",
//                 "account": "6516853173",
//                 "tariff": "",
//                 "customerName": "testadmin",
//                 "customerPhone": "",
//                 "customerBusinessUnit": "",
//                 "arrearspaid": "0.0",
//                 "rate": "2.959400620349201",
//                 "tenderedAmount": 200,
//                 "mapRefundAmt": 3,
//                 "mapRefundBal": 8681,
//                 "mapRefundUnit": 0.05000000074505806,
//                 "mapRefundTxnRef": "BEDCTEST000024-MAPREF"
//             }
//         }
//     }
// }

    // $resx = json_encode($responsex);
            $res = json_decode($response);

    // Process the response
    if (isset($res->statusCode) && $res->statusCode === 90000 && $res->message === "Electricity bill payment was successful") {
        $saveData = [
            'user_id' => $userData->user_id,
            'email' => $userData->email,
            'tr_id' => $res->data->transactionRef,
            'amount' => $amount,
            'tr_type' => 'Electricity',
            'phone' => $phone,
            'serviceID' => $res->data->data->tokenCode,
            'date' => date('y-m-d:h-i-s'),
            'response' => $res->data->data->transactionMessage
        ];
// echo json_encode($saveData); exit;
        // Save transaction to database
        if ($this->transactionModel->saveTransactionx($saveData)) {
            unset($saveData->response); // Exclude sensitive data from the response
            $res2 = [
                "status_code" => 200,
                "status" => true,
                "message" => $saveData['response'],
                "data" => $saveData
            ];
            $this->handleResponseWithData($res2);
            exit;
        } else {
            $res = [
                "status_code" => 500,
                "status" => false,
                "message" => "Failed to save transaction."
            ];
            $this->handleResponse($res);
            exit;
        }
    } else {
        $ress = json_decode($responsex);
        $res = [
            "status_code" => 400,
            "status" => false,
            "message" => $res->message ?? "Transaction failed.",
            "data"=> $ress
        ];
        $this->handleResponseWithData($res);
        exit;
    }
}

public function verifyPayment()
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
            http_response_code(404);
            $res = [
                'status' => 401,
                'message' =>  $e->getMessage(),
            ];
            print_r(json_encode($res));
            exit;
        }
        $jsonData = $this->getData();
        $pay_id = $jsonData['payment_id'] ?? '';


      $response =  $this->queryNowPaymentransaction($pay_id);

            print_r($response);
            exit;
    }

    
        
    public function createPayment()
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
            http_response_code(404);
            $res = [
                'status' => 401,
                'message' =>  $e->getMessage(),
            ];
            print_r(json_encode($res));
            exit;
        }

        $sentData = $this->getData();
         $key = $sentData['secret_key'];
         if(password_verify($key, $userData->security_key)){
             
             if($userData->used_key >= 1){
                  $response = [
                'status' => false,
                'message' => "key already used",
            ];
            http_response_code(404);
            print_r(json_encode($response));
            exit;
             }
            $this->userModel->security_use($userData->user_id, $userData->email);
        }else{
             $response = [
                'status' => false,
                'message' => "can't go through this point",
            ];
            http_response_code(404);
            print_r(json_encode($response));
            exit;
        }
        $url = 'https://api.nowpayments.io/v1/payment';
        $data = [
            'price_amount' => $sentData['amount'],
            'price_currency' => 'usd',
            'pay_currency' => 'USDTTRC20',
            'is_fee_paid_by_user' => true,
            'is_fixed_rate' => false,
            'ipn_callback_url' => 'https://nowpayments.io',
            'order_id' => $this->generateUniqueId(),
            'order_description'=> 'PAYKING',
            // 'amount_received' => $sentData['amount'] + ($sentData['amount'] * 0.02)
        ];
        foreach ($data as $key => $value) {
            if (is_string($value) && $value === "") {
                $res = (array(
                    "status_code" => 401,
                    "status" => false,
                    "message" => "Incomplete params: " . $key . " is required."
                ));

                $this->handleResponse($res);
                exit;
            }
        }

        $res = $this->makePostRequest4($url, $data);
        
        $res2 = json_decode($res);
        
        if(isset($res2->status) && $res2->status === false && $res2->statusCode != 200)
        {
                  $res = (array(
                    "status_code" => $res2->statusCode,
                    "status" => $res2->status,
                    "message" => $res2->message
                ));

                $this->handleResponse($res);
                exit;
        }else{
           $data = json_decode($res, true);
            $fData = array_filter($data, function ($value) {
                return $value !== null;
            });
            
            $fData['email'] = $userData->email;
            $fData['user_id'] = $userData->user_id;
            
            if($this->transactionModel->creatPayment($fData))
            {
                   $res = (array(
                    "payment_status" => $data['payment_status'],
                    "pay_amount" => $data['pay_amount'],
                    "payment_id" => $data['payment_id'],
                    "pay_address" => $data['pay_address'],
                    
                ));

                print_r(json_encode($res));
                exit;
            }else{
                  $res = (array(
                    "status_code" => 401,
                    "status" => false,
                    "message" => "failed to store record"
                ));

                $this->handleResponse($res);
                exit;
            }
            
           

      }
     
       
    }
    public function getProductCode($billerSystemId, $tkn)
    {
        // echo json_encode($billerSystemId);
          $url2= "https://api.hydrogenpay.com/sb/resellerservice/api/BillPayment/cabletv/product-codes?BillerSystemId=".($billerSystemId)."&pageSize=20";
        $url = "https://api.hydrogenpay.com/sb/resellerservice/api/BillPayment/cabletv/{$billerSystemId}/product-codes?page=1";
        $token = $this->hydrogenLogin();
        $ch = curl_init();
            
            // Set cURL options for GET request
            curl_setopt($ch, CURLOPT_URL, $url2);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer '.$token, // Replace with your actual token
                'Content-Type: application/json',
            ]);
            
            // Execute request and get response
            $response = curl_exec($ch);
            echo $response;exit;
            // Check for errors
            if (curl_errno($ch)) {
                echo 'Curl error: ' . curl_error($ch);
            } else {
                // Decode JSON response
                $data = json_decode($response, true);
                return $response;
            }
            
            // Close cURL
            curl_close($ch);
    }

       public function loginNowP()
    {
        // echo 'hello';exit;
       $res =  $this->NPAuth();
        print_r($res);
    }
    
        public function cableTv22()
{
    try {
        // Ensure user authentication
        $userData = $this->RouteProtecion();
    } catch (UnexpectedValueException $e) {
        $res = [
            'status' => 401,
            'message' => $e->getMessage(),
        ];
        http_response_code(401);
        print_r(json_encode($res));
        exit;
    } catch (DomainException $e) {
        http_response_code(401);
        $res = [
            'status' => 401,
            'message' => $e->getMessage(),
        ];
        print_r(json_encode($res));
        exit;
    }

    // Extract and validate JSON payload
    $jsonData = $this->getData();
    //   $key = $jsonData['secret_key'];
    //     if(password_verify($key, $userData->security_key)){
             
    //          if($userData->used_key >= 1){
    //               $response = [
    //             'status' => false,
    //             'message' => "key already used",
    //         ];
    //         http_response_code(404);
    //         print_r(json_encode($response));
    //         exit;
    //          }
    //         $this->userModel->security_use($userData->user_id, $userData->email);
    //     }else{
    //          $response = [
    //             'status' => false,
    //             'message' => "can't go through this point",
    //         ];
    //         http_response_code(404);
    //         print_r(json_encode($response));
    //         exit;
    //     }
    // $serviceID = $jsonData['serviceID'] ?? '';
    $api = $this->hydrogenLogin();
    // $amount = $jsonData['amount'] ?? '';
    // $phone = $jsonData['phone'] ?? '';
    // $customerID = $jsonData['customerID'] ?? '';
    // $url = 'https://gsubz.com/api/pay/';
    
     $serviceID = $jsonData['serviceID'] ?? '';
        $tkn = $this->hydrogenLogin();
        $amount = $jsonData['amount'] ?? '';
        $cardNo = $jsonData['card_no'] ?? '';
        $type = $jsonData['type'] ?? '';
        $phone = $jsonData['phone'] ?? '';
        $requestID = $this->generateNineDigitValue();
        $url = 'https://api.hydrogenpay.com/sb/resellerservice/api/BillPayment/cabletv/purchase';
        $network = $type.' Subscription on Multichoice Network';
        $biller = $this->getBillers($network);
        // $biller = "";
        $productCode = $jsonData['value'];

 $payload = [
        "billerSystemId" => $biller,
        "smartCardNumber" => $cardNo,
        "totalAmount" => $amount,
        "productCode" => $productCode,
        // "addonCode" => $addonCode,
        "productMonthsPaidFor" => $jsonData['duration'],
        "clientReference" => $this->generateNineDigitValue()
    ];
// print_r(json_encode($payload));exit;
    // Validate input data
    foreach ($payload as $key => $value) {
        if (is_string($value) && trim($value) === '') {
            $res = [
                "status_code" => 400,
                "status" => false,
                "message" => "Incomplete params: $key is required."
            ];
            $this->handleResponse($res);
            exit;
        }
    }

    // Perform the cURL request
    $responsex = $this->makePostRequest2($url, $payload, $api);
    
    echo $responsex;exit;
    // $responsex = [
    //     "code" => 200,
    //     "status" => "TRANSACTION_SUCCESSFUL",
    //     "description" => "TRANSACTION_SUCCESSFUL",
    //     "content" => [
    //         "transactionID" => 3574357094,
    //         "requestID" => "",
    //         "amount" => "1100",
    //         "phone" => "08140558898",
    //         "serviceID" => "kaduna_electric",
    //         "amountPaid" => 1100,
    //         "initialBalance" => "1280",
    //         "finalBalance" => 180,
    //         "image" => "//gsubz.com/uploads/service/1867790347.png",
    //         "fee" => "0",
    //         "serviceName" => "Kaduna Electricity",
    //         "status" => "TRANSACTION_SUCCESSFUL",
    //         "code" => 200,
    //         "description" => "TRANSACTION_SUCCESSFUL",
    //         "date" => "2022-05-03T03:00:59+01:00",
    //         "diplayValue" => null
    //     ],
    //     "gateway" => [
    //         "referrer" => ""
    //     ]
    // ];
    
    $resx = json_encode($responsex);
            $res = json_decode($resx);

    // Process the response
    if (isset($res->code) && $res->code === 200 && $res->status === "TRANSACTION_SUCCESSFUL") {
        $saveData = [
            'user_id' => $userData->user_id,
            'email' => $userData->email,
            'tr_id' => $res->content->transactionID,
            'amount' => $res->content->amount,
            'tr_type' => 'Electricity',
            'phone' => $res->content->phone,
            'serviceID' => $res->content->serviceID,
            'date' => $res->content->date,
            'response' => $res->content->serviceName
        ];

        // Save transaction to database
        if ($this->transactionModel->saveTransactionx($saveData)) {
            unset($saveData->response); // Exclude sensitive data from the response
            $res2 = [
                "status_code" => 200,
                "status" => true,
                "message" => $saveData['response'],
                "data" => $saveData
            ];
            $this->handleResponseWithData($res2);
            exit;
        } else {
            $res = [
                "status_code" => 500,
                "status" => false,
                "message" => "Failed to save transaction."
            ];
            $this->handleResponse($res);
            exit;
        }
    } else {
        $res = [
            "status_code" => 400,
            "status" => false,
            "message" => $res->message ?? "Transaction failed."
        ];
        $this->handleResponse($res);
        exit;
    }
}

public function cableTv()
{
    try {
        $userData = $this->RouteProtecion();
    } catch (UnexpectedValueException | DomainException $e) {
        http_response_code(401);
        print_r(json_encode([
            'status' => 401,
            'message' => $e->getMessage()
        ]));
        exit;
    }

    $jsonData = $this->getData();

    $tkn = $this->hydrogenLogin();
    $amount = $jsonData['amount'] ?? '';
    $cardNo = $jsonData['card_no'] ?? '';
    $type = $jsonData['type'] ?? '';
    $phone = $jsonData['phone'] ?? '';
    $addonCode = $jsonData['addon_code'] ?? ["string"]; // should be an array
    $duration = $jsonData['duration'] ?? 1;
    $requestID = $this->generateNineDigitValue();
    $url = 'https://api.hydrogenpay.com/sb/resellerservice/api/BillPayment/cabletv/purchase';

    $network = $type . ' Subscription on Multichoice Network';
    $biller = $this->getBillers($network);
    $productCode = $jsonData['value'];
    $responseJson = $this->purchaseCableTV2($tkn, $biller, $cardNo, $amount, $productCode, $addonCode, $duration, $phone, $requestID);
    
    // echo $responseJson ; exit;
    
    $response = json_decode($responseJson);
    
//     {
//   "statusCode": 90000,
//   "message": "Operation Successful",
//   "description": "CableTv purchase was successful",
//   "data": {
//     "referenceId": "11a02b2b-c8f4-4032-bfee-a3296d843760",
//     "transactionStatus": "success"
//   },
//   "transactionStatus": "Cable TV Subscription successful",
//   "transactionRef": "11a02b2b-c8f4-4032-bfee-a3296d843760"
// }
    
    if (isset($response->statusCode) && $response->statusCode === 90000 && $response->message === "Operation Successful") {
        $saveData = [
            'user_id' => $userData->user_id,
            'email' => $userData->email,
            'tr_id' => $response->data->transactionRef,
            'amount' => $amount,
            'tr_type' => 'CableTV',
            'phone' => $phone,
            'serviceID' => "",
            'date' => date('y-m-d:h-i-s'),
            'response' => $response->description
        ];

        if ($this->transactionModel->saveTransactionx($saveData)) {
            unset($saveData['response']); // don't return internal note
            $this->handleResponseWithData([
                "status_code" => 200,
                "status" => true,
                "message" => $response->description,
                // "data" => $saveData
            ]);
            exit;
        } else {
            $this->handleResponse([
                "status_code" => 500,
                "status" => false,
                "message" => "Failed to save transaction."
            ]);
            exit;
        }
    } else {
        $this->handleResponse([
            "status_code" => 400,
            "status" => false,
            "message" => $response->description ?? "Transaction failed."
        ]);
        exit;
    }
}

public function purchaseCableTV2($token, $billerSystemId, $smartCardNumber, $totalAmount, $productCode, $addonCodes, $monthsPaid, $phoneNumber, $clientReference) {
    $url = 'https://api.hydrogenpay.com/sb/resellerservice/api/BillPayment/cabletv/purchase';

    $payload = json_encode([
        "billerSystemId" => $billerSystemId,
        "smartCardNumber" => $smartCardNumber,
        "totalAmount" => $totalAmount,
        "productCode" => $productCode,
        "addonCode" => $addonCodes, // should be an array
        "productMonthsPaidFor" => $monthsPaid,
        "phoneNumber" => $phoneNumber,
        "clientReference" => $clientReference
    ]);

    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token
    ];

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => $headers,
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);
    
   

    if ($err) {
        return (object)[
            "status" => "error",
            "message" => "cURL Error: $err"
        ];
    }

    // Attempt to decode the response
    $decoded = json_decode($response);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return (object)[
            "status" => "error",
            "message" => "Invalid JSON response",
            "raw" => $response
        ];
    }

    return $response;
}


public function createVirtualCard($id)
{
    try {
        // Ensure user authentication
        $userData = $this->RouteProtecion();
    } catch (UnexpectedValueException $e) {
        $res = [
            'status' => 401,
            'message' => $e->getMessage(),
        ];
        http_response_code(401);
        print_r(json_encode($res));
        exit;
    } catch (DomainException $e) {
        http_response_code(401);
        $res = [
            'status' => 401,
            'message' => $e->getMessage(),
        ];
        print_r(json_encode($res));
        exit;
    }

    $sentData = $this->getData();

    if (!isset($sentData['card_pin']) || empty($sentData['card_pin'])) {
        $res = [
            'status' => false,
            'message' => 'must enter a 4 digit pin',
        ];
        http_response_code(401);
        print_r(json_encode($res));
        exit;
    }

    $data = [
        'cardholder_id' => $id,
        'card_type' => 'virtual',
        'card_brand' => 'Mastercard',
        'card_currency' => 'USD',
        'card_limit' => '500000',
        'pin' => $this->bridgecardPin($sentData['card_pin']),
        'funding_amount'=> '300',
        'meta_data' => ['user_id' => $userData->user_id]
    ];
 
    // print_r($data);
    $url = 'https://issuecards.api.bridgecard.co/v1/issuing/cards/create_card';
    $res = $this->createCard($url, $data);
//  $data['card_id'] = 

    print_r($res);
}

 public function splitName($fullName) {
            // Trim and split the full name into parts
            $nameParts = array_filter(explode(' ', trim($fullName)));
        
            $firstName = '';
            $middleName = '';
            $lastName = '';
        
            $count = count($nameParts);
        
            if ($count === 1) {
                // Only one name part
                $firstName = $nameParts[0];
            } elseif ($count === 2) {
                // Two name parts
                $firstName = $nameParts[0];
                $lastName = $nameParts[1];
            } elseif ($count === 3) {
                // Three name parts
                $firstName = $nameParts[0];
                $middleName = $nameParts[1];
                $lastName = $nameParts[2];
            } else {
                // Four or more name parts
                $firstName = $nameParts[0];
                $lastName = array_pop($nameParts);
                $middleName = implode(' ', array_slice($nameParts, 1));
            }
        
            return [
                'first_name' => $firstName,
                'middle_name' => $middleName,
                'last_name' => $lastName
            ];
        }
public function createVirtualCardholder()
{
    try {
        // Ensure user authentication
        $userData = $this->RouteProtecion();
    } catch (UnexpectedValueException $e) {
        $res = [
            'status' => 401,
            'message' => $e->getMessage(),
        ];
        http_response_code(401);
        print_r(json_encode($res));
        exit;
    } catch (DomainException $e) {
        http_response_code(401);
        $res = [
            'status' => 401,
            'message' => $e->getMessage(),
        ];
        print_r(json_encode($res));
        exit;
    }

    $sentData = $this->getData();
    $user = $this->userModel->getUser($userData->user_id);
    $nameParts = $this->splitName($user->fullname);
// print_r($userData);exit;

    $data = [
        'cardholder_id' => $this->generateUniqueId(),
        'first_name' => $nameParts['first_name'],
        'last_name' => $nameParts['last_name'],
        'address' => ['address' => $user->address, 'country' => 'Nigeria', 'state' => $sentData['state'], 'city' => $sentData['city'], 'postal_code' => $sentData['postal_code'], 'house_no' => $sentData['house_no']],
        'phone' => $user->phone,
        'email_address' => $user->email,
        'identity'=> ['id_type'=>'NIGERIAN_BVN_VERIFICATION', 'bvn' => $userData->bvn_no, 'selfie_image' => $user->image ],
        'meta_data' => ['user_id' => $userData->user_id]
    ];
//  print_r(json_encode($data));exit;
    // print_r($data);
    $url = 'https://issuecards.api.bridgecard.co/v1/issuing/cardholder/register_cardholder';
    $res = $this->createCardHolder($url, $data);
    // print_r(json_encode($data));exit;

    $response = (json_decode($res));
    print_r(json_encode($response));exit;
    $card_id = $response->data->cardholder_id;
    
    $this->createVirtualCard($card_id);
}
 
    


}
