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
    
    public function ensureUserExists($userId) {
    $this->db->query("SELECT id FROM users WHERE id = :id");
    $this->db->bind(':id', $userId);
    $this->db->execute();

    if ($this->db->rowCount() === 0) {
        // Insert with minimal default data
        $this->db->query("INSERT INTO users (id, username, email, created_at) VALUES (:id, :username, :email, NOW())");
        $this->db->bind(':id', $userId);
        $this->db->bind(':username', 'Guest_' . substr($userId, -5));
        $this->db->bind(':email', 'guest_' . substr($userId, -5) . '@example.com');
        $this->db->execute();
    }
}

    
    public function saveUsers($users)
{
    foreach ($users as $user) {
        $this->db->query("
            INSERT INTO users (
                id, username, email, phone, first_name, last_name,
                role_name, department, status, created_at
            ) VALUES (
                :id, :username, :email, :phone, :first_name, :last_name,
                :role_name, :department, :status, :created_at
            )
            ON DUPLICATE KEY UPDATE
                username = VALUES(username),
                email = VALUES(email),
                phone = VALUES(phone),
                first_name = VALUES(first_name),
                last_name = VALUES(last_name),
                role_name = VALUES(role_name),
                department = VALUES(department),
                status = VALUES(status),
                created_at = VALUES(created_at)
        ");

        $this->db->bind(':id', $user['id']);
        $this->db->bind(':username', $user['username']);
        $this->db->bind(':email', $user['email']);
        $this->db->bind(':phone', $user['phone']);
        $this->db->bind(':first_name', $user['firstName']);
        $this->db->bind(':last_name', $user['lastName']);
        $this->db->bind(':role_name', $user['roleName']);
        $this->db->bind(':department', $user['department']);
        $this->db->bind(':status', $user['status']);
        $this->db->bind(':created_at', date('Y-m-d H:i:s'));

        if (!$this->db->execute()) {
            return false;
        }
    }

    return true;
}

    
    public function getChatsByUserId($userId) {
    $this->db->query("SELECT * FROM personal_chats WHERE user1_id = :userId OR user2_id = :userId ORDER BY updated_at DESC");
    $this->db->bind(':userId', $userId);
    return $this->db->resultSet();
}

public function markMessagesAsRead($chatId, $userId) {
    $this->db->query("UPDATE personal_messages SET is_read = 1 WHERE chat_id = :chatId AND receiver_id = :userId AND is_read = 0");
    $this->db->bind(':chatId', $chatId);
    $this->db->bind(':userId', $userId);
    $this->db->execute();

    // Reset unread count in personal_chats
    $this->db->query("
        UPDATE personal_chats 
        SET unread_count_user1 = IF(user1_id = :userId, 0, unread_count_user1), 
            unread_count_user2 = IF(user2_id = :userId, 0, unread_count_user2) 
        WHERE id = :chatId
    ");
    $this->db->bind(':userId', $userId);
    $this->db->bind(':chatId', $chatId);
    $this->db->execute();
}

public function getUnreadMessageCount($userId) {
    $this->db->query("
        SELECT 
            SUM(
                CASE 
                    WHEN user1_id = :userId THEN unread_count_user1
                    WHEN user2_id = :userId THEN unread_count_user2
                    ELSE 0
                END
            ) AS total_unread
        FROM personal_chats
        WHERE user1_id = :userId OR user2_id = :userId
    ");
    $this->db->bind(':userId', $userId);
    $row = $this->db->single();
    return $row['total_unread'] ?? 0;
}

    
    public function getMessagesByChatId($chatId) {
    $this->db->query("SELECT * FROM personal_messages WHERE chat_id = :chatId ORDER BY timestamp ASC");
    $this->db->bind(':chatId', $chatId);
    return $this->db->resultSet();
}

    
public function getOrCreateChat($userId1, $userId2) {
    // Normalize order
    $ids = [$userId1, $userId2];
    sort($ids);
    $uid1 = $ids[0];
    $uid2 = $ids[1];

    // Check if chat exists
    $this->db->query("SELECT * FROM personal_chats WHERE user1_id = :u1 AND user2_id = :u2");
    $this->db->bind(':u1', $uid1);
    $this->db->bind(':u2', $uid2);
    $chat = $this->db->single();

    if ($chat) {
        return $chat;
    }

    // Create new chat
    $this->db->query("INSERT INTO personal_chats (user1_id, user2_id, updated_at) VALUES (:u1, :u2, NOW())");
    $this->db->bind(':u1', $uid1);
    $this->db->bind(':u2', $uid2);
    if ($this->db->execute()) {
        $chatId = $this->db->lastInsertId();

        return [
            'id' => $chatId,
            'participants' => [$uid1, $uid2],
            'lastMessage' => null,
            'updatedAt' => date('Y-m-d H:i:s'),
            'unreadCount' => [$uid1 => 0, $uid2 => 0],
        ];
    }

    return false;
}

// In UserModel.php

public function savePersonalMessage($msg) {
    

    try {
        // 1. Insert message into personal_messages
        $this->db->query("INSERT INTO personal_messages (
            id, chat_id, sender_id, receiver_id, content, timestamp, is_read
        ) VALUES (
            :id, :chatId, :senderId, :receiverId, :content, :timestamp, :isRead
        )");

        $this->db->bind(':id', $msg['id']);
        $this->db->bind(':chatId', $msg['chatId']);
        $this->db->bind(':senderId', $msg['senderId']);
        $this->db->bind(':receiverId', $msg['receiverId']);
        $this->db->bind(':content', $msg['content']);
        $this->db->bind(':timestamp', date('Y-m-d H:i:s', strtotime($msg['timestamp'])));
        $this->db->bind(':isRead', $msg['read'] ? 1 : 0);

        $this->db->execute();

        // 2. Update personal_chats metadata
        $this->db->query("UPDATE personal_chats SET
            last_message = :lastMessage,
            updated_at = :updatedAt,
            unread_count_user1 = CASE 
                WHEN user1_id = :receiver THEN unread_count_user1 + 1 
                ELSE unread_count_user1 
            END,
            unread_count_user2 = CASE 
                WHEN user2_id = :receiver THEN unread_count_user2 + 1 
                ELSE unread_count_user2 
            END
            WHERE id = :chatId
        ");

        $this->db->bind(':lastMessage', $msg['content']);
        $this->db->bind(':updatedAt', date('Y-m-d H:i:s', strtotime($msg['timestamp'])));
        $this->db->bind(':receiver', $msg['receiverId']);
        $this->db->bind(':chatId', $msg['chatId']);

        $this->db->execute();

        $this->db->commit();
        return true;
    } catch (Exception $e) {
        $this->db->rollback();
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
                scanner_id = :scanner_id,
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
        $this->db->bind(":scanner_id", $data["scanner_id"]);
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
      public function virtualUpdate($res, $data)
    {
     
            $this->db->query(" UPDATE   user_accounts 
            SET 
                account_name = :account_name,
                account_no = :account_no,
                bank_name = :bank_name
                 WHERE  user_id = :user_id AND   email = :email");
                $this->db->bind(":email", $data['email']);
                $this->db->bind(":user_id", $data['user_id']);
                $this->db->bind(":account_name", $res->accountName);
                $this->db->bind(":account_no", $res->account);
                $this->db->bind(":bank_name", $res->bankName);
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
          
                   $this->db->query ("SELECT * FROM initkey_ WHERE email = :email");
         $this->db->bind(':email', $email);

        $row = $this->db->single();

        if($this->db->rowCount() > 0)
        {
            return $row;
        }else{
            return false;
        }
       
        
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
                $this->db->query("SELECT * FROM initkey_ WHERE email = :email");
                $this->db->bind(':email', $email);

        $row = $this->db->single();

        if($this->db->rowCount() > 0)
        {
            return true ;
        }else{
            return false;
        }
    }
}

public function findUserByNin($email)
{
    $this->db->query("SELECT * FROM initkey WHERE nin_no = :nin_no");

    // Bind Values
    $this->db->bind(':nin_no', $email);

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
    public function getUserByid2($id)
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
        //   $this->db->bind(':phone_validated', 1);
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
                uname = :uname,
                nin_no = :nin_no
                  WHERE  user_id = :user_id AND email = :email");
        $this->db->bind(":email", $data["email"]);
        $this->db->bind(":uname", $data["uname"]);
        $this->db->bind(":user_id", $data["user_id"]);
         $this->db->bind(":nin_no", $data["nin"]);

       if ($this->db->execute()) {
        $this->db->query(" UPDATE   userprofile 
        SET 
            fullname = :fullname,
            address = :address,
            gender = :gender,
            dob = :dob,
           nin_no = :nin_no,
            uname = :uname,  
            image = :image
             WHERE  user_id = :user_id AND   email = :email");
    $this->db->bind(":email", $data["email"]);
    $this->db->bind(":uname", $data["uname"]);
    $this->db->bind(":user_id", $data["user_id"]);
    $this->db->bind(":address", $data["address"]);
    $this->db->bind(":nin_no", $data["nin"]);
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
    
    

    public function validAccount($data) {
        $this->db->query(" UPDATE   initkey 
            SET 
                nin_no = :nin_no,
                bvn_no = :bvn_no,
                nin_img = :nin_img,
                bvn_img = :bvn_img
                  WHERE  user_id = :user_id AND email = :email");
        $this->db->bind(":email", $data["email"]);
        $this->db->bind(":nin_no", $data["nin"]);
        $this->db->bind(":bvn_no", 0);
        $this->db->bind(":nin_img", $data["nin_img"]);
        $this->db->bind(":bvn_img", 0);
        $this->db->bind(":user_id", $data["user_id"]);

       if ($this->db->execute()) {
        $this->db->query(" UPDATE   userprofile 
        SET 
            nin_no = :nin_no,
                bvn_no = :bvn_no,
                nin_img = :nin_img,
                nin_img = :nin_img
             WHERE  user_id = :user_id AND   email = :email");
       $this->db->bind(":email", $data["email"]);
        $this->db->bind(":nin_no", $data["nin"]);
        $this->db->bind(":bvn_no", 0);
        $this->db->bind(":nin_img", $data["nin_img"]);
        $this->db->bind(":bvn_img", 0);
        $this->db->bind(":user_id", $data["user_id"]);


        if ($this->db->execute()) {
            return true;
        }else{
            return false;
        }
       }else{
        return false;
       }
    }
    

    public function validAccountBvn($data) {
        $this->db->query(" UPDATE   initkey 
            SET 
   
                bvn_no = :bvn_no,
             
                bvn_img = :bvn_img
                  WHERE  user_id = :user_id AND email = :email");
        $this->db->bind(":email", $data["email"]);
      
        $this->db->bind(":bvn_no", $data['bvn']);
       
        $this->db->bind(":bvn_img", 0);
        $this->db->bind(":user_id", $data["user_id"]);

       if ($this->db->execute()) {
        $this->db->query(" UPDATE   userprofile 
        SET 
          
                bvn_no = :bvn_no,
             bvn_img = :bvn_img
             WHERE  user_id = :user_id AND   email = :email");
       $this->db->bind(":email", $data["email"]);
        
        $this->db->bind(":bvn_no", $data['bvn']);
       
        $this->db->bind(":bvn_img", 0);
        $this->db->bind(":user_id", $data["user_id"]);


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
