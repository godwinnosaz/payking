<?php
class User
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }


    public function findLoginByToken($token)
    {
        $this->db->query('SELECT * FROM initkey WHERE token= :token');
        $this->db->bind(':token', $token);

        $row = $this->db->single();
    
        
        if($this->db->rowCount() > 0){
            return $row;
        } else{
            return false;
        
        
        
        }


    }
    
  public function getNotifications($id) {
    $this->db->query("SELECT * FROM notification WHERE user_id = :id OR user_id = '' OR user_id = 0 ORDER BY id DESC");

    $this->db->bind(':id', $id);
    $rows = $this->db->resultSet();

    if ($rows) {
        return $rows;
    } else {
        return [];
    }
}

 
    public function setNotifications($datax){
       $this->db->query('INSERT INTO  notification (headers, text,img, date, user_id) VALUES( :headers, :text,:img, :date, :user_id)');
      // Bind Valuesrid
       $this->db->bind(':headers', $datax['headers']);
      $this->db->bind(':text', $datax['text']);
      $this->db->bind(':img', $datax['img']);
       date_default_timezone_set('Europe/Berlin'); // or 'Africa/Lagos'
     $this->db->bind(':date', date('Y-m-d H:i:s'));
     $this->db->bind(':user_id', $datax['user_id']);
    
      if ($this->db->execute())
      {
          return true;
      }else 
      {
          return false;
      }
       
    
  }
    
    
      public function saveCrowdFunding($data)
    {
        $this->db->query(" INSERT INTO  campaign_details 
            SET 
                email = :email, 
                user_id = :user_id, 
                campaign_name = :campaign_name,
                category = :category,
                target_amount = :amount,
                `desc` = :desc,
                image = :image,
                campaign_id = :campaign_id,
                user_name = :user_name
                ");
        $this->db->bind(":email", $data["email"]);
        $this->db->bind(":user_id", $data["user_id"]);
        $this->db->bind(":campaign_name", $data["campaign_name"]);
        $this->db->bind(":category", $data["category"]);
        $this->db->bind(":amount", $data["amount"]);
        $this->db->bind(":desc", $data["desc"]);
        $this->db->bind(":image", $data["image"]);
        $this->db->bind(":campaign_id", $data["campaign_id"]);
        $this->db->bind(":user_name", $data["user_name"]);
        if ($this->db->execute()) {
             $activity = "CROWD FUNDING";
                            $processID = "log_" . md5(time());
                            $this->db->query("INSERT INTO activity_log 
                            (activity, user_id, activity_id, process_id, amount) 
                            VALUES 
                            (:activity, :user_id, :activity_id, :process_id, :amount)");

                            $this->db->bind(":activity", $activity);
                            $this->db->bind(":user_id", $data["user_id"]);
                            $this->db->bind(":activity_id", $data["campaign_id"]);
                            $this->db->bind(":process_id", $processID);
                            $this->db->bind(":amount", $data["amount"]);
                if ($this->db->execute()) {
                    return true;
                } else {
                    return false;
                }
        } else {
            return false;
        }
    }
      public function saveticket($data)
    {
        $this->db->query(" INSERT INTO  ticket_details 
            SET 
                email = :email, 
                user_id = :user_id, 
                event_name = :event_name,
                price = :price,
                username = :username,
                description = :description,
                no_of_ticket = :no_of_ticket,
                `time` = :time,
                image = :image,
                address = :address,
                state = :state,
                event_id = :event_id,
                date = :date
                ");
        $this->db->bind(":email", $data["email"]);
        $this->db->bind(":user_id", $data["user_id"]);
        $this->db->bind(":event_name", $data["event_name"]);
        $this->db->bind(":price", $data["price"]);
        $this->db->bind(":username", $data["username"]);
        $this->db->bind(":description", $data["desc"]);
        $this->db->bind(":no_of_ticket", $data["no_of_ticket"]);
        $this->db->bind(":time", $data["time"]);
        $this->db->bind(":image", $data["image"]);
        $this->db->bind(":address", $data["address"]);
        $this->db->bind(":state", $data["state"]);
        $this->db->bind(":event_id", $data["event_id"]);
        $this->db->bind(":date", $data["date"]);
        if ($this->db->execute()) {
             $activity = "TICKET CREATION";
                            $processID = "log_" . md5(time());
                            $this->db->query("INSERT INTO activity_log 
                            (activity, user_id, activity_id, process_id, amount) 
                            VALUES 
                            (:activity, :user_id, :activity_id, :process_id, :amount)");

                            $this->db->bind(":activity", $activity);
                            $this->db->bind(":user_id", $data["user_id"]);
                            $this->db->bind(":activity_id", $data["event_id"]);
                            $this->db->bind(":process_id", $processID);
                            $this->db->bind(":amount", $data["price"]);
                if ($this->db->execute()) {
                    return true;
                } else {
                    return false;
                }
        } else {
            return false;
        }
    }
      public function createAccount($data)
    {
        $this->db->query(" INSERT INTO  user_accounts 
            SET 
                email = :email, 
                user_id = :user_id, 
                account_id = :account_id");
        $this->db->bind(":email", $data["email"]);
        $this->db->bind(":user_id", $data["user_id"]);
        $this->db->bind(":account_id", $data["account_id"]);
        if ($this->db->execute()) {
            $this->db->query(" UPDATE   userprofile 
            SET 
                account_id = :account_id
                 WHERE  user_id = :user_id AND   email = :email");
                $this->db->bind(":email", $data["email"]);
                $this->db->bind(":user_id", $data["user_id"]);
                $this->db->bind(":account_id", $data["account_id"]);
                if ($this->db->execute()) {
                    return true;
                } else {
                    return false;
                }
        } else {
            return false;
        }
    }
      public function storeTransactionKey($otp, $id, $email)
    {
     
            $this->db->query(" UPDATE   initkey 
            SET 
                security_key = :security_key,
                used_key = :used_key
                 WHERE  user_id = :user_id AND   email = :email");
                $this->db->bind(":email", $email);
                $this->db->bind(":user_id", $id);
                $this->db->bind(":security_key", $otp);
                $this->db->bind(":used_key", 0);
                if ($this->db->execute()) {
                    return true;
                } else {
                    return false;
                }
      
    }
      public function security_use($id, $email)
    {
     
            $this->db->query(" UPDATE   initkey 
            SET 
                used_key = :used_key
                 WHERE  user_id = :user_id AND   email = :email");
                $this->db->bind(":email", $email);
                $this->db->bind(":user_id", $id);
                $this->db->bind(":used_key", 1);
                if ($this->db->execute()) {
                    return true;
                } else {
                    return false;
                }
      
    }



        public function getAccountDetails($email, $id)
    {
        $this->db->query("SELECT * FROM user_accounts WHERE  user_id = :user_id AND account_id = :account_id");

        // Bind Values
        $this->db->bind(':user_id', $id);
        $this->db->bind(':account_id', $email);

        $row = $this->db->single();

        if ($this->db->rowCount() > 0) {
            return $row;
        } else {

            return false;
        }
    }



    public function loginUser($email)
    {
        $this->db->query('SELECT * FROM initkey  WHERE email= :email');
        $this->db->bind(':email', $email);

        $row = $this->db->single();
       
        //return $row;
        if($this->db->rowCount() > 0){
            return $row;
        } else {
          
           return false;
       
        
        }


    }





    //Find user by email
    public function findUserByEmail_det($email)
    {
        $this->db->query("SELECT * FROM initkey WHERE  email = :email");

        // Bind Values
        $this->db->bind(':email', $email);

        $row = $this->db->single();

        if($this->db->rowCount() > 0){
        return $row;
        }else{
            
            return false;
        }
    
    }
    public function findUserByEmail_dett($email)
    {
        $this->db->query("SELECT * FROM initkey_ WHERE  email = :email");

        // Bind Values
        $this->db->bind(':email', $email);

        $row = $this->db->single();

        if($this->db->rowCount() > 0){
        return $row;
        }else{
            
            return false;
        }
    
    }
    public function findUserByEmail_dett_admin($email)
    {
        $this->db->query("SELECT * FROM initkey_admin_ WHERE  email = :email");

        // Bind Values
        $this->db->bind(':email', $email);

        $row = $this->db->single();

        if($this->db->rowCount() > 0){
        return $row;
        }else{
            
            return false;
        }
    
    }
    public function findUserByEmail_det2($email)
    {
        $this->db->query("SELECT * FROM userprofile WHERE  email = :email");

        // Bind Values
        $this->db->bind(':email', $email);

        $row = $this->db->single();

        if($this->db->rowCount() > 0){
        return $row;
        }else{
            
            return false;
        }
    
    }


public function findUserByEmail($email)
{
    $this->db->query("SELECT * FROM initkey WHERE email = :email AND activationx = 1");

    // Bind Values
    $this->db->bind(':email', $email);

    $row = $this->db->single();

    // Check row
    if ($this->db->rowCount() > 0) {
        return true;
    } else {
        return false;
    }
}

public function findUserByEmail1($email)
{
    $this->db->query("SELECT * FROM initkey WHERE email = :email");

    // Bind Values
    $this->db->bind(':email', $email);

    $row = $this->db->single();

    // Check row
    if ($this->db->rowCount() > 0) {
        return true;
    } else {
        return false;
    }
}
public function findUserByEmail360x($email)
{
    $this->db->query("SELECT * FROM initkey WHERE email = :email");

    // Bind Values
    $this->db->bind(':email', $email);

    $row = $this->db->single();

    // Check row
    if ($this->db->rowCount() > 0) {
        return $row;
    } else {
        return false;
    }
}
public function findUserByPhone($email)
{
    $this->db->query("SELECT * FROM initkey WHERE phone = :phone");

    // Bind Values
    $this->db->bind(':phone', $email);

    $row = $this->db->single();

    // Check row
    if ($this->db->rowCount() > 0) {
        return true;
    } else {
        return false;
    }
}
public function findHospitalByEmail2($email)
{
    $this->db->query("SELECT * FROM hospitaldetails WHERE email = :email");

    // Bind Values
    $this->db->bind(':email', $email);

    $row = $this->db->single();

    // Check row
    if ($this->db->rowCount() > 0) {
        return true;
    } else {
        return false;
    }
}

 


    //Get user by Id
    public function getUserByid($id)
    {
        $this->db->query("SELECT * FROM initkey WHERE  user_id = :user_id");

        // Bind Values
        $this->db->bind(':user_id', $id);

        $row1 = $this->db->single();

        // Check roow
       
         if ($this->db->rowCount() > 0) {
             return $row1;
        } else {
          
            return false;
        }
    
    }

    public function updateResetToken($data)
    {
        $tokenTime = 15;
        $dateTime = new DateTime();
        $currentDate = $dateTime->format('Y-m-d H:i:s');

        $this->db->query('UPDATE  initkey SET password_reset_token = :password_reset_token,password_reset_token_time = :password_reset_token_time, reset_token_set_date = :reset_token_set_date  WHERE email = :email ');
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':password_reset_token', $data['otp']);
        $this->db->bind(':reset_token_set_date', $currentDate);
        $this->db->bind(':password_reset_token_time', $tokenTime);
        // Execute
        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }
    public function updateResetToken2($data)
    {
        $tokenTime = 15;
        $dateTime = new DateTime();
        $currentDate = $dateTime->format('Y-m-d H:i:s');

        $this->db->query('UPDATE  initkey_ SET email_reset_token = :email_reset_token,password_reset_token_time = :password_reset_token_time, reset_token_set_date = :reset_token_set_date  WHERE email = :email ');
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':email_reset_token', $data['otp']);
        $this->db->bind(':reset_token_set_date', $currentDate);
        $this->db->bind(':password_reset_token_time', $tokenTime);
        // Execute
        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }
    public function updateResetToken32($data)
    {
        $tokenTime = 15;
        $dateTime = new DateTime();
        $currentDate = $dateTime->format('Y-m-d H:i:s');

        $this->db->query('UPDATE  initkey_ SET email_reset_token = :email_reset_token,password_reset_token_time = :password_reset_token_time, reset_token_set_date = :reset_token_set_date  WHERE email = :email ');
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':email_reset_token', $data['otp']);
        $this->db->bind(':reset_token_set_date', $currentDate);
        $this->db->bind(':password_reset_token_time', $tokenTime);
        // Execute
        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }
    public function updateResetToken23($data, $email)
    {
        $tokenTime = 15;
        $dateTime = new DateTime();
        $currentDate = $dateTime->format('Y-m-d H:i:s');

        $this->db->query('UPDATE  initkey_ SET email_reset_token = :email_reset_token,password_reset_token_time = :password_reset_token_time, reset_token_set_date = :reset_token_set_date  WHERE email = :email ');
        $this->db->bind(':email', $email);
        $this->db->bind(':email_reset_token', $data['otp']);
        $this->db->bind(':reset_token_set_date', $currentDate);
        $this->db->bind(':password_reset_token_time', $tokenTime);
        // Execute
        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }
    public function updateResetToken23456($email)
    {
        $this->db->query('UPDATE  initkey SET phone_validated = :phone_validated WHERE email = :email ');
        $this->db->bind(':email', $email);
        $this->db->bind(':phone_validated', 1);
        // Execute
        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }
    public function updateResetToken234($data, $email)
    {
        $tokenTime = 15;
        $dateTime = new DateTime();
        $currentDate = $dateTime->format('Y-m-d H:i:s');

        $this->db->query('UPDATE  initkey SET email_reset_token = :email_reset_token,password_reset_token_time = :password_reset_token_time, reset_token_set_date = :reset_token_set_date, phone = :phone  WHERE email = :email ');
        $this->db->bind(':email', $email);
        $this->db->bind(':email_reset_token', $data['otp']);
        $this->db->bind(':phone', $data['mobileNumber']);
        $this->db->bind(':reset_token_set_date', $currentDate);
        $this->db->bind(':password_reset_token_time', $tokenTime);
        // Execute
        if ($this->db->execute()) {
               $this->db->query('UPDATE  userprofile SET phone = :phone  WHERE email = :email ');
        $this->db->bind(':email', $email);
         $this->db->bind(':phone', $data['mobileNumber']);
         
          if ($this->db->execute()) {
            return true;
          }else{
              return false;
          }
        } else {
            return false;
        }
    }
    public function findAllUsers()
    {
        $this->db->query("SELECT * FROM userprofile");

        $rows = $this->db->resultSet();

        // Check roow
        return $rows;

    }
    public function findAllUsers2()
    {
        $this->db->query("SELECT * FROM initkey");

        $rows = $this->db->resultSet();

        // Check roow
        return $rows;

    }
    public function updatePassword($data)
    {
        $tokenTime = 0;
        $dateTime = new DateTime();
        $currentDate = $dateTime->format('Y-m-d H:i:s');

        $this->db->query('UPDATE  initkey SET password_reset_token = :password_reset_token,password_reset_token_time = :password_reset_token_time, password = :password  WHERE email = :email AND user_id = :user_id ');
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':user_id', $data['user_id']);
        $this->db->bind(':password_reset_token', 0);
        $this->db->bind(':password', $data['password']);
        $this->db->bind(':password_reset_token_time', $tokenTime);

        // Execute
        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }
   
    public function activateAccount($data)
    {
        $tokenTime = 0;
        $dateTime = new DateTime();
        $currentDate = $dateTime->format('Y-m-d H:i:s');
        $activation = 1;
        $this->db->query('UPDATE  initkey SET activationx = :activationx WHERE email = :email AND user_id = :user_id ');
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':user_id', $data['user_id']);
        $this->db->bind(':activationx', $activation);

        // Execute
        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }
    public function activateAccount_admin($data)
    {
        $tokenTime = 0;
        $dateTime = new DateTime();
        $currentDate = $dateTime->format('Y-m-d H:i:s');
        $activation = 1;
        $this->db->query('UPDATE  initkey_admin SET activationx = :activationx WHERE email = :email AND user_id = :user_id ');
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':user_id', $data['user_id']);
        $this->db->bind(':activationx', $activation);

        // Execute
        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }
    public function setTransferPin($pin, $id)
    {
        $this->db->query('UPDATE  initkey SET transfer_pin = :transfer_pin WHERE user_id = :user_id ');
        $this->db->bind(':user_id', $id);
        $this->db->bind(':transfer_pin', $pin);

        // Execute
        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }
    public function setLockPin($pin, $id)
    {
        $this->db->query('UPDATE  initkey SET lock_pin = :lock_pin WHERE user_id = :user_id ');
        $this->db->bind(':user_id', $id);
        $this->db->bind(':lock_pin', $pin);

        // Execute
        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }
   
    public function getUser($id)
    {
        $this->db->query("SELECT * FROM userprofile WHERE  user_id = :user_id");

        // Bind Values
        $this->db->bind(':user_id', $id);

        $row1 = $this->db->single();

        // Check roow
       
         if ($this->db->rowCount() > 0) {
             return $row1;
        } else {
          
            return false;
        }
    
    }
    public function getVtu()
    {
        $this->db->query("SELECT * FROM vtu_services");

  
        $row1 = $this->db->resultSet();

        // Check roow
       
         if ($this->db->rowCount() > 0) {
             return $row1;
        } else {
          
            return false;
        }
    
    }

    public function register_admin($data) {

        $login = 1;
        $active = "PAYKING_".md5(time());
        $this->db->query(" INSERT INTO  initkey
            SET 
                email = :email, 
                user_id = :user_id, 
                accessStatus = :accessStatus, 
                password = :password,
                 activeCode = :activeCode,
                 roleID = :roleID,
                 uname = :uname,
                 registerer_id = :registerer_id,
                loginStatus = :loginStatus");
        $this->db->bind(":email", $data["email"]);
        // $this->db->bind(":uname", "");
        $this->db->bind(":user_id", $data["user_id"]);
        $this->db->bind(":accessStatus", $login);
        $this->db->bind(":password", $data["password"]);
        $this->db->bind(":activeCode", $active);
        $this->db->bind(":roleID", 8);
        $this->db->bind(":uname", 'Customer Rep');
        $this->db->bind(":registerer_id", $data['reg_id']);
        $this->db->bind(":loginStatus", $login);
       if ($this->db->execute()) {
        $this->db->query(" INSERT INTO  userprofile 
        SET 
            fullname = :fullname,
            email = :email, 

            user_id = :user_id,  
            status = :status, 
            image = :image, 
              roleID = :roleID,
                 uname = :uname,
                 registerer_id = :registerer_id,
            password = :password,
             activeCode = :activeCode");
    $this->db->bind(":email", $data["email"]);
    $this->db->bind(":uname", "");
    $this->db->bind(":user_id", $data["user_id"]);
    $this->db->bind(":status", $login);
    $this->db->bind(":password", $data["password"]);
    $this->db->bind(":activeCode", $active);
    $this->db->bind(":image", "");
      $this->db->bind(":roleID", 8);
        $this->db->bind(":uname", 'Customer Rep');
        $this->db->bind(":registerer_id", $data['reg_id']);
    $this->db->bind(":fullname", "");


        if ($this->db->execute()) {
            return true;
        }else{
            return false;
        }
       }else{
        return false;
       }
    }
    
    
    
    public function register_user($data) {

        $login = 1;
        $active = "PAYKING_".md5(time());
        $this->db->query(" INSERT INTO  initkey 
            SET 
                email = :email, 
                uname = :uname, 
                user_id = :user_id, 
                accessStatus = :accessStatus, 
                password = :password,
                 activeCode = :activeCode, 
                loginStatus = :loginStatus");
        $this->db->bind(":email", $data["email"]);
        $this->db->bind(":uname", "");
        $this->db->bind(":user_id", $data["user_id"]);
        $this->db->bind(":accessStatus", $login);
        $this->db->bind(":password", $data["password"]);
        $this->db->bind(":activeCode", $active);
        $this->db->bind(":loginStatus", $login);
       if ($this->db->execute()) {
        $this->db->query(" INSERT INTO  userprofile 
        SET 
            fullname = :fullname,
            email = :email, 
            uname = :uname, 
            user_id = :user_id,  
            status = :status, 
            image = :image, 
            password = :password,
             activeCode = :activeCode");
    $this->db->bind(":email", $data["email"]);
    $this->db->bind(":uname", "");
    $this->db->bind(":user_id", $data["user_id"]);
    $this->db->bind(":status", $login);
    $this->db->bind(":password", $data["password"]);
    $this->db->bind(":activeCode", $active);
    $this->db->bind(":image", "");
    $this->db->bind(":fullname", "");


        if ($this->db->execute()) {
            return true;
        }else{
            return false;
        }
       }else{
        return false;
       }
    }
    public function register_user2($data) {

        $login = 1;
        $active = "PAYKING_".md5(time());
        $this->db->query(" INSERT INTO  initkey_ 
            SET 
                email = :email, 
                uname = :uname, 
                user_id = :user_id, 
                accessStatus = :accessStatus, 
                password = :password,
                 activeCode = :activeCode, 
                loginStatus = :loginStatus");
        $this->db->bind(":email", $data["email"]);
        $this->db->bind(":uname", "");
        $this->db->bind(":user_id", $data["user_id"]);
        $this->db->bind(":accessStatus", $login);
        $this->db->bind(":password", $data["password"]);
        $this->db->bind(":activeCode", $active);
        $this->db->bind(":loginStatus", $login);
       if ($this->db->execute()) {
         return true;
       }else{
        return false;
       }
    }
    public function register_admin2($data) {

        $login = 1;
        $role = 8;
        $active = "PAYKING_".md5(time());
        $this->db->query(" INSERT INTO  initkey_
            SET 
                email = :email, 
                uname = :uname, 
                user_id = :user_id, 
                accessStatus = :accessStatus, 
                password = :password,
                 activeCode = :activeCode, 
                loginStatus = :loginStatus,
                roleID = :roleID,
                registerer_id = :registerer_id");
        $this->db->bind(":email", $data["email"]);
        $this->db->bind(":uname", "");
        $this->db->bind(":user_id", $data["user_id"]);
        $this->db->bind(":accessStatus", $login);
        $this->db->bind(":password", $data["password"]);
        $this->db->bind(":activeCode", $active);
        $this->db->bind(":loginStatus", $login);
        $this->db->bind(":roleID", $role);
        
        $this->db->bind(":registerer_id", $data['reg_id']);
       if ($this->db->execute()) {
         return true;
       }else{
        return false;
       }
    }


    public function edit_user($data) {
        $this->db->query(" UPDATE   initkey 
            SET 
                uname = :uname
                  WHERE  user_id = :user_id AND email = :email");
        $this->db->bind(":email", $data["email"]);
        $this->db->bind(":uname", $data["uname"]);
        $this->db->bind(":user_id", $data["user_id"]);

       if ($this->db->execute()) {
        $this->db->query(" UPDATE   userprofile 
        SET 
            fullname = :fullname,
            address = :address,
            gender = :gender,
            dob = :dob,
           
            uname = :uname,  
            image = :image
             WHERE  user_id = :user_id AND   email = :email");
    $this->db->bind(":email", $data["email"]);
    $this->db->bind(":uname", $data["uname"]);
    $this->db->bind(":user_id", $data["user_id"]);
    $this->db->bind(":address", $data["address"]);
    $this->db->bind(":image", $data["image"]);
    $this->db->bind(":fullname", $data["fullname"]);
    $this->db->bind(":gender", $data["gender"]);
    $this->db->bind(":dob", $data["dob"]);

        if ($this->db->execute()) {
            return true;
        }else{
            return false;
        }
       }else{
        return false;
       }
    }
    
    

 


    //Get user by Id
    public function cookieChecker($live)
    {
        $this->db->query("SELECT * FROM initkey WHERE token = :token");

        // Bind Valuesrid
        $this->db->bind(':token', $live);

        $row = $this->db->single();

        // Check roow



        if ($this->db->rowCount() > 0) {
            return true;
        } else {

            return false;
        }

    }

    public function getUserBytoken($token)
    {
        $this->db->query("SELECT * FROM initkey WHERE token = :token");

        // Bind Values
        $this->db->bind(':token', $token);

        $row = $this->db->single();

        // Check roow
        return $row;

    }





    public function deleteToken($user_id, $token)
    {
        $token = '';
        //echo "removed"; exit;
        $this->db->query('UPDATE  initkey SET token = :token WHERE user_id= :user_id');
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':token', $token);

        // Execute
        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }



    public function updateToken($user_id, $token)
    {
        $this->db->query('UPDATE  initkey SET token = :token WHERE user_id= :user_id ');
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':token', $token);
        // Execute
        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function updateValidationlog($data) {
        $this->db->query(" INSERT INTO  verifiication_logs 
            SET 
                activity_details = :activity_details, 
                activity_id = :activity_id, 
                log_id = :log_id, 
                uploaded_file = :uploaded_file, 
                file_name = :file_name");
        $this->db->bind(":activity_details", $data["activity_details"]);
        $this->db->bind(":activity_id", $data["activity_id"]);
        $this->db->bind(":log_id", $data["log_id"]);
        $this->db->bind(":uploaded_file", $data["uploaded_file"]);
        $this->db->bind(":file_name", $data["file_name"]);
       if ($this->db->execute()) {
        return true;
       }else{
        return false;
       }
    }
    


}