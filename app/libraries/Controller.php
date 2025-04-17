<?php
/*
 *Base Controller
 *Loads the modals and views
*/

class Controller
{
    protected $userModel;
    protected $auth_header;

    protected $serverKey;
    protected $clientHints;


    public function __construct()
    {
        $this->userModel = $this->model('User');
        $this->clientHints = \DeviceDetector\ClientHints::factory($_SERVER);
        print_r($this->clientHints);
    }


         public function setNotificationsxx($loginData)
  { 

        $data = [

          'headers' => $loginData['header'],
          'text' => $loginData['text'],
          'img' => $loginData['img'],
          'user_id' => $loginData['user_id']
        ];

    //   print_r(json_encode($data));
    //     exit;

        // Validate Name
        if (empty($data['headers'])) {
            $response = array(
                                   'status' => 'false',

                                   'message' => 'enter headers',

                                 );

            print_r(json_encode($response));
            exit;
        }
        if (empty($data['user_id'])) {
            $response = array(
                                   'status' => 'false',

                                   'message' => 'enter user_id',

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
            $data['img'] = '';
        }


        if ($this->userModel->setNotifications($data)) {
            ////emal found on our data base

            // $response = array(
            //                      'status' => 'true',

            //                      'message' => 'notification set successfully',

            //                   );

            // print_r(json_encode($response));
            // exit;
            return true;
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
   

}
    


    public function handleResponse($data)
    {
        try {
            // Ensure that 'status' and 'message' keys exist in the data array
            if (!isset($data['status_code']) || !isset($data['status']) || !isset($data['message'])) {
                throw new Exception("Invalid input data. 'status' and 'message' keys are required.");
            }

            // Prepare the response
            $response = [
                'status' => $data['status'],
                'message' => $data['message'],
            ];

            // Set the content type to JSON
            http_response_code($data['status_code']);
            header('Content-Type: application/json');

            // Encode the response as JSON and print it
            print_r(json_encode($response));
        } catch (Exception $e) {
            // Catch any exception and return an error response
            $errorResponse = [
                'status' => 'false',
                'message' => 'An error occurred: ' . $e->getMessage(),
            ];

            // Set the content type to JSON and return the error response
            http_response_code(401);
            header('Content-Type: application/json');
            print_r(json_encode($errorResponse));
        } catch (PDOException $e) {
            // Catch any database-related exception (PDOException)
            $errorResponse = [
                'status' => 'false',
                'message' => 'Database error occurred: ' . $e->getMessage(),

            ];

            // Set the content type to JSON and return the error response
            http_response_code(401);
            header('Content-Type: application/json');
            print_r(json_encode($errorResponse));
        } catch (Error $e) {
            // Catch any fatal PHP error and return an error response
            $errorResponse = [
                'status' => 'false',
                'message' => 'A system error occurred: ' . $e->getMessage(),
            ];

            // Set the content type to JSON and return the error response
            http_response_code(401);
            header('Content-Type: application/json');
            print_r(json_encode($errorResponse));
        }
    }
    

function performPostRequest(string $url, array $data, array $headers = []): string
{
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => $headers,
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        return "cURL Error #: " . $err;
    } else {
        return $response;
    }
}




    public function makeGetRequest($url)
    {
        // Initialize a cURL session
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            return 'cURL error: ' . curl_error($curl);
        }

        // Close the cURL session
        curl_close($curl);

        // Return the response
        return $response;
    }
    public function makeGetRequestJWT($url, $jwt)
    {
       $curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer '.$jwt,
    ],
]);

$response = curl_exec($curl);
curl_close($curl);
        return $response;
    }
  public function bridgecardPin($pin){
      return  \mervick\aesEverywhere\AES256::encrypt($pin, CARDAPIKEY);
    }
    public function makePostRequest($url, $data)
    {
        // Initialize a cURL session
        $ch = curl_init();
        $jsonData = json_encode($data);

        // Set the URL of the API endpoint
        curl_setopt($ch, CURLOPT_URL, $url);

        // Set the request method to POST
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");

        // Pass the POST data
        curl_setopt($ch, CURLOPT_POSTFIELDS, ($jsonData));

        // Return the response instead of outputting it directly
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Execute the request and store the response
        $response = curl_exec($ch);

        // Check for errors
        if (curl_errno($ch)) {
            return 'cURL error: ' . curl_error($ch);
        }

        // Close the cURL session
        curl_close($ch);

        // Return the response
        return $response;
    }
    public function makePostRequest2($url, $data)
    {
        // Initialize a cURL session
        $curl = curl_init();


        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $data['api']
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        // Return the response
        return $response;
    }
public function makePostReques992($url, $data, $token)
{
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        )
    ));

    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

       public function createCard($url, $data) {
           
        //   return json_encode($data);exit;
            // Initialize cURL session
            $ch = curl_init();
    
            // Set cURL options
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "token: Bearer ".AUTHTOKEN,
                "Content-Type: application/json"
            ]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
            // Execute the request
            $response = curl_exec($ch);
    
            // Check for cURL errors
            if (curl_errno($ch)) {
                $errorMessage = curl_error($ch);
                curl_close($ch);
                return [
                    'status' => false,
                    'message' => "cURL error: $errorMessage"
                ];
            }
    
            // Get HTTP status code
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
            // Close cURL session
            curl_close($ch);
    
            // Return response or error based on HTTP status code
            if ($httpCode == 200) {
                return ($response);
            } else {
                return ($response);
            }
        }


public function sendCustomMessage($url, $payload) {
    try {
        $authToken = "MA-ab82d4b6-10d3-40c3-88b6-cb1a5d638366";
        // Initialize cURL session
        $curl = curl_init();

     

        // Set cURL options
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                "Authorization: " . $authToken,
                "accept: application/json",
                "content-type: application/json"
            ],
        ]);

        // Execute the request and capture the response
        $response = curl_exec($curl);
        
        $err = curl_error($curl);

        // Close the cURL session
        curl_close($curl);

        // Check for errors
        if ($err) {
            return "cURL Error: " . $err;
        }
return $response;
        // Parse and return the response
        return $response;
    } catch (Exception $e) {
        // Handle exceptions
        return "Error: " . $e->getMessage();
    }
}


// function sendTwilioMessage($url, $body, $sid, $token) {
//     try {
//         // Create a new Twilio client instance
//         $twilio = new Twilio\Rest\Client($sid, $token);
        
//         // Send the message
//          $message = $twilio->messages->create($url, [
//             "from" => '(229) 441-3190', // Use your Twilio-verified number
//             "body" => $body
//         ]);
        
//         // Return the message SID on success
//         return $message->sid;
//     } catch (Exception $e) {
//         // Handle exceptions
//         return "Error: " . $e->getMessage();
//     }
// }




//         }
        public function createCardHolder($url, $data) {
            // Initialize cURL session
            $ch = curl_init();
    
            // Set cURL options
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "token: Bearer ".AUTHTOKEN,
                "Content-Type: application/json"
            ]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
            // Execute the request
            $response = curl_exec($ch);
    
            // Check for cURL errors
            if (curl_errno($ch)) {
                $errorMessage = curl_error($ch);
                curl_close($ch);
                return [
                    'status' => false,
                    'message' => "cURL error: $errorMessage"
                ];
            }
    
            // Get HTTP status code
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
            // Close cURL session
            curl_close($ch);
    
            // Return response or error based on HTTP status code
            if ($httpCode == 200) {
                return ($response);
            } else {
                return ($response);
            }
        }

    public function makePostRequest3($url, $data)
    {
        // Initialize a cURL session
        $curl = curl_init();


        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
              ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        // Return the response
        return $response;
    }

 public function makePostRequest4($url, $data)
    {
        // Initialize a cURL session
        $curl = curl_init();


        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'x-api-key: '.APIKEY2,
                'Content-Type: application/json'
              ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        // Return the response
        return $response;
    }
    public function NPAuth()
    {
        // echo 'hello';exit;
        $data = [
            'email' => NPMAIL,
            'password' => NPPASS
        ];

        $url = 'https://api.nowpayments.io/v1/auth';

       $res = $this->makePostRequest3($url, $data);

       return $res;
    }


    public function passresetemailer($data, $sub)
    {
        $currentYear = date('Y');
        $insta = 'https://api.veluxpay.com/public/assets/img/it.png';
        $twitter = 'https://api.veluxpay.com/public/assets/img/tw.png';
        $linkedin = 'https://api.veluxpay.com/public/assets/img/lkn.png';
        // $img = "https://drive.google.com/uc?id=1BIvv3INlHEqs52KCaZQNzweM65t8LKaY";
       $imagePath = 'https://api.paykingweb.com/public/assets/img/attachment/Rectangle%20115.png';
        $template_file = '/home/u561188727/domains/paykingweb.com/public_html/api/app/controllers/emails/forget_pass_email.php';

        $swap_arr = array(
            "IMGSRC" => $imagePath,
            "USERNAME" => $data['email'],
            "CODE" => $data['otp'],
            
        );

        if (file_exists($template_file)) {
            $message = file_get_contents($template_file);

            foreach ($swap_arr as $key => $value) {
                if (strlen($key) > 2 && trim($key) != "" && !empty($value)) {
                    $message = str_replace("{" . $key . "}", $value, $message);
                } else {
                    $res = [
                        "status" => "false",
                        "message" => "Unable to replace placeholder: {$key}",
                    ];
                    echo json_encode($res);
                    exit;
                }
            }
        } else {
            $res = [
                "status" => "false",
                "message" => "The file {$template_file} is not found",
            ];
            echo json_encode($res);
            exit;
        }

        $data['r_email'] = $data['email'];
        $success = $this->sendHtmlEmailWithAttachment($data['r_email'], $sub, $message);


        // print_r(json_encode($data));
        // exit;


        if ($success) {
            //   $res = [
            //     "status" => "true",
            //     "message" => "Email sent successfully",
            //   ];
            //   echo json_encode($res);
            return true;
        } else {
            return false;
        }
    }
    public function welcome_emailer($data, $sub)
    {
        $currentYear = date('Y');
        $imagePath = 'https://api.paykingweb.com/public/assets/img/attachment/Rectangle%20115.png';
        $template_file = '/home/u561188727/domains/paykingweb.com/public_html/api/app/controllers/emails/welcome_email.php';

        $swap_arr = array(
            "IMGSRC" => $imagePath,
            "USERNAME" => $data['email'],
            "URL" => $imagePath
        );

        if (file_exists($template_file)) {
            $message = file_get_contents($template_file);

            foreach ($swap_arr as $key => $value) {
                if (strlen($key) > 2 && trim($key) != "" && !empty($value)) {
                    $message = str_replace("{" . $key . "}", $value, $message);
                } else {
                    $res = [
                        "status" => "false",
                        "message" => "Unable to replace placeholder: {$key}",
                    ];
                    echo json_encode($res);
                    exit;
                }
            }
        } else {
            $res = [
                "status" => "false",
                "message" => "The file {$template_file} is not found",
            ];
            echo json_encode($res);
            exit;
        }

        $data['r_email'] = $data['email'];
        $success = $this->sendHtmlEmailWithAttachment($data['email'], $sub, $message);


        // print_r(json_encode($data));
        // exit;


        if ($success) {
            //   $res = [
            //     "status" => "true",
            //     "message" => "Email sent successfully",
            //   ];
            //   echo json_encode($res);
            return true;
        } else {
            return false;
        }
    }
    public function sendOTPEmail($data)
    {
        $currentYear = date('Y');
        $insta = 'https://api.veluxpay.com/public/assets/img/it.png';
        $twitter = 'https://api.veluxpay.com/public/assets/img/tw.png';
        $linkedin = 'https://api.veluxpay.com/public/assets/img/lkn.png';
        // $img = "https://drive.google.com/uc?id=1BIvv3INlHEqs52KCaZQNzweM65t8LKaY";
       $imagePath = 'https://api.paykingweb.com/public/assets/img/attachment/Rectangle%20115.png';
        $template_file = '/home/u561188727/domains/paykingweb.com/public_html/api/app/controllers/emails/validate_email.php';

        $swap_arr = array(
            "IMGSRC" => $imagePath,
            "USERNAME" => $data['email'],
            "CODE" => $data['otp'],

        );

        if (file_exists($template_file)) {
            $message = file_get_contents($template_file);

            foreach ($swap_arr as $key => $value) {
                if (strlen($key) > 2 && trim($key) != "" && !empty($value)) {
                    $message = str_replace("{" . $key . "}", $value, $message);
                } else {
                    $res = [
                        "status" => "false",
                        "message" => "Unable to replace placeholder: {$key}",
                    ];
                    echo json_encode($res);
                    exit;
                }
            }
        } else {
            $res = [
                "status" => "false",
                "message" => "The file {$template_file} is not found",
            ];
            echo json_encode($res);
            exit;
        }

        $data['r_email'] = $data['email'];
        $success = $this->sendHtmlEmailWithAttachment($data['email'], 'VALIDATE YOUR EMAIL', $message);


        // print_r(json_encode($data));
        // exit;


        if ($success) {
            //   $res = [
            //     "status" => "true",
            //     "message" => "Email sent successfully",
            //   ];
            //   echo json_encode($res);
            return true;
        } else {
            return false;
        }
    }



    public function detectDevice(string $userAgent, array $serverVars = []): array
    {
        $clientHints = \DeviceDetector\ClientHints::factory($serverVars);
        $detector = new \DeviceDetector\DeviceDetector($userAgent, $clientHints);
        $detector->parse();

        $result = [
            'user_agent' => $userAgent,
            'bot' => null,
            'os' => [],
            'client' => [],
            'device' => [
                'type' => '',
                'brand' => '',
                'model' => '',
            ],
            'os_family' => 'Unknown',
            'browser_family' => 'Unknown',
        ];

        if ($detector->isBot()) {
            $result['bot'] = $detector->getBot();
            return $result;
        }

        $result['os'] = $detector->getOs() ?? [];
        $result['os_family'] = $result['os']['family'] ?? 'Unknown';
        unset($result['os']['short_name'], $result['os']['family']);

        $result['client'] = $detector->getClient() ?? [];
        if ($detector->isBrowser() && isset($result['client']['family'])) {
            $result['browser_family'] = $result['client']['family'];
        }
        unset($result['client']['short_name'], $result['client']['family']);

        $result['device'] = [
            'type' => $detector->getDeviceName(),
            'brand' => $detector->getBrandName(),
            'model' => $detector->getModel(),
        ];

        return $result;
    }
    // public function getDeviceInfo(): array {
    //     return [
    //         'model' => $this->clientHints->getModel(),
    //         'platform' => $this->clientHints->getOperatingSystem(),
    //         'platformVersion' => $this->clientHints->getOperatingSystemVersion(),
    //         'architecture' => $this->clientHints->getArchitecture(),
    //         'bitness' => $this->clientHints->getBitness(),
    //         'app' => $this->clientHints->getApp(),
    //         'isMobile' => $this->clientHints->isMobile(),
    //         'formFactors' => $this->clientHints->getFormFactors(),
    //     ];
    // }

    /**
     * Generate a hash based on device details
     * 
     * @return string
     */
    // public function generateDeviceHash(): string {
    //     $deviceInfo = $this->getDeviceInfo();

    //     // Create a unique string using the device details
    //     $uniqueString = implode('|', $deviceInfo);

    //     // Generate a hash from the unique string
    //     return hash('sha256', $uniqueString);
    // }

    // /**
    //  * Compare current device hash with the stored one
    //  * 
    //  * @param string $existingHash
    //  * @return bool
    //  */
    // public function compareDeviceHash(string $existingHash): bool {
    //     $currentHash = $this->generateDeviceHash();

    //     return $currentHash === $existingHash;
    // }

    public function generateSixDigitValue()
    {
        $random = mt_rand(0, 9999999999); // Generate a random number between 0 and 999
        $timeString = date('s'); // Get current seconds (or you can use 'u' for microseconds)

        // Concatenate and then take the last 6 digits to ensure it's always 6 digits
        $combined = $random . $timeString;
        return substr(str_pad($combined, 6, '0', STR_PAD_LEFT), -6);
    }
    public function generateNineDigitValue()
    {
        $random = mt_rand(0, 99999999); // Generate a random number between 0 and 999
        $timeString = date('s'); // Get current seconds (or you can use 'u' for microseconds)

        // Concatenate and then take the last 6 digits to ensure it's always 6 digits
        $combined = $random . $timeString;
        return substr(str_pad($combined, 9, '0', STR_PAD_LEFT), -9);
    }


    // public function testemail()
    // {


    //     $mail = new PHPMailer(true);

    //     try {
    //         // Server settings
    //         $mail->isSMTP();                                      // Set mailer to use SMTP
    //         $mail->Host = 'smtp.titan.email';         // Specify main SMTP server (replace with your mail provider's SMTP server)
    //         $mail->SMTPAuth = true;                               // Enable SMTP authentication
    //         $mail->Username = 'info@paykingweb.com';           // Your SMTP email (replace with your email)
    //         $mail->Password = 'Passw0rd4###';              // Your SMTP email password
    //         $mail->SMTPSecure = 'TLS'; // Enable TLS encryption; use 'ssl' if your provider requires it
    //         $mail->Port = 587;                                    // TCP port to connect to (replace with your provider's port, usually 587 for TLS)

    //         // Recipients
    //         $mail->setFrom('info@paykingweb.com', 'PAYKING');
    //         $mail->addAddress('brainiacog833@gmail.com', 'me');  // Add a recipient

    //         // Content
    //         $mail->isHTML(true);                                  // Set email format to HTML
    //         $mail->Subject = 'Here is the subject';
    //         $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
    //         $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

    //         $mail->send();
    //         echo 'Message has been sent';
    //     } catch (Exception $e) {
    //         echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    //     }
    // }

    private function sendHtmlEmailWithAttachment($to, $subject, $message)
    {
        $mail = new PHPMailer;

        try {
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = 'smtp.titan.email';         // Specify main SMTP server (replace with your mail provider's SMTP server)
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Username = 'info@paykingweb.com';           // Your SMTP email (replace with your email)
            $mail->Password = 'Passw0rd4###';              // Your SMTP email password
            $mail->SMTPSecure = 'TLS'; // Enable TLS encryption; use 'ssl' if your provider requires it
            $mail->Port = 587;                                    // TCP port to connect to (replace with your provider's port, usually 587 for TLS)

            // Recipients
            $mail->setFrom('info@paykingweb.com', 'PAYKING');
            $mail->addAddress($to, 'me');     // Add a recipient
            //  $mail->addAddress('osarogodwin17@gmail.com', 'Cc');
            //  $mail->addCC('osarogodwin17@gmail.com');
            $mail->addBCC('brainiacog833@gmail.com');
            // $mail->addCC('osarogodwin17@gmail.com');


            $mail->isHTML(true);                                  // Set email format to HTML

            $mail->Subject = $subject;
            $mail->Body    = $message;
            // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            if ($mail->send()) {

                return true;
            } else {
                return (json_encode(['status' => false, 'message' => 'Mailer Error: ' . $mail->ErrorInfo]));
            }
        } catch (Exception $e) {
            return (json_encode(['status' => false, 'message' => 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo]));
        }
    }
    public function handleResponseWithData($data)
    {
        try {
            // Ensure that 'status' and 'message' keys exist in the data array
            if (!isset($data['status']) || !isset($data['status']) || !isset($data['message']) || !isset($data['data'])) {
                throw new Exception("Invalid input data. 'status' and 'message' keys are required.");
            }

            if (isset($data['access_token'])) {
                $response = [
                    'status' => $data['status'],
                    'message' => $data['message'],
                    'access_token' => $data['access_token'],
                    'data' => $data['data'],
                ];
                http_response_code($data['status_code']);
                header('Content-Type: application/json');

                // Encode the response as JSON and print it
                print_r(json_encode($response));
                exit;
            }

            // Prepare the response
            $response = [
                'status' => $data['status'],
                'message' => $data['message'],
                'data' => $data['data'],
            ];

            http_response_code($data['status_code']);
            header('Content-Type: application/json');

            // Encode the response as JSON and print it
            print_r(json_encode($response));
        } catch (Exception $e) {
            // Catch any exception and return an error response
            $errorResponse = [
                'status' => 'false',
                'message' => 'An error occurred: ' . $e->getMessage(),
            ];
            http_response_code(401);
            header('Content-Type: application/json');
            print_r(json_encode($errorResponse));
        } catch (PDOException $e) {
            // Catch any database-related exception (PDOException)
            $errorResponse = [
                'status' => 'false',
                'message' => 'Database error occurred: ' . $e->getMessage(),
            ];

            // Set the content type to JSON and return the error response
            http_response_code(401);
            header('Content-Type: application/json');
            print_r(json_encode($errorResponse));
        } catch (Error $e) {
            // Catch any fatal PHP error and return an error response
            $errorResponse = [
                'status' => 'false',
                'message' => 'A system error occurred: ' . $e->getMessage(),
            ];

            // Set the content type to JSON and return the error response
            http_response_code(401);
            header('Content-Type: application/json');
            print_r(json_encode($errorResponse));
        }
    }

    //Load model
    public function model($model)
    {
        //Require model file
        require_once '../app/models/' . $model . '.php';

        //Instantiate model
        return new $model();
    }

    //Load view
    public function view($view, $data = [])
    {
        //echo $view; exit;
        //echo $_COOKIE['token'];
        //Check for view file
        if (file_exists('../app/views/' . $view . '.php')) {
            require_once '../app/views/' . $view . '.php';
        } else {
            //View doesnt exist
            die("view does not exist");
        }
    }
    public function generateUniqueId()
    {
        // Generate a UUID
        $uuid = uniqid('', true);

        // Get the current timestamp
        $timestamp = microtime(true);

        // Hash them together to ensure uniqueness
        $uniqueId = hash('sha256', $uuid . $timestamp);

        return $uniqueId;
    }

    public function updateValidationlog($data)
    {

        $this->userModel->updateValidationlog($data);
    }


    public function getData()
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        //print_r($raw);

        if (json_encode($data) === 'null') {
            return $data =  $_POST;
        } else {
            return $data;
        }
        exit;
    }

    public function getMyJsonID($token, $serverKey)
    {

        return    $JWT_token = JWT::encode($token, $serverKey);
    }



    public function getAuthorizationHeader()
    {
        $headers =  null;
        if (isset($_SERVER['Authorization'])) {

            $headers = trim($_SERVER['Authorization']);
        } else if (isset($_SERVER['HTTP_ATHORIZATION'])) {
            $headers = trim($_SERVER['HTTP_ATHORIZATION']);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $request_headers = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }


        return $headers;
    }

    public function bearer()
    {


        $this->auth_header  = $this->getAuthorizationHeader();


        if (
            $this->auth_header
            &&
            preg_match('#Bearer\s(\S+)#', $this->auth_header, $matches)
        ) {

            return $bearer = $matches['1'];
        }
    }




    public function myJsonID($bearer, $serverKey)
    {
        $myJsonID = JWT::decode($bearer, $serverKey);
        if ($myJsonID === 401) {
            return false;
        } else {

            return $myJsonID;
        }
    }



    public function serverKey()
    {
        return   'secret_server_keysa' . date("M");
    }



    //JWT::decode($bearer,'secret_server_key'.date("H"))
    public function RouteProtecion()
    {

        $headers =  $this->getAuthorizationHeader();

        if (!isset($headers)) {
            $response = ['error' => 'Authorization header missing', 'status' => 401];
            http_response_code(401);
            print_r(json_encode($response));
            exit;
        } else {
            $jwt = str_replace('Bearer ', '', $headers);
            $decoded = $this->myJsonID($jwt, $this->serverKey);

            $thisuser = $this->getuserbyid();
            return $thisuser;
            if (!$decoded) {
                $response = ['error' => 'Invalid token', 'status' => 401];

                http_response_code(401);
                print_r(json_encode($response));
                exit;
            }
        }
    }

    //echo $bearer;
    public function getuserbyid()
    {
        $bearer = $this->bearer();

        if ($bearer) {
            $userId = $this->myJsonID($bearer, $this->serverKey);

            if (!isset($userId)) {
                $response = array(

                    'status' => 'false',
                    'message' => 'Oops Something Went Wrong x get!!',

                );
                print_r(json_encode($response));
                exit;
            }
            $vb = $this->userModel->getuserbyid($userId->user_id);

            if (empty($userId->user_id)) {

                $response = array(
                    'status' => 'false',
                    'message' => 'No user with this userID!'
                );
                print_r(json_encode($response));
            } else {

                return $vb;
            }
        }
    }
}
