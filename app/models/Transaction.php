<?php

class Transaction

{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }
     public function findUserByemail($email)
  {
      $this->db->query("SELECT * FROM initkey WHERE  uname = :uname");

      // Bind Values
      $this->db->bind(':uname', $email);

      $row = $this->db->single();

      // Check roow
      if ($this->db->rowCount() > 0) {
          return $row;
      } else {
          return false;
      }
  }
     public function findCampaignById($email)
  {
      $this->db->query("SELECT * FROM campaign_details WHERE  campaign_id = :campaign_id");

      // Bind Values
      $this->db->bind(':campaign_id', $email);

      $row = $this->db->single();

      // Check roow
      if ($this->db->rowCount() > 0) {
          return $row;
      } else {
          return false;
      }
  }
     public function findEventById($email)
  {
      $this->db->query("SELECT * FROM ticket_details WHERE  event_id = :event_id");

      // Bind Values
      $this->db->bind(':event_id', $email);

      $row = $this->db->single();

      // Check roow
      if ($this->db->rowCount() > 0) {
          return $row;
      } else {
          return false;
      }
  }
   public function  checkAccount($tag, $user_id, $amount)
  {


      $this->db->query('SELECT * FROM user_accounts WHERE   email = :email and  user_id = :user_id ');
      // Bind Valuesrid
      $this->db->bind(':email', $tag);
      $this->db->bind(':user_id', $user_id);
         $row = $this->db->single();
      if ($this->db->rowCount() > 0) {
        if ($row->savings > $amount) {
            return true;
      } else {
          return false;
      }
      
      }else{
          return false; 
      }
      
      





  }
  
    public function creditUser($datax)
{
    $this->db->query('SELECT * FROM user_accounts WHERE email = :email AND user_id = :user_id');
    $this->db->bind(':email', $datax['email']);
    $this->db->bind(':user_id', $datax['s_id']);
    $row = $this->db->single();

    if ($this->db->rowCount() > 0) {
        $newFund = $row->savings + $datax['amount'];
        $this->db->query('UPDATE user_accounts SET savings = :savings WHERE email = :email AND user_id = :user_id');
        $this->db->bind(':email', $datax['email']);
        $this->db->bind(':user_id', $datax['s_id']);
        $this->db->bind(':savings', $newFund);
        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    } else {
        $res = [
            'status' => 401,
            'message' => 'failed: user account not found'
        ];
        print_r(json_encode($res));
        exit;
    }
}
 public function  creditAdmin($datax)
  {
    //   print_r(json_encode($datax));
    $this->db->query('SELECT * FROM user_accounts WHERE  email = :email and  user_id = :user_id ');
    // Bind Valuesrid
    $this->db->bind(':email', $datax['email']);
    $this->db->bind(':user_id', $datax['s_id']);
    $row = $this->db->single();

    // Check roow
            if ($this->db->rowCount() > 0) {
            $newFund = $row->savings + $datax['amount'];
            $this->db->query('UPDATE user_accounts set savings = :savings WHERE  email = :email and  user_id = :user_id');

            $this->db->bind(':email', $datax['email']);
            $this->db->bind(':user_id', $datax['s_id']);
            $this->db->bind(':savings', $newFund);
            if ($this->db->execute()) {
                return true;
            } else {
                $res = [
                    'status' => 401,
                    'message' => 'failed',
                ];
                print_r(json_encode($res));
                exit;
            }
        }else {
            return false;
        }


}

  public function  deposite($datax)
  {

      $this->db->query('INSERT INTO  deposite_ (email,full_name, user_id, transaction_status, amount, transaction_id, transaction_ref, date) VALUES( :email,:full_name, :user_id, :transaction_status, :amount, :transaction_id, :transaction_ref, :date)');
      // Bind Valuesrid
      $this->db->bind(':email', $datax['email']);
      $this->db->bind(':full_name', $datax['fulname']);
      $this->db->bind(':user_id', $datax['s_id']);
      $this->db->bind(':transaction_status', $datax['tr_status']);
      $this->db->bind(':amount', $datax['amount']);
      $this->db->bind(':transaction_id', $datax['tr_id']);
      $this->db->bind(':transaction_ref', $datax['t_ref']);
      // Set the PHP timezone to GMT
      date_default_timezone_set('Europe/Berlin'); // or 'Africa/Lagos'
     $this->db->bind(':date', date('Y-m-d H:i:s'));
    
      //$this->db->execute();


        if ($this->db->execute()) {
          
          // Insert into all_transactions table
       $this->db->query('INSERT INTO  all_transactions (email,fullname, user_id, transaction_status, amount, transaction_id, transaction_ref, transaction_type, date) VALUES( :email,:fullname, :user_id, :transaction_status, :amount, :transaction_id, :transaction_ref, :transaction_type, :date)');
      // Bind Valuesrid
      $this->db->bind(':email', $datax['email']);
      $this->db->bind(':fullname', $datax['fulname']);
      $this->db->bind(':user_id', $datax['s_id']);
      $this->db->bind(':transaction_status', $datax['tr_status']);
      $this->db->bind(':amount', $datax['amount']);
      $this->db->bind(':transaction_id', $datax['tr_id']);
      $this->db->bind(':transaction_ref', $datax['t_ref']);
      $this->db->bind(':transaction_type', 'credit');
       // Set the PHP timezone to GMT
    date_default_timezone_set('Europe/Berlin'); // or 'Africa/Lagos'
      $this->db->bind(':date', date('Y-m-d H:i:s'));
    
     if ($this->db->execute()) {
            $activity = "ACCOUNT DEPOSIT FROM BANK";
            $processID = "log_" . md5(time());
            $this->db->query("INSERT INTO activity_log 
        (activity, user_id, activity_id, process_id, amount) 
        VALUES 
        (:activity, :user_id, :activity_id, :process_id, :amount)");

            $this->db->bind(":activity", $activity);
            $this->db->bind(":user_id", $datax["s_id"]);
            $this->db->bind(":activity_id", $datax["tr_id"]);
            $this->db->bind(":process_id", $processID);
            $this->db->bind(":amount", $datax["amount"]);

            if ($this->db->execute()) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }

      } else {
        //   return false;
        if (!$this->db->execute()) {
    // Log or display the error
//   echo "error here";
    return false;
}
      }




  }
  
  public function getUserTransactionsxx($id, $tag, $tr_id)
{
    $this->db->query('
        SELECT * 
        FROM all_transactions 
        WHERE (sender_username = :sender_username AND sender_id = :sender_id AND transaction_id = :transaction_id) 
           OR (receiver_username = :receiver_username AND receiver_id = :receiver_id AND transaction_id = :transaction_id) OR user_id = :user_id AND transaction_id = :transaction_id
        ORDER BY id DESC');

    $this->db->bind(':sender_username', $tag);
    $this->db->bind(':sender_id', $id);
    $this->db->bind(':receiver_username', $tag);
    $this->db->bind(':receiver_id', $id);
     $this->db->bind(':user_id', $id);
  $this->db->bind(':transaction_id', $tr_id);
    $rows = $this->db->resultSet();
    

    if ($this->db->rowCount() > 0) {
        
     return ['status' => true,'message'=>'sucess', 'data' => $rows];
    } else {
       // echo 'Error: ' . $this->db->error();
        http_response_code(200);
        return ['status' => false,'message'=>'no transaction made yet', 'data' => []];
    }
}
  public function getAllTransactions()
{
    $this->db->query('
        SELECT * 
        FROM all_transactions 
        ORDER BY id DESC');

    $rows = $this->db->resultSet();
    

    if ($this->db->rowCount() > 0) {
        
     return ['status' => true,'message'=>'sucess', 'data' => $rows];
    } else {
       // echo 'Error: ' . $this->db->error();
        http_response_code(200);
        return ['status' => false,'message'=>'no transaction made yet', 'data' => []];
    }
}
  
  
  public function getCryptoTransactionsByID($id, $tag, $tr_id)
{
    $this->db->query('
        SELECT * 
        FROM payments 
        WHERE user_id = :user_id AND payment_id = :payment_id AND email =:email
        ORDER BY id DESC');

    $this->db->bind(':email', $tag);
     $this->db->bind(':user_id', $id);
  $this->db->bind(':payment_id', $tr_id);
    $rows = $this->db->resultSet();
    

    if ($this->db->rowCount() > 0) {
        
     return ['status' => true,'message'=>'sucess', 'data' => $rows];
    } else {
        // echo 'Error: ' . $this->db->error();
        http_response_code(200);
        return ['status' => false,'message'=>'no transaction made yet', 'data' => []];
    }
}
  
  public function getUserTransactions($id, $tag)
{
    $this->db->query('
        SELECT * 
        FROM all_transactions 
        WHERE (sender_username = :sender_username AND sender_id = :sender_id) 
           OR (receiver_username = :receiver_username AND receiver_id = :receiver_id) OR user_id = :user_id
        ORDER BY id DESC');

    $this->db->bind(':sender_username', $tag);
    $this->db->bind(':sender_id', $id);
    $this->db->bind(':receiver_username', $tag);
    $this->db->bind(':receiver_id', $id);
     $this->db->bind(':user_id', $id);
//   $this->db->bind(':veluxite_id', $id);
    $rows = $this->db->resultSet();
    

    if ($this->db->rowCount() > 0) {
        
//print_r($this->db->getBindings());
//print_r($rows);
        //  print_r(json_encode($rows));
        return ['status' => true,'message'=>'sucess', 'data' => $rows];
    } else {
        // echo 'Error: ' . $this->db->error();
        http_response_code(200);
        return ['status' => false,'message'=>'no transaction made yet', 'data' => []];
    }
}
  public function getCryptoTransactions($id, $tag)
{
    $this->db->query('
        SELECT * 
        FROM payments 
        WHERE user_id = :user_id AND email = :email
        ORDER BY id DESC');

     $this->db->bind(':user_id', $id);
  $this->db->bind(':email', $tag);
    $rows = $this->db->resultSet();
    

    if ($this->db->rowCount() > 0) {
        
//print_r($this->db->getBindings());
//print_r($rows);
        //  print_r(json_encode($rows));
        return ['status' => true,'message'=>'sucess', 'data' => $rows];
    } else {
        // echo 'Error: ' . $this->db->error();
        http_response_code(200);
        return ['status' => false,'message'=>'no transaction made yet', 'data' => []];
    }
}

   public function getElectricity()
    {
        $this->db->query(
            "SELECT * FROM  electric_plans"
        );

        return $this->db->resultSet();
    }
   public function appsettings()
    {
        $this->db->query(
            "SELECT * FROM  appsettings"
        );

        return $this->db->single()->crypto_charge;
    }
        public function getGotvPlans()
    {
        $this->db->query(
            "SELECT * FROM  gotvplans"
        );

        return $this->db->resultSet();
    }
    public function getDstvPlans()
    {
        $this->db->query(
            "SELECT * FROM  dstvplans"
        );

        return $this->db->resultSet();
    }
    public function getStartimePlans()
    {
        $this->db->query(
            "SELECT * FROM  startimeplans"
        );

        return $this->db->resultSet();
    }
       public function saveGotv($data)
    {
        $this->db->query(
            "INSERT INTO gotvplans (display_name, price, value)
             VALUES (:display_name, :price, :value)
             ON DUPLICATE KEY UPDATE price = :price"
        );

        $this->db->bind(':display_name', $data['displayName']);
        $this->db->bind(':price', $data['price']);
        $this->db->bind(':value', $data['value']);

        return $this->db->execute();
    }
    public function saveDstv($data)
    {
        $this->db->query(
            "INSERT INTO dstvplans (display_name, price, value)
             VALUES (:display_name, :price, :value)
             ON DUPLICATE KEY UPDATE price = :price"
        );

        $this->db->bind(':display_name', $data['displayName']);
        $this->db->bind(':price', $data['price']);
        $this->db->bind(':value', $data['value']);

        return $this->db->execute();
    }
    public function saveStartime($data)
    {
        $this->db->query(
            "INSERT INTO startimeplans (display_name, price, value)
             VALUES (:display_name, :price, :value)
             ON DUPLICATE KEY UPDATE price = :price"
        );

        $this->db->bind(':display_name', $data['displayName']);
        $this->db->bind(':price', $data['price']);
        $this->db->bind(':value', $data['value']);

        return $this->db->execute();
    }
    public function saveEti($data)
    {
        $this->db->query(
            "INSERT INTO ettisalatdataplans (data_type, display_name, price, value)
             VALUES (:data_type, :display_name, :price, :value)
             ON DUPLICATE KEY UPDATE price = :price"
        );

        $this->db->bind(':data_type', $data['dataType']);
        $this->db->bind(':display_name', $data['displayName']);
        $this->db->bind(':price', $data['price']);
        $this->db->bind(':value', $data['value']);

        return $this->db->execute();
    }
    public function getEtisalatDataPlans()
    {
        $this->db->query(
            "SELECT * FROM  ettisalatdataplans"
        );

        return $this->db->resultSet();
    }


    public function saveAir($data)
    {
        $this->db->query(
            "INSERT INTO airteldataplans (data_type, display_name, price, value)
         VALUES (:data_type, :display_name, :price, :value)
         ON DUPLICATE KEY UPDATE price = :price"
        );

        $this->db->bind(':data_type', $data['dataType']);
        $this->db->bind(':display_name', $data['displayName']);
        $this->db->bind(':price', $data['price']);
        $this->db->bind(':value', $data['value']);

        return $this->db->execute();
    }

    public function getAirtelDataPlan()
    {
        $this->db->query(
            "SELECT * FROM  airteldataplans"
        );

        return $this->db->resultSet();
    }
    public function saveGloDataPlan($data)
    {
        $this->db->query(
            "INSERT INTO glodataplans (data_type, display_name, price, value)
         VALUES (:data_type, :display_name, :price, :value)
         ON DUPLICATE KEY UPDATE price = :price"
        );

        $this->db->bind(':data_type', $data['dataType']);
        $this->db->bind(':display_name', $data['displayName']);
        $this->db->bind(':price', $data['price']);
        $this->db->bind(':value', $data['value']);

        return $this->db->execute();
    }
    public function getGloDataPlan()
    {
        $this->db->query(
            "SELECT * FROM  glodataplans"
        );

        return $this->db->resultSet();
    }
    public function saveMtnDataPlan($data)
    {
        $this->db->query(
            "INSERT INTO mtndataplans (data_type, display_name, price, value)
         VALUES (:data_type, :display_name, :price, :value)
         ON DUPLICATE KEY UPDATE price = :price, value = :value"
        );

        $this->db->bind(':data_type', $data['dataType']);
        $this->db->bind(':display_name', $data['displayName']);
        $this->db->bind(':price', $data['price']);
        $this->db->bind(':value', $data['value']);

        return $this->db->execute();
    }
    public function getMtnDataPlan()
    {
        $this->db->query(
            "SELECT * FROM  mtndataplans"
        );

        return $this->db->resultSet();
    }
      public function  buyAirtime($datax)
    {
      $this->db->query('SELECT * FROM user_accounts WHERE  email = :email and  user_id = :user_id ');
      // Bind Valuesrid
      $this->db->bind(':email', $datax['email']);
      $this->db->bind(':user_id', $datax['user_id']);
      $row = $this->db->single();
  
      // Check roow
      if ($this->db->rowCount() > 0) {
        if ($row->savings > $datax['amount']) {
           $newFund = $row->savings - $datax['amount'];
             $this->db->query('UPDATE user_accounts set savings = :savings WHERE  email = :email and  user_id = :user_id');
  
        $this->db->bind(':email', $datax['email']);
        $this->db->bind(':user_id', $datax['user_id']);
        $this->db->bind(':savings', $newFund);
        if ($this->db->execute()) {
          $this->db->query('INSERT INTO  vtu_services (user_id, email,transaction_id, vtupackage, amount, date) VALUES( :user_id, :email,:transaction_id, :vtupackage, :amount, :date)');
          // Bind Valuesrid
          $this->db->bind(':email', $datax['email']);
          $this->db->bind(':user_id', $datax['user_id']);
          $this->db->bind(':transaction_id', $datax['tr_id']);
          $this->db->bind(':vtupackage', $datax['serviceID']);
          $this->db->bind(':amount',"-".$datax['amount']);
          // Set the PHP timezone to GMT
        date_default_timezone_set('Europe/Berlin'); // or 'Africa/Lagos'
       $this->db->bind(':date', date('Y-m-d H:i:s'));
          if ($this->db->execute()) {
              //return true;
               // Insert into all_transactions table
             $this->db->query('INSERT INTO  all_transactions (user_id, email,transaction_id,transaction_type,transaction_status, vtupackage, amount, date, phone) VALUES( :user_id, :email,:transaction_id,:transaction_type,:transaction_status, :vtupackage, :amount, :date, :phone)');
          // Bind Valuesrid
          $this->db->bind(':email', $datax['email']);
          $this->db->bind(':user_id', $datax['user_id']);
          $this->db->bind(':transaction_id', $datax['tr_id']);
          $this->db->bind(':transaction_type', $datax['tr_type']);
          $this->db->bind(':transaction_status', "successful");
          $this->db->bind(':vtupackage', $datax['serviceID']);
          $this->db->bind(':amount',$datax['amount']);
          $this->db->bind(':phone',$datax['phone']);
         // Set the PHP timezone to GMT
        date_default_timezone_set('Europe/Berlin'); // or 'Africa/Lagos'
       $this->db->bind(':date', date('Y-m-d H:i:s'));
           if ($this->db->execute()) {
            $activity = $datax['response'];
            $processID = "log_" . md5(time());
            $this->db->query("INSERT INTO activity_log 
        (activity, user_id, activity_id, process_id, amount) 
        VALUES 
        (:activity, :user_id, :activity_id, :process_id, :amount)");

            $this->db->bind(":activity", $activity);
            $this->db->bind(":user_id", $datax["user_id"]);
            $this->db->bind(":activity_id", $datax["tr_id"]);
            $this->db->bind(":process_id", $processID);
            $this->db->bind(":amount", $datax["amount"]);

            if ($this->db->execute()) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
              
          } else {
                 $res = [
                'status' => 401,
                'message' => 'failed 22',
              ];
              print_r(json_encode($res));
              exit;
          }
        } else {
            $res = [
                'status' => 401,
                'message' => 'failed',
              ];
              print_r(json_encode($res));
              exit;
        }
        }else {
            $res = [
                'status' => 401,
                'message' => 'not enough Balance',
              ];
              print_r(json_encode($res));
              exit;
        }
       
      
      }
  
  
  
  
  
  
  
    }
    
     public function  buyData($datax)
    {
  
  
      $this->db->query('SELECT * FROM user_accounts WHERE  email = :email and  user_id = :user_id ');
      // Bind Valuesrid
      $this->db->bind(':email', $datax['email']);
      $this->db->bind(':user_id', $datax['user_id']);
      $row = $this->db->single();
  
      // Check roow
      if ($this->db->rowCount() > 0) {
        if ($row->savings > $datax['amount']) {
           $newFund = $row->savings - $datax['amount'];
             $this->db->query('UPDATE user_accounts set savings = :savings WHERE  email = :email and  user_id = :user_id');
  
        $this->db->bind(':email', $datax['email']);
        $this->db->bind(':user_id', $datax['user_id']);
        $this->db->bind(':savings', $newFund);
        if ($this->db->execute()) {
          $this->db->query('INSERT INTO  vtu_services (user_id, email,transaction_id, vtupackage, amount, date) VALUES( :user_id, :email,:transaction_id, :vtupackage, :amount, :date)');
          // Bind Valuesrid
          $this->db->bind(':email', $datax['email']);
          $this->db->bind(':user_id', $datax['user_id']);
          $this->db->bind(':transaction_id', $datax['tr_id']);
          $this->db->bind(':vtupackage', $datax['serviceID']);
          $this->db->bind(':amount',"-".$datax['amount']);
          // Set the PHP timezone to GMT
        date_default_timezone_set('Europe/Berlin'); // or 'Africa/Lagos'
       $this->db->bind(':date', date('Y-m-d H:i:s'));
          if ($this->db->execute()) {
              //return true;
               // Insert into all_transactions table
             $this->db->query('INSERT INTO  all_transactions (user_id, email,transaction_id,transaction_type,transaction_status, vtupackage, amount, date, phone) VALUES( :user_id, :email,:transaction_id,:transaction_type,:transaction_status, :vtupackage, :amount, :date, :phone)');
          // Bind Valuesrid
          $this->db->bind(':email', $datax['email']);
          $this->db->bind(':user_id', $datax['user_id']);
          $this->db->bind(':transaction_id', $datax['tr_id']);
          $this->db->bind(':transaction_type', $datax['tr_type']);
          $this->db->bind(':transaction_status', "successful");
          $this->db->bind(':vtupackage', $datax['serviceID']);
          $this->db->bind(':amount',$datax['amount']);
          $this->db->bind(':phone',$datax['phone']);
         // Set the PHP timezone to GMT
        date_default_timezone_set('Europe/Berlin'); // or 'Africa/Lagos'
       $this->db->bind(':date', date('Y-m-d H:i:s'));
           if ($this->db->execute()) {
            $activity = $datax['response'];
            $processID = "log_" . md5(time());
            $this->db->query("INSERT INTO activity_log 
        (activity, user_id, activity_id, process_id, amount) 
        VALUES 
        (:activity, :user_id, :activity_id, :process_id, :amount)");

            $this->db->bind(":activity", $activity);
            $this->db->bind(":user_id", $datax["user_id"]);
            $this->db->bind(":activity_id", $datax["tr_id"]);
            $this->db->bind(":process_id", $processID);
            $this->db->bind(":amount", $datax["amount"]);

            if ($this->db->execute()) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
              
          } else {
                 $res = [
                'status' => 401,
                'message' => 'failed 22',
              ];
              print_r(json_encode($res));
              exit;
          }
        } else {
            $res = [
                'status' => 401,
                'message' => 'failed',
              ];
              print_r(json_encode($res));
              exit;
        }
        }else {
            $res = [
                'status' => 401,
                'message' => 'not enough Balance',
              ];
              print_r(json_encode($res));
              exit;
        }
       
      
      }
  
  
  
  
  
  
  
    }
    
    public function creatPayment($data)
{
    $this->db->query(
        "INSERT INTO payments (
            payment_id, payment_status, pay_address, price_amount, price_currency, 
            pay_amount, amount_received, pay_currency, order_id, order_description, 
            ipn_callback_url, created_at, updated_at, purchase_id, network, 
            expiration_estimate_date, is_fixed_rate, is_fee_paid_by_user, 
            valid_until, type, product, origin_ip, email, user_id
        ) VALUES (
            :payment_id, :payment_status, :pay_address, :price_amount, :price_currency, 
            :pay_amount, :amount_received, :pay_currency, :order_id, :order_description, 
            :ipn_callback_url, :created_at, :updated_at, :purchase_id, :network, 
            :expiration_estimate_date, :is_fixed_rate, :is_fee_paid_by_user, 
            :valid_until, :type, :product, :origin_ip, :email, :user_id
        ) ON DUPLICATE KEY UPDATE 
            payment_status = :payment_status, 
            updated_at = :updated_at,
            amount_received = :amount_received"
    );

    // Bind parameters
    $this->db->bind(':payment_id', $data['payment_id']);
    $this->db->bind(':payment_status', $data['payment_status']);
    $this->db->bind(':pay_address', $data['pay_address']);
    $this->db->bind(':price_amount', $data['price_amount']);
    $this->db->bind(':price_currency', $data['price_currency']);
    $this->db->bind(':pay_amount', $data['pay_amount']);
    $this->db->bind(':amount_received', $data['amount_received']);
    $this->db->bind(':pay_currency', $data['pay_currency']);
    $this->db->bind(':order_id', $data['order_id']);
    $this->db->bind(':order_description', $data['order_description']);
    $this->db->bind(':ipn_callback_url', $data['ipn_callback_url']);
    $this->db->bind(':created_at', $data['created_at']);
    $this->db->bind(':updated_at', $data['updated_at']);
    $this->db->bind(':purchase_id', $data['purchase_id']);
    $this->db->bind(':network', $data['network']);
    $this->db->bind(':expiration_estimate_date', $data['expiration_estimate_date']);
    $this->db->bind(':is_fixed_rate', $data['is_fixed_rate']);
    $this->db->bind(':is_fee_paid_by_user', $data['is_fee_paid_by_user']);
    $this->db->bind(':valid_until', $data['valid_until']);
    $this->db->bind(':type', $data['type']);
    $this->db->bind(':product', $data['product']);
    $this->db->bind(':origin_ip', $data['origin_ip']);
    $this->db->bind(':email', $data['email']);
    $this->db->bind(':user_id', $data['user_id']);

    // Execute query
    return $this->db->execute();
}

    
    
       public function  saveTransactionx($datax)
    {
  
  
      $this->db->query('SELECT * FROM user_accounts WHERE  email = :email and  user_id = :user_id ');
      // Bind Valuesrid
      $this->db->bind(':email', $datax['email']);
      $this->db->bind(':user_id', $datax['user_id']);
      $row = $this->db->single();
  
      // Check roow
      if ($this->db->rowCount() > 0) {
        if ($row->savings > $datax['amount']) {
           $newFund = $row->savings - $datax['amount'];
             $this->db->query('UPDATE user_accounts set savings = :savings WHERE  email = :email and  user_id = :user_id');
  
        $this->db->bind(':email', $datax['email']);
        $this->db->bind(':user_id', $datax['user_id']);
        $this->db->bind(':savings', $newFund);
        if ($this->db->execute()) {
          $this->db->query('INSERT INTO  vtu_services (user_id, email,transaction_id, vtupackage, amount, date) VALUES( :user_id, :email,:transaction_id, :vtupackage, :amount, :date)');
          // Bind Valuesrid
          $this->db->bind(':email', $datax['email']);
          $this->db->bind(':user_id', $datax['user_id']);
          $this->db->bind(':transaction_id', $datax['tr_id']);
          $this->db->bind(':vtupackage', $datax['serviceID']);
          $this->db->bind(':amount',"-".$datax['amount']);
          // Set the PHP timezone to GMT
        date_default_timezone_set('Europe/Berlin'); // or 'Africa/Lagos'
       $this->db->bind(':date', date('Y-m-d H:i:s'));
          if ($this->db->execute()) {
              //return true;
               // Insert into all_transactions table
             $this->db->query('INSERT INTO  all_transactions (user_id, email,transaction_id,transaction_type,transaction_status, vtupackage, amount, date, phone) VALUES( :user_id, :email,:transaction_id,:transaction_type,:transaction_status, :vtupackage, :amount, :date, :phone)');
          // Bind Valuesrid
          $this->db->bind(':email', $datax['email']);
          $this->db->bind(':user_id', $datax['user_id']);
          $this->db->bind(':transaction_id', $datax['tr_id']);
          $this->db->bind(':transaction_type', $datax['tr_type']);
          $this->db->bind(':transaction_status', "successful");
          $this->db->bind(':vtupackage', $datax['serviceID']);
          $this->db->bind(':amount',$datax['amount']);
          $this->db->bind(':phone',$datax['phone']);
         // Set the PHP timezone to GMT
        date_default_timezone_set('Europe/Berlin'); // or 'Africa/Lagos'
       $this->db->bind(':date', date('Y-m-d H:i:s'));
           if ($this->db->execute()) {
            $activity = $datax['response'];
            $processID = "log_" . md5(time());
            $this->db->query("INSERT INTO activity_log 
        (activity, user_id, activity_id, process_id, amount) 
        VALUES 
        (:activity, :user_id, :activity_id, :process_id, :amount)");

            $this->db->bind(":activity", $activity);
            $this->db->bind(":user_id", $datax["user_id"]);
            $this->db->bind(":activity_id", $datax["tr_id"]);
            $this->db->bind(":process_id", $processID);
            $this->db->bind(":amount", $datax["amount"]);

            if ($this->db->execute()) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
              
          } else {
                 $res = [
                'status' => 401,
                'message' => 'failed 22',
              ];
              print_r(json_encode($res));
              exit;
          }
        } else {
            $res = [
                'status' => 401,
                'message' => 'failed',
              ];
              print_r(json_encode($res));
              exit;
        }
        }else {
            $res = [
                'status' => 401,
                'message' => 'not enough Balance',
              ];
              print_r(json_encode($res));
              exit;
        }
       
      
      }
  
  
  
  
  
  
  
    }
    
    
    
      public function  inAppTransfer($datax)
  {
// print_r(json_encode($datax));
// exit;

      $this->db->query('INSERT INTO  transaction_history (sender_username,sender_name, receiver_username, receiver_name,sender_id,receiver_id, transaction_status, amount, transaction_id, date) VALUES( :sender_username,:sender_name, :receiver_username,:receiver_name,:sender_id, :receiver_id, :transaction_status, :amount, :transaction_id, :date)');
      // Bind Valuesrid
      $this->db->bind(':sender_username', $datax['s_tag']);
      $this->db->bind(':sender_name', $datax['s_name']);
      $this->db->bind(':receiver_username', $datax['r_tag']);
      $this->db->bind(':receiver_name', $datax['r_name']);
      $this->db->bind(':sender_id', $datax['s_id']);
      $this->db->bind(':receiver_id', $datax['r_id']);
      $this->db->bind(':transaction_status', $datax['tr_status']);
      $this->db->bind(':amount', $datax['amount']);
      $this->db->bind(':transaction_id', $datax['tr_id']);
      // Set the PHP timezone to GMT
      date_default_timezone_set('Europe/Berlin'); // or 'Africa/Lagos'
     $this->db->bind(':date', date('Y-m-d H:i:s'));
    
      //$this->db->execute();


      if ($this->db->execute()) {
          
          // Insert into all_transactions table
       $this->db->query('INSERT INTO  all_transactions (sender_username,sender_name, receiver_username, receiver_name,sender_id,receiver_id, transaction_status, amount, transaction_id, transaction_type, date) VALUES( :sender_username,:sender_name, :receiver_username,:receiver_name,:sender_id, :receiver_id, :transaction_status, :amount, :transaction_id, :transaction_type, :date)');
      // Bind Valuesrid
      $this->db->bind(':sender_username', $datax['s_tag']);
      $this->db->bind(':sender_name', $datax['s_name']);
      $this->db->bind(':receiver_username', $datax['r_tag']);
      $this->db->bind(':receiver_name', $datax['r_name']);
      $this->db->bind(':sender_id', $datax['s_id']);
      $this->db->bind(':receiver_id', $datax['r_id']);
      $this->db->bind(':transaction_status', $datax['tr_status']);
      $this->db->bind(':amount', $datax['amount']);
      $this->db->bind(':transaction_id', $datax['tr_id']);
      $this->db->bind(':transaction_type', 'inapp');
       // Set the PHP timezone to GMT
    date_default_timezone_set('Europe/Berlin'); // or 'Africa/Lagos'
      $this->db->bind(':date', date('Y-m-d H:i:s'));
    
      //$this->db->execute();


      if ($this->db->execute()) {
        //print_r($datax)
         // return true;
          return true;
      } else {
          return false;
      }



      } else {
        //   return false;
        if (!$this->db->execute()) {
    // Log or display the error
   echo "error here";
    return false;
}
      }




  }
    
      public function  campaignTransfer($datax)
  {
// print_r(json_encode($datax));
// exit;

      $this->db->query('INSERT INTO  transaction_history (sender_username,sender_name, receiver_username, receiver_name,sender_id,receiver_id, transaction_status, amount, transaction_id, date) VALUES( :sender_username,:sender_name, :receiver_username,:receiver_name,:sender_id, :receiver_id, :transaction_status, :amount, :transaction_id, :date)');
      // Bind Valuesrid
      $this->db->bind(':sender_username', $datax['s_tag']);
      $this->db->bind(':sender_name', $datax['s_name']);
      $this->db->bind(':receiver_username', $datax['r_tag']);
      $this->db->bind(':receiver_name', $datax['r_name']);
      $this->db->bind(':sender_id', $datax['s_id']);
      $this->db->bind(':receiver_id', $datax['r_id']);
      $this->db->bind(':transaction_status', $datax['tr_status']);
      $this->db->bind(':amount', $datax['amount']);
      $this->db->bind(':transaction_id', $datax['tr_id']);
      // Set the PHP timezone to GMT
      date_default_timezone_set('Europe/Berlin'); // or 'Africa/Lagos'
     $this->db->bind(':date', date('Y-m-d H:i:s'));
    
      //$this->db->execute();


      if ($this->db->execute()) {
          
          // Insert into all_transactions table
       $this->db->query('INSERT INTO  all_transactions (sender_username,sender_name, receiver_username, receiver_name,sender_id,receiver_id, transaction_status, amount, transaction_id, transaction_type, date) VALUES( :sender_username,:sender_name, :receiver_username,:receiver_name,:sender_id, :receiver_id, :transaction_status, :amount, :transaction_id, :transaction_type, :date)');
      // Bind Valuesrid
      $this->db->bind(':sender_username', $datax['s_tag']);
      $this->db->bind(':sender_name', $datax['s_name']);
      $this->db->bind(':receiver_username', $datax['r_tag']);
      $this->db->bind(':receiver_name', $datax['r_name']);
      $this->db->bind(':sender_id', $datax['s_id']);
      $this->db->bind(':receiver_id', $datax['r_id']);
      $this->db->bind(':transaction_status', $datax['tr_status']);
      $this->db->bind(':amount', $datax['amount']);
      $this->db->bind(':transaction_id', $datax['tr_id']);
      $this->db->bind(':transaction_type', 'inapp');
       // Set the PHP timezone to GMT
    date_default_timezone_set('Europe/Berlin'); // or 'Africa/Lagos'
      $this->db->bind(':date', date('Y-m-d H:i:s'));
    
      //$this->db->execute();


      if ($this->db->execute()) {
         $activity = "CAMPAIGN TRANSFER";
            $processID = "log_" . md5(time());
            $this->db->query("INSERT INTO activity_log 
        (activity, user_id, activity_id, process_id, amount) 
        VALUES 
        (:activity, :user_id, :activity_id, :process_id, :amount)");

            $this->db->bind(":activity", $activity);
            $this->db->bind(":user_id", $datax["s_id"]);
            $this->db->bind(":activity_id", $datax["tr_id"]);
            $this->db->bind(":process_id", $processID);
            $this->db->bind(":amount", $datax["amount"]);

            if ($this->db->execute()) {
                 $this->db->query("INSERT INTO campaign_tr 
        (campaign_id, user_id, amount) 
        VALUES 
        (:campaign_id, :user_id, :amount)");
        $this->db->bind(":campaign_id", $datax['campaign_id']);
            $this->db->bind(":user_id", $datax["s_id"]);

            $this->db->bind(":amount", $datax["amount"]);
            if($this->db->execute()){
                return true;
                
            }else{
                return false;
            }
            } else {
                return false;
            }
      } else {
          return false;
      }



      } else {
        //   return false;
        if (!$this->db->execute()) {
    // Log or display the error
   echo "error here";
    return false;
}
      }




  }
  
  
  
      public function  ticketTransfer($datax)
  {
// print_r(json_encode($datax));
// exit;

      $this->db->query('INSERT INTO  transaction_history (sender_username,sender_name, receiver_username, receiver_name,sender_id,receiver_id, transaction_status, amount, transaction_id, date) VALUES( :sender_username,:sender_name, :receiver_username,:receiver_name,:sender_id, :receiver_id, :transaction_status, :amount, :transaction_id, :date)');
      // Bind Valuesrid
      $this->db->bind(':sender_username', $datax['s_tag']);
      $this->db->bind(':sender_name', $datax['s_name']);
      $this->db->bind(':receiver_username', $datax['r_tag']);
      $this->db->bind(':receiver_name', $datax['r_name']);
      $this->db->bind(':sender_id', $datax['s_id']);
      $this->db->bind(':receiver_id', $datax['r_id']);
      $this->db->bind(':transaction_status', $datax['tr_status']);
      $this->db->bind(':amount', $datax['amount']);
      $this->db->bind(':transaction_id', $datax['tr_id']);
      // Set the PHP timezone to GMT
      date_default_timezone_set('Europe/Berlin'); // or 'Africa/Lagos'
     $this->db->bind(':date', date('Y-m-d H:i:s'));
    
      //$this->db->execute();


      if ($this->db->execute()) {
          
          // Insert into all_transactions table
       $this->db->query('INSERT INTO  all_transactions (sender_username,sender_name, receiver_username, receiver_name,sender_id,receiver_id, transaction_status, amount, transaction_id, transaction_type, date) VALUES( :sender_username,:sender_name, :receiver_username,:receiver_name,:sender_id, :receiver_id, :transaction_status, :amount, :transaction_id, :transaction_type, :date)');
      // Bind Valuesrid
      $this->db->bind(':sender_username', $datax['s_tag']);
      $this->db->bind(':sender_name', $datax['s_name']);
      $this->db->bind(':receiver_username', $datax['r_tag']);
      $this->db->bind(':receiver_name', $datax['r_name']);
      $this->db->bind(':sender_id', $datax['s_id']);
      $this->db->bind(':receiver_id', $datax['r_id']);
      $this->db->bind(':transaction_status', $datax['tr_status']);
      $this->db->bind(':amount', $datax['amount']);
      $this->db->bind(':transaction_id', $datax['tr_id']);
      $this->db->bind(':transaction_type', 'inapp');
       // Set the PHP timezone to GMT
    date_default_timezone_set('Europe/Berlin'); // or 'Africa/Lagos'
      $this->db->bind(':date', date('Y-m-d H:i:s'));
    
      //$this->db->execute();


      if ($this->db->execute()) {
         $activity = "CAMPAIGN TRANSFER";
            $processID = "log_" . md5(time());
            $this->db->query("INSERT INTO activity_log 
        (activity, user_id, activity_id, process_id, amount) 
        VALUES 
        (:activity, :user_id, :activity_id, :process_id, :amount)");

            $this->db->bind(":activity", $activity);
            $this->db->bind(":user_id", $datax["s_id"]);
            $this->db->bind(":activity_id", $datax["tr_id"]);
            $this->db->bind(":process_id", $processID);
            $this->db->bind(":amount", $datax["amount"]);

            if ($this->db->execute()) {
                 $this->db->query("INSERT INTO ticket_tr 
        (event_id, user_id, amount) 
        VALUES 
        (:event_id, :user_id, :amount)");
        $this->db->bind(":event_id", $datax['campaign_id']);
            $this->db->bind(":user_id", $datax["s_id"]);

            $this->db->bind(":amount", $datax["amount"]);
            if($this->db->execute()){
                return true;
                
            }else{
                return false;
            }
            } else {
                return false;
            }
      } else {
          return false;
      }



      } else {
        //   return false;
        if (!$this->db->execute()) {
    // Log or display the error
   echo "error here";
    return false;
}
      }




  }
  public function  inAppTransfer2($datax)
  {
// print_r(json_encode($datax));
// exit;

      $this->db->query('INSERT INTO  transaction_history (sender_username,sender_name, receiver_username, receiver_name,sender_id,receiver_id, transaction_status, amount, transaction_id, date) VALUES( :sender_username,:sender_name, :receiver_username,:receiver_name,:sender_id, :receiver_id, :transaction_status, :amount, :transaction_id, :date)');
      // Bind Valuesrid
      $this->db->bind(':sender_username', $datax['s_tag']);
      $this->db->bind(':sender_name', $datax['s_name']);
      $this->db->bind(':receiver_username', $datax['r_tag']);
      $this->db->bind(':receiver_name', $datax['r_name']);
      $this->db->bind(':sender_id', $datax['s_id']);
      $this->db->bind(':receiver_id', $datax['r_id']);
      $this->db->bind(':transaction_status', $datax['tr_status']);
      $this->db->bind(':amount', $datax['amount']);
      $this->db->bind(':transaction_id', $datax['tr_id']);
      // Set the PHP timezone to GMT
      date_default_timezone_set('Europe/Berlin'); // or 'Africa/Lagos'
     $this->db->bind(':date', $datax['date']);
    
      //$this->db->execute();


      if ($this->db->execute()) {
          
          // Insert into all_transactions table
       $this->db->query('INSERT INTO  all_transactions (sender_username,sender_name, receiver_username, receiver_name,sender_id,receiver_id, transaction_status, amount, transaction_id, transaction_type, date) VALUES( :sender_username,:sender_name, :receiver_username,:receiver_name,:sender_id, :receiver_id, :transaction_status, :amount, :transaction_id, :transaction_type, :date)');
      // Bind Valuesrid
      $this->db->bind(':sender_username', $datax['s_tag']);
      $this->db->bind(':sender_name', $datax['s_name']);
      $this->db->bind(':receiver_username', $datax['r_tag']);
      $this->db->bind(':receiver_name', $datax['r_name']);
      $this->db->bind(':sender_id', $datax['s_id']);
      $this->db->bind(':receiver_id', $datax['r_id']);
      $this->db->bind(':transaction_status', $datax['tr_status']);
      $this->db->bind(':amount', $datax['amount']);
      $this->db->bind(':transaction_id', $datax['tr_id']);
      $this->db->bind(':transaction_type', 'inapp');
       // Set the PHP timezone to GMT
    date_default_timezone_set('Europe/Berlin'); // or 'Africa/Lagos'
       $this->db->bind(':date', $datax['date']);
    
      //$this->db->execute();


      if ($this->db->execute()) {
        //print_r($datax)
         // return true;
          return true;
      } else {
          return false;
      }



      } else {
        //   return false;
        if (!$this->db->execute()) {
    // Log or display the error
   echo "error here";
    return false;
}
      }




  }
  public function  pendinginAppTransfer($datax)
  {
// print_r(json_encode($datax));
// exit;

      $this->db->query('INSERT INTO  pending_transaction_history (sender_username,sender_name, receiver_username, receiver_name,sender_id,receiver_id, transaction_status, targettime, amount, transaction_id, date) VALUES( :sender_username,:sender_name, :receiver_username,:receiver_name,:sender_id, :receiver_id, :transaction_status, :targettime, :amount, :transaction_id, :date)');
      // Bind Valuesrid
      $this->db->bind(':sender_username', $datax['s_tag']);
      $this->db->bind(':sender_name', $datax['s_name']);
      $this->db->bind(':receiver_username', $datax['r_tag']);
      $this->db->bind(':receiver_name', $datax['r_name']);
      $this->db->bind(':sender_id', $datax['s_id']);
      $this->db->bind(':receiver_id', $datax['r_id']);
      $this->db->bind(':transaction_status', 'pending');
      $this->db->bind(':targettime', $datax['targettime']);
      $this->db->bind(':amount', $datax['amount']);
      $this->db->bind(':transaction_id', $datax['tr_id']);
      // Set the PHP timezone to GMT
      date_default_timezone_set('Europe/Berlin'); // or 'Africa/Lagos'
     $this->db->bind(':date', date('Y-m-d H:i:s'));
    
      //$this->db->execute();


      if ($this->db->execute()) {
          
       return true;

      } else {
        //   return false;
        if (!$this->db->execute()) {
    // Log or display the error
   echo "error here";
    return false;
}
      }




  }
    public function  campaignUpdate($datax)
  {

      $this->db->query('SELECT * FROM user_accounts WHERE  email = :email and  user_id = :user_id ');
      // Bind Valuesrid
      $this->db->bind(':email', $datax['s_e']);
      $this->db->bind(':user_id', $datax['s_id']);
      $row = $this->db->single();

      // Check roow
      if ($this->db->rowCount() > 0) {
        if ($row->savings > $datax['amount']) {
           $newFund = $row->savings - $datax['amount'];
             $this->db->query('UPDATE user_accounts set savings = :savings WHERE  email = :email and  user_id = :user_id');

        $this->db->bind(':email', $datax['s_e']);
        $this->db->bind(':user_id', $datax['s_id']);
        $this->db->bind(':savings', $newFund);
        if ($this->db->execute()) {
                    $this->db->query('SELECT * FROM user_accounts WHERE  email = :email and  user_id = :user_id ');
        // Bind Valuesrid
        $this->db->bind(':email', $datax['r_e']);
        $this->db->bind(':user_id', $datax['r_id']);
                $rowx = $this->db->single();
        if ($this->db->rowCount() > 0) {
        
            $newFundx = $rowx->campaign_account + $datax['amount'];
            $this->db->query('UPDATE user_accounts set campaign_account = :campaign_account WHERE  email = :email and  user_id = :user_id');

            $this->db->bind(':email', $datax['r_e']);
            $this->db->bind(':user_id', $datax['r_id']);
            $this->db->bind(':campaign_account', $newFundx);
            if ($this->db->execute()) {
                 $this->db->query('SELECT * FROM campaign_details WHERE  email = :email and  user_id = :user_id and campaign_id = :campaign_id');
        // Bind Valuesrid
        $this->db->bind(':email', $datax['r_e']);
        $this->db->bind(':user_id', $datax['r_id']);
        $this->db->bind(':campaign_id', $datax['campaign_id']);
                $rowx = $this->db->single();
                 if ($this->db->rowCount() > 0) {
                     $newFundx = $rowx->current_amount + $datax['amount'];
                      $this->db->query('UPDATE campaign_details set current_amount = :current_amount WHERE  email = :email and  user_id = :user_id and campaign_id = :campaign_id');
                       $this->db->bind(':current_amount', $newFundx);
                       $this->db->bind(':email', $datax['r_e']);
        $this->db->bind(':user_id', $datax['r_id']);
        $this->db->bind(':campaign_id', $datax['campaign_id']);
        if ($this->db->execute()) {
            return true;
        }else{
            return false;
        }
                 }
            } else {
                $res = [
                    'status' => 401,
                    'message' => 'failed',
                ];
                print_r(json_encode($res));
                exit;
            }
        }else {
            return false;
        }

        } else {
            $res = [
                'status' => 401,
                'message' => 'failed',
              ];
              print_r(json_encode($res));
              exit;
        }

      } else {
        return false;
      }
        }else {
            $res = [
                'status' => 401,
                'message' => 'not enough Balance',
              ];
              print_r(json_encode($res));
              exit;
        }
       
       





  } 
  
  
    public function  ticketUpdate($datax)
  {

      $this->db->query('SELECT * FROM user_accounts WHERE  email = :email and  user_id = :user_id ');
      // Bind Valuesrid
      $this->db->bind(':email', $datax['s_e']);
      $this->db->bind(':user_id', $datax['s_id']);
      $row = $this->db->single();

      // Check roow
      if ($this->db->rowCount() > 0) {
        if ($row->savings > $datax['amount']) {
           $newFund = $row->savings - $datax['amount'];
             $this->db->query('UPDATE user_accounts set savings = :savings WHERE  email = :email and  user_id = :user_id');

        $this->db->bind(':email', $datax['s_e']);
        $this->db->bind(':user_id', $datax['s_id']);
        $this->db->bind(':savings', $newFund);
        if ($this->db->execute()) {
                    $this->db->query('SELECT * FROM user_accounts WHERE  email = :email and  user_id = :user_id ');
        // Bind Valuesrid
        $this->db->bind(':email', $datax['r_e']);
        $this->db->bind(':user_id', $datax['r_id']);
                $rowx = $this->db->single();
        if ($this->db->rowCount() > 0) {
        
            $newFundx = $rowx->ticket_account + $datax['amount'];
            $this->db->query('UPDATE user_accounts set event_account = :event_account WHERE  email = :email and  user_id = :user_id');

            $this->db->bind(':email', $datax['r_e']);
            $this->db->bind(':user_id', $datax['r_id']);
            $this->db->bind(':event_account', $newFundx);
            if ($this->db->execute()) {
                return true;
            } else {
                // $res = [
                //     'status' => 401,
                //     'message' => 'failed',
                // ];
                // print_r(json_encode($res));
                // exit;
                return false;
            }
        }else {
            return false;
        }

        } else {
            $res = [
                'status' => 401,
                'message' => 'failed',
              ];
              print_r(json_encode($res));
              exit;
        }

      } else {
        return false;
      }
        }else {
            $res = [
                'status' => 401,
                'message' => 'not enough Balance',
              ];
              print_r(json_encode($res));
              exit;
        }
       
       





  }
  
  
    public function  accountUpdate($datax)
  {

      $this->db->query('SELECT * FROM user_accounts WHERE  email = :email and  user_id = :user_id ');
      // Bind Valuesrid
      $this->db->bind(':email', $datax['s_e']);
      $this->db->bind(':user_id', $datax['s_id']);
      $row = $this->db->single();

      // Check roow
      if ($this->db->rowCount() > 0) {
        if ($row->savings > $datax['amount']) {
           $newFund = $row->savings - $datax['amount'];
             $this->db->query('UPDATE user_accounts set savings = :savings WHERE  email = :email and  user_id = :user_id');

        $this->db->bind(':email', $datax['s_e']);
        $this->db->bind(':user_id', $datax['s_id']);
        $this->db->bind(':savings', $newFund);
        if ($this->db->execute()) {
                    $this->db->query('SELECT * FROM user_accounts WHERE  email = :email and  user_id = :user_id ');
        // Bind Valuesrid
        $this->db->bind(':email', $datax['r_e']);
        $this->db->bind(':user_id', $datax['r_id']);
                $rowx = $this->db->single();
        if ($this->db->rowCount() > 0) {
        
            $newFundx = $rowx->savings + $datax['amount'];
            $this->db->query('UPDATE user_accounts set savings = :savings WHERE  email = :email and  user_id = :user_id');

            $this->db->bind(':email', $datax['r_e']);
            $this->db->bind(':user_id', $datax['r_id']);
            $this->db->bind(':savings', $newFundx);
            if ($this->db->execute()) {
                return true;
            } else {
                $res = [
                    'status' => 401,
                    'message' => 'failed',
                ];
                print_r(json_encode($res));
                exit;
            }
        }else {
            return false;
        }

        } else {
            $res = [
                'status' => 401,
                'message' => 'failed',
              ];
              print_r(json_encode($res));
              exit;
        }

      } else {
        return false;
      }
        }else {
            $res = [
                'status' => 401,
                'message' => 'not enough Balance',
              ];
              print_r(json_encode($res));
              exit;
        }
       
       





  }
 public function pendingupdate($datax)
{
    $this->db->query('UPDATE pending_transaction_history 
                      SET transaction_status = :transaction_status 
                      WHERE transaction_id = :transaction_id');

    // Bind Values
    $this->db->bind(':transaction_status', 'successful');
    $this->db->bind(':transaction_id', $datax['tr_id']);

    // Set the PHP timezone to GMT
    date_default_timezone_set('Europe/Berlin'); // or 'Africa/Lagos'
    // $this->db->bind(':date', date('Y-m-d H:i:s'));

    if ($this->db->execute()) {
        return true;
    } else {
        // Log or display the error
        echo "Error updating transaction status";
        return false;
    }
}

}
