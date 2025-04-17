<?php



class Thrift
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }



    public function  payDailyThriftFixed($datax)
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
                    $newFund2 = $row->fixed_thrift + $datax['amount'];
                    $this->db->query('UPDATE user_accounts set fixed_thrift = :fixed_thrift WHERE  email = :email and  user_id = :user_id');

                    $this->db->bind(':email', $datax['email']);
                    $this->db->bind(':user_id', $datax['user_id']);
                    $this->db->bind(':fixed_thrift', $newFund2);
                    if ($this->db->execute()) {
                        $activity = "DAILY FIXED THRIFT SETTLEMENT";
                        $processID = "log_" . md5(time());
                        $this->db->query("INSERT INTO activity_log 
                            (activity, user_id, activity_id, process_id, amount) 
                            VALUES 
                            (:activity, :user_id, :activity_id, :process_id, :amount)");

                        $this->db->bind(":activity", $activity);
                        $this->db->bind(":user_id", $datax["user_id"]);
                        $this->db->bind(":activity_id", $datax["thrift_id"]);
                        $this->db->bind(":process_id", $processID);
                        $this->db->bind(":amount", $datax["amount"]);

                        if ($this->db->execute()) {
                            $this->db->query("SELECT * FROM daily_thrift_record WHERE user_id = :user_id AND email = :email AND thrift_id = :thrift_id");
                            $this->db->bind(':email', $datax['email']);
                            $this->db->bind(":user_id", $datax["user_id"]);
                            $this->db->bind(":thrift_id", $datax["thrift_id"]);
                            $row23 = $this->db->single();
                            if($this->db->rowCount() > 0)
                            {
                                $this->db->query("UPDATE daily_thrift_record set pay_count = :pay_count WHERE user_id = :user_id AND email = :email AND thrift_id = :thrift_id");
                            $this->db->bind(':pay_count', $row23->pay_count + 1);
                            $this->db->bind(':email', $datax['email']);
                            $this->db->bind(":user_id", $datax["user_id"]);
                            $this->db->bind(":thrift_id", $datax["thrift_id"]);
                             if ($this->db->execute()) {
                                 return true;
                             }else{
                                 return false;
                             }
                            }else{
                                return false;
                            }
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
                        'message' => 'failed',
                    ];
                    print_r(json_encode($res));
                    exit;
                }
            } else {
                $res = [
                    'status' => 401,
                    'message' => 'not enough Balance',
                ];
                print_r(json_encode($res));
                exit;
            }
        }
    }
    public function  payDailyOsusuFixed($datax, $data, $nplayer)
    {
        foreach ($data as $data_item) {
            $this->db->query('SELECT * FROM user_accounts WHERE user_id = :user_id ');
            $this->db->bind(':user_id', $data_item);
            $row_item = $this->db->single();

            if ($this->db->rowCount() > 0) {
                if ($row_item->savings >= $datax['amount']) {
                    $newFund = $row_item->savings - $datax['amount'];
                    $this->db->query('UPDATE user_accounts set savings = :savings WHERE  email = :email and  user_id = :user_id');

                    $this->db->bind(':email', $row_item->email);
                    $this->db->bind(':user_id', $data_item);
                    $this->db->bind(':savings', $newFund);
                    if ($this->db->execute()) {
                        $this->db->query('SELECT * FROM user_accounts WHERE user_id = :user_id ');
                        $this->db->bind(':user_id', $nplayer);
                        $row_item2 = $this->db->single();
                        $newFund2 = $row_item2->osusu_account + $datax['amount'];
                        $this->db->query('UPDATE user_accounts set osusu_account = :osusu_account WHERE  email = :email and  user_id = :user_id');

                        $this->db->bind(':email', $row_item2->email);
                        $this->db->bind(':user_id', $nplayer);
                        $this->db->bind(':osusu_account', $newFund2);
                        if ($this->db->execute()) {
                            $activity = "DAILY OSUSU SETTLEMENT";
                            $processID = "log_" . md5(time());
                            $this->db->query("INSERT INTO activity_log 
                            (activity, user_id, activity_id, process_id, amount) 
                            VALUES 
                            (:activity, :user_id, :activity_id, :process_id, :amount)");

                            $this->db->bind(":activity", $activity);
                            $this->db->bind(":user_id", $datax["user_id"]);
                            $this->db->bind(":activity_id", $datax["osusu_id"]);
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
                            'message' => 'failed',
                        ];
                        print_r(json_encode($res));
                        exit;
                    }
                } else {
                    $res = [
                        'status' => 401,
                        'message' => 'not enough Balance',
                    ];
                    print_r(json_encode($res));
                    exit;
                }
            }
        }
        $this->db->query('SELECT * FROM user_accounts WHERE  email = :email and  user_id = :user_id ');
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
                    $this->db->query('SELECT * FROM user_accounts WHERE user_id = :user_id ');
                        $this->db->bind(':user_id', $nplayer);
                        $row_item2 = $this->db->single();
                        $newFund2 = $row_item2->osusu_account + $datax['amount'];
                        $this->db->query('UPDATE user_accounts set osusu_account = :osusu_account WHERE  email = :email and  user_id = :user_id');

                        $this->db->bind(':email', $row_item2->email);
                        $this->db->bind(':user_id', $nplayer);
                        $this->db->bind(':osusu_account', $newFund2);
                        if ($this->db->execute()) {
                            $activity = "DAILY OSUSU SETTLEMENT";
                            $processID = "log_" . md5(time());
                            $this->db->query("INSERT INTO activity_log 
                            (activity, user_id, activity_id, process_id, amount) 
                            VALUES 
                            (:activity, :user_id, :activity_id, :process_id, :amount)");

                            $this->db->bind(":activity", $activity);
                            $this->db->bind(":user_id", $datax["user_id"]);
                            $this->db->bind(":activity_id", $datax["osusu_id"]);
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
                        'message' => 'failed',
                    ];
                    print_r(json_encode($res));
                    exit;
                }
            } else {
                $res = [
                    'status' => 401,
                    'message' => 'not enough Balance',
                ];
                print_r(json_encode($res));
                exit;
            }
        }
    }
    public function  payWeeklyOsusuFixed($datax, $data, $nplayer)
    {
        foreach ($data as $data_item) {
            $this->db->query('SELECT * FROM user_accounts WHERE user_id = :user_id ');
            $this->db->bind(':user_id', $data_item);
            $row_item = $this->db->single();

            if ($this->db->rowCount() > 0) {
                if ($row_item->savings >= $datax['amount']) {
                    $newFund = $row_item->savings - $datax['amount'];
                    $this->db->query('UPDATE user_accounts set savings = :savings WHERE  email = :email and  user_id = :user_id');

                    $this->db->bind(':email', $row_item->email);
                    $this->db->bind(':user_id', $data_item);
                    $this->db->bind(':savings', $newFund);
                    if ($this->db->execute()) {
                        $this->db->query('SELECT * FROM user_accounts WHERE user_id = :user_id ');
                        $this->db->bind(':user_id', $nplayer);
                        $row_item2 = $this->db->single();
                        $newFund2 = $row_item2->osusu_account + $datax['amount'];
                        $this->db->query('UPDATE user_accounts set osusu_account = :osusu_account WHERE  email = :email and  user_id = :user_id');

                        $this->db->bind(':email', $row_item2->email);
                        $this->db->bind(':user_id', $nplayer);
                        $this->db->bind(':osusu_account', $newFund2);
                        if ($this->db->execute()) {
                            $activity = "WEEKLY OSUSU SETTLEMENT";
                            $processID = "log_" . md5(time());
                            $this->db->query("INSERT INTO activity_log 
                            (activity, user_id, activity_id, process_id, amount) 
                            VALUES 
                            (:activity, :user_id, :activity_id, :process_id, :amount)");

                            $this->db->bind(":activity", $activity);
                            $this->db->bind(":user_id", $datax["user_id"]);
                            $this->db->bind(":activity_id", $datax["osusu_id"]);
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
                            'message' => 'failed',
                        ];
                        print_r(json_encode($res));
                        exit;
                    }
                } else {
                    $res = [
                        'status' => 401,
                        'message' => 'not enough Balance',
                    ];
                    print_r(json_encode($res));
                    exit;
                }
            }
        }
        $this->db->query('SELECT * FROM user_accounts WHERE  email = :email and  user_id = :user_id ');
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
                    $this->db->query('SELECT * FROM user_accounts WHERE user_id = :user_id ');
                        $this->db->bind(':user_id', $nplayer);
                        $row_item2 = $this->db->single();
                        $newFund2 = $row_item2->osusu_account + $datax['amount'];
                        $this->db->query('UPDATE user_accounts set osusu_account = :osusu_account WHERE  email = :email and  user_id = :user_id');

                        $this->db->bind(':email', $row_item2->email);
                        $this->db->bind(':user_id', $nplayer);
                        $this->db->bind(':osusu_account', $newFund2);
                        if ($this->db->execute()) {
                            $activity = "WEEKLY OSUSU SETTLEMENT";
                            $processID = "log_" . md5(time());
                            $this->db->query("INSERT INTO activity_log 
                            (activity, user_id, activity_id, process_id, amount) 
                            VALUES 
                            (:activity, :user_id, :activity_id, :process_id, :amount)");

                            $this->db->bind(":activity", $activity);
                            $this->db->bind(":user_id", $datax["user_id"]);
                            $this->db->bind(":activity_id", $datax["osusu_id"]);
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
                        'message' => 'failed',
                    ];
                    print_r(json_encode($res));
                    exit;
                }
            } else {
                $res = [
                    'status' => 401,
                    'message' => 'not enough Balance',
                ];
                print_r(json_encode($res));
                exit;
            }
        }
    }
    public function  payMonthlyOsusuFixed($datax, $data, $nplayer)
    {
        foreach ($data as $data_item) {
            $this->db->query('SELECT * FROM user_accounts WHERE user_id = :user_id ');
            $this->db->bind(':user_id', $data_item);
            $row_item = $this->db->single();

            if ($this->db->rowCount() > 0) {
                if ($row_item->savings >= $datax['amount']) {
                    $newFund = $row_item->savings - $datax['amount'];
                    $this->db->query('UPDATE user_accounts set savings = :savings WHERE  email = :email and  user_id = :user_id');

                    $this->db->bind(':email', $row_item->email);
                    $this->db->bind(':user_id', $data_item);
                    $this->db->bind(':savings', $newFund);
                    if ($this->db->execute()) {
                        $this->db->query('SELECT * FROM user_accounts WHERE user_id = :user_id ');
                        $this->db->bind(':user_id', $nplayer);
                        $row_item2 = $this->db->single();
                        $newFund2 = $row_item2->osusu_account + $datax['amount'];
                        $this->db->query('UPDATE user_accounts set osusu_account = :osusu_account WHERE  email = :email and  user_id = :user_id');

                        $this->db->bind(':email', $row_item2->email);
                        $this->db->bind(':user_id', $nplayer);
                        $this->db->bind(':osusu_account', $newFund2);
                        if ($this->db->execute()) {
                            $activity = "MONTHLY OSUSU SETTLEMENT";
                            $processID = "log_" . md5(time());
                            $this->db->query("INSERT INTO activity_log 
                            (activity, user_id, activity_id, process_id, amount) 
                            VALUES 
                            (:activity, :user_id, :activity_id, :process_id, :amount)");

                            $this->db->bind(":activity", $activity);
                            $this->db->bind(":user_id", $datax["user_id"]);
                            $this->db->bind(":activity_id", $datax["osusu_id"]);
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
                            'message' => 'failed',
                        ];
                        print_r(json_encode($res));
                        exit;
                    }
                } else {
                    $res = [
                        'status' => 401,
                        'message' => 'not enough Balance',
                    ];
                    print_r(json_encode($res));
                    exit;
                }
            }
        }
        $this->db->query('SELECT * FROM user_accounts WHERE  email = :email and  user_id = :user_id ');
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
                    $this->db->query('SELECT * FROM user_accounts WHERE user_id = :user_id ');
                        $this->db->bind(':user_id', $nplayer);
                        $row_item2 = $this->db->single();
                        $newFund2 = $row_item2->osusu_account + $datax['amount'];
                        $this->db->query('UPDATE user_accounts set osusu_account = :osusu_account WHERE  email = :email and  user_id = :user_id');

                        $this->db->bind(':email', $row_item2->email);
                        $this->db->bind(':user_id', $nplayer);
                        $this->db->bind(':osusu_account', $newFund2);
                        if ($this->db->execute()) {
                            $activity = "MONTHLY OSUSU SETTLEMENT";
                            $processID = "log_" . md5(time());
                            $this->db->query("INSERT INTO activity_log 
                            (activity, user_id, activity_id, process_id, amount) 
                            VALUES 
                            (:activity, :user_id, :activity_id, :process_id, :amount)");

                            $this->db->bind(":activity", $activity);
                            $this->db->bind(":user_id", $datax["user_id"]);
                            $this->db->bind(":activity_id", $datax["osusu_id"]);
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
                        'message' => 'failed',
                    ];
                    print_r(json_encode($res));
                    exit;
                }
            } else {
                $res = [
                    'status' => 401,
                    'message' => 'not enough Balance',
                ];
                print_r(json_encode($res));
                exit;
            }
        }
    }
    public function  payWeeklyThriftFixed($datax)
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
                    $newFund2 = $row->fixed_thrift + $datax['amount'];
                    $this->db->query('UPDATE user_accounts set fixed_thrift = :fixed_thrift WHERE  email = :email and  user_id = :user_id');

                    $this->db->bind(':email', $datax['email']);
                    $this->db->bind(':user_id', $datax['user_id']);
                    $this->db->bind(':fixed_thrift', $newFund2);
                    if ($this->db->execute()) {
                        $activity = "WEEKLY FIXED THRIFT SETTLEMENT";
                        $processID = "log_" . md5(time());
                        $this->db->query("INSERT INTO activity_log 
                            (activity, user_id, activity_id, process_id, amount) 
                            VALUES 
                            (:activity, :user_id, :activity_id, :process_id, :amount)");

                        $this->db->bind(":activity", $activity);
                        $this->db->bind(":user_id", $datax["user_id"]);
                        $this->db->bind(":activity_id", $datax["thrift_id"]);
                        $this->db->bind(":process_id", $processID);
                        $this->db->bind(":amount", $datax["amount"]);

                       
                        if ($this->db->execute()) {
                            $this->db->query("SELECT * FROM weekly_thrift_record WHERE user_id = :user_id AND email = :email AND thrift_id = :thrift_id");
                            $this->db->bind(':email', $datax['email']);
                            $this->db->bind(":user_id", $datax["user_id"]);
                            $this->db->bind(":thrift_id", $datax["thrift_id"]);
                            $row23 = $this->db->single();
                            if($this->db->rowCount() > 0)
                            {
                                $this->db->query("UPDATE weekly_thrift_record set pay_count = :pay_count WHERE user_id = :user_id AND email = :email AND thrift_id = :thrift_id");
                            $this->db->bind(':pay_count', $row23->pay_count + 1);
                            $this->db->bind(':email', $datax['email']);
                            $this->db->bind(":user_id", $datax["user_id"]);
                            $this->db->bind(":thrift_id", $datax["thrift_id"]);
                             if ($this->db->execute()) {
                                 return true;
                             }else{
                                 return false;
                             }
                            }else{
                                return false;
                            }
                            return true;
                        } 
                    } else {
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
                $res = [
                    'status' => 401,
                    'message' => 'not enough Balance',
                ];
                print_r(json_encode($res));
                exit;
            }
        }
    }
    public function  payMonthlyThriftFixed($datax)
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
                    $newFund2 = $row->fixed_thrift + $datax['amount'];
                    $this->db->query('UPDATE user_accounts set fixed_thrift = :fixed_thrift WHERE  email = :email and  user_id = :user_id');

                    $this->db->bind(':email', $datax['email']);
                    $this->db->bind(':user_id', $datax['user_id']);
                    $this->db->bind(':fixed_thrift', $newFund2);
                    if ($this->db->execute()) {
                        $activity = "MONTHLY FIXED THRIFT SETTLEMENT";
                        $processID = "log_" . md5(time());
                        $this->db->query("INSERT INTO activity_log 
                            (activity, user_id, activity_id, process_id, amount) 
                            VALUES 
                            (:activity, :user_id, :activity_id, :process_id, :amount)");

                        $this->db->bind(":activity", $activity);
                        $this->db->bind(":user_id", $datax["user_id"]);
                        $this->db->bind(":activity_id", $datax["thrift_id"]);
                        $this->db->bind(":process_id", $processID);
                        $this->db->bind(":amount", $datax["amount"]);

                      
                        if ($this->db->execute()) {
                            $this->db->query("SELECT * FROM monthly_thrift_record WHERE user_id = :user_id AND email = :email AND thrift_id = :thrift_id");
                            $this->db->bind(':email', $datax['email']);
                            $this->db->bind(":user_id", $datax["user_id"]);
                            $this->db->bind(":thrift_id", $datax["thrift_id"]);
                            $row23 = $this->db->single();
                            if($this->db->rowCount() > 0)
                            {
                                $this->db->query("UPDATE monthly_thrift_record set pay_count = :pay_count WHERE user_id = :user_id AND email = :email AND thrift_id = :thrift_id");
                            $this->db->bind(':pay_count', $row23->pay_count + 1);
                            $this->db->bind(':email', $datax['email']);
                            $this->db->bind(":user_id", $datax["user_id"]);
                            $this->db->bind(":thrift_id", $datax["thrift_id"]);
                             if ($this->db->execute()) {
                                 return true;
                             }else{
                                 return false;
                             }
                            }else{
                                return false;
                            }
                            return true;
                        } 
                    } else {
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
                $res = [
                    'status' => 401,
                    'message' => 'not enough Balance',
                ];
                print_r(json_encode($res));
                exit;
            }
        }
    }
    public function  payDailyThriftLiberal($datax)
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
                    $newFund2 = $row->liberal_thrift + $datax['amount'];
                    $this->db->query('UPDATE user_accounts set liberal_thrift = :liberal_thrift WHERE  email = :email and  user_id = :user_id');

                    $this->db->bind(':email', $datax['email']);
                    $this->db->bind(':user_id', $datax['user_id']);
                    $this->db->bind(':liberal_thrift', $newFund2);
                    if ($this->db->execute()) {
                        $activity = "DAILY LIBERAL THRIFT SETTLEMENT";
                        $processID = "log_" . md5(time());
                        $this->db->query("INSERT INTO activity_log 
                            (activity, user_id, activity_id, process_id, amount) 
                            VALUES 
                            (:activity, :user_id, :activity_id, :process_id, :amount)");

                        $this->db->bind(":activity", $activity);
                        $this->db->bind(":user_id", $datax["user_id"]);
                        $this->db->bind(":activity_id", $datax["thrift_id"]);
                        $this->db->bind(":process_id", $processID);
                        $this->db->bind(":amount", $datax["amount"]);

                       
                        if ($this->db->execute()) {
                            $this->db->query("SELECT * FROM daily_thrift_record WHERE user_id = :user_id AND email = :email AND thrift_id = :thrift_id");
                            $this->db->bind(':email', $datax['email']);
                            $this->db->bind(":user_id", $datax["user_id"]);
                            $this->db->bind(":thrift_id", $datax["thrift_id"]);
                            $row23 = $this->db->single();
                            if($this->db->rowCount() > 0)
                            {
                                $this->db->query("UPDATE daily_thrift_record set pay_count = :pay_count WHERE user_id = :user_id AND email = :email AND thrift_id = :thrift_id");
                            $this->db->bind(':pay_count', $row23->pay_count + 1);
                            $this->db->bind(':email', $datax['email']);
                            $this->db->bind(":user_id", $datax["user_id"]);
                            $this->db->bind(":thrift_id", $datax["thrift_id"]);
                             if ($this->db->execute()) {
                                 return true;
                             }else{
                                 return false;
                             }
                            }else{
                                return false;
                            }
                            return true;
                        } 
                    } else {
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
                $res = [
                    'status' => 401,
                    'message' => 'not enough Balance',
                ];
                print_r(json_encode($res));
                exit;
            }
        }
    }
    public function  payWeeklyThriftLiberal($datax)
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
                    $newFund2 = $row->liberal_thrift + $datax['amount'];
                    $this->db->query('UPDATE user_accounts set liberal_thrift = :liberal_thrift WHERE  email = :email and  user_id = :user_id');

                    $this->db->bind(':email', $datax['email']);
                    $this->db->bind(':user_id', $datax['user_id']);
                    $this->db->bind(':liberal_thrift', $newFund2);
                    if ($this->db->execute()) {
                        $activity = "WEEKLY LIBERAL THRIFT SETTLEMENT";
                        $processID = "log_" . md5(time());
                        $this->db->query("INSERT INTO activity_log 
                            (activity, user_id, activity_id, process_id, amount) 
                            VALUES 
                            (:activity, :user_id, :activity_id, :process_id, :amount)");

                        $this->db->bind(":activity", $activity);
                        $this->db->bind(":user_id", $datax["user_id"]);
                        $this->db->bind(":activity_id", $datax["thrift_id"]);
                        $this->db->bind(":process_id", $processID);
                        $this->db->bind(":amount", $datax["amount"]);

                       
                        if ($this->db->execute()) {
                            $this->db->query("SELECT * FROM weekly_thrift_record WHERE user_id = :user_id AND email = :email AND thrift_id = :thrift_id");
                            $this->db->bind(':email', $datax['email']);
                            $this->db->bind(":user_id", $datax["user_id"]);
                            $this->db->bind(":thrift_id", $datax["thrift_id"]);
                            $row23 = $this->db->single();
                            if($this->db->rowCount() > 0)
                            {
                                $this->db->query("UPDATE weekly_thrift_record set pay_count = :pay_count WHERE user_id = :user_id AND email = :email AND thrift_id = :thrift_id");
                            $this->db->bind(':pay_count', $row23->pay_count + 1);
                            $this->db->bind(':email', $datax['email']);
                            $this->db->bind(":user_id", $datax["user_id"]);
                            $this->db->bind(":thrift_id", $datax["thrift_id"]);
                             if ($this->db->execute()) {
                                 return true;
                             }else{
                                 return false;
                             }
                            }else{
                                return false;
                            }
                            return true;
                        } 
                    } else {
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
                $res = [
                    'status' => 401,
                    'message' => 'not enough Balance',
                ];
                print_r(json_encode($res));
                exit;
            }
        }
    }
    public function  payMonthlyThriftLiberal($datax)
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
                    $newFund2 = $row->liberal_thrift + $datax['amount'];
                    $this->db->query('UPDATE user_accounts set liberal_thrift = :liberal_thrift WHERE  email = :email and  user_id = :user_id');

                    $this->db->bind(':email', $datax['email']);
                    $this->db->bind(':user_id', $datax['user_id']);
                    $this->db->bind(':liberal_thrift', $newFund2);
                    if ($this->db->execute()) {
                        $activity = "MONTHLY LIBERAL THRIFT SETTLEMENT";
                        $processID = "log_" . md5(time());
                        $this->db->query("INSERT INTO activity_log 
                            (activity, user_id, activity_id, process_id, amount) 
                            VALUES 
                            (:activity, :user_id, :activity_id, :process_id, :amount)");

                        $this->db->bind(":activity", $activity);
                        $this->db->bind(":user_id", $datax["user_id"]);
                        $this->db->bind(":activity_id", $datax["thrift_id"]);
                        $this->db->bind(":process_id", $processID);
                        $this->db->bind(":amount", $datax["amount"]);

                       
                        if ($this->db->execute()) {
                            $this->db->query("SELECT * FROM monthly_thrift_record WHERE user_id = :user_id AND email = :email AND thrift_id = :thrift_id");
                            $this->db->bind(':email', $datax['email']);
                            $this->db->bind(":user_id", $datax["user_id"]);
                            $this->db->bind(":thrift_id", $datax["thrift_id"]);
                            $row23 = $this->db->single();
                            if($this->db->rowCount() > 0)
                            {
                                $this->db->query("UPDATE monthly_thrift_record set pay_count = :pay_count WHERE user_id = :user_id AND email = :email AND thrift_id = :thrift_id");
                            $this->db->bind(':pay_count', $row23->pay_count + 1);
                            $this->db->bind(':email', $datax['email']);
                            $this->db->bind(":user_id", $datax["user_id"]);
                            $this->db->bind(":thrift_id", $datax["thrift_id"]);
                             if ($this->db->execute()) {
                                 return true;
                             }else{
                                 return false;
                             }
                            }else{
                                return false;
                            }
                            return true;
                        } 
                    } else {
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
                $res = [
                    'status' => 401,
                    'message' => 'not enough Balance',
                ];
                print_r(json_encode($res));
                exit;
            }
        }
    }

    public function createDailyThrift($data)
    {

        $active = 1;

        $this->db->query(" INSERT INTO  daily_thrift_record 
            SET 
                thrift_name = :thrift_name,
                thrift_type = :thrift_type,
                time_interval = :time_interval,
                amount = :amount,
                collection_time = :collection_time,
                duration = :duration,
                email = :email,
                user_id = :user_id,
                thrift_id = :thrift_id,
                active_thrift = :active_thrift
                ");
        $this->db->bind(":email", $data["email"]);
        $this->db->bind(":thrift_name", $data["thrift_name"]);
        $this->db->bind(":user_id", $data["user_id"]);
        $this->db->bind(":thrift_type", $data["thrift_type"]);
        $this->db->bind(":time_interval", $data["interval"]);
        $this->db->bind(":amount", $data["amount"]);
        $this->db->bind(":collection_time", $data["collection_time"]);
        $this->db->bind(":duration", $data["duration"]);
        $this->db->bind(":thrift_id", $data["thrift_id"]);
        $this->db->bind(":active_thrift", $active);
        $this->db->execute();
        $this->db->query(" INSERT INTO  thrift_record 
            SET 
                thrift_name = :thrift_name,
                thrift_type = :thrift_type,
                time_interval = :time_interval,
                amount = :amount,
                collection_time = :collection_time,
                duration = :duration,
                email = :email,
                user_id = :user_id,
                thrift_id = :thrift_id,
                active_thrift = :active_thrift
                ");
        $this->db->bind(":email", $data["email"]);
        $this->db->bind(":thrift_name", $data["thrift_name"]);
        $this->db->bind(":user_id", $data["user_id"]);
        $this->db->bind(":thrift_type", $data["thrift_type"]);
        $this->db->bind(":time_interval", $data["interval"]);
        $this->db->bind(":amount", $data["amount"]);
        $this->db->bind(":collection_time", $data["collection_time"]);
        $this->db->bind(":duration", $data["duration"]);
        $this->db->bind(":thrift_id", $data["thrift_id"]);
        $this->db->bind(":active_thrift", $active);


        if ($this->db->execute()) {
            $activity = "CREATED A THRIFT";
            $processID = "log_" . md5(time());
            $this->db->query("INSERT INTO activity_log 
        (activity, user_id, activity_id, process_id, amount) 
        VALUES 
        (:activity, :user_id, :activity_id, :process_id, :amount)");

            $this->db->bind(":activity", $activity);
            $this->db->bind(":user_id", $data["user_id"]);
            $this->db->bind(":activity_id", $data["thrift_id"]);
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
    public function createWeeklyThrift($data)
    {

        $active = 1;

        $this->db->query(" INSERT INTO  weekly_thrift_record 
            SET 
                thrift_name = :thrift_name,
                thrift_type = :thrift_type,
                time_interval = :time_interval,
                amount = :amount,
                collection_time = :collection_time,
                duration = :duration,
                email = :email,
                user_id = :user_id,
                thrift_id = :thrift_id,
                active_thrift = :active_thrift
                ");
        $this->db->bind(":email", $data["email"]);
        $this->db->bind(":thrift_name", $data["thrift_name"]);
        $this->db->bind(":user_id", $data["user_id"]);
        $this->db->bind(":thrift_type", $data["thrift_type"]);
        $this->db->bind(":time_interval", $data["interval"]);
        $this->db->bind(":amount", $data["amount"]);
        $this->db->bind(":collection_time", $data["collection_time"]);
        $this->db->bind(":duration", $data["duration"]);
        $this->db->bind(":thrift_id", $data["thrift_id"]);
        $this->db->bind(":active_thrift", $active);
        $this->db->execute();
        $this->db->query(" INSERT INTO  thrift_record 
            SET 
                thrift_name = :thrift_name,
                thrift_type = :thrift_type,
                time_interval = :time_interval,
                amount = :amount,
                collection_time = :collection_time,
                duration = :duration,
                email = :email,
                user_id = :user_id,
                thrift_id = :thrift_id,
                active_thrift = :active_thrift
                ");
        $this->db->bind(":email", $data["email"]);
        $this->db->bind(":thrift_name", $data["thrift_name"]);
        $this->db->bind(":user_id", $data["user_id"]);
        $this->db->bind(":thrift_type", $data["thrift_type"]);
        $this->db->bind(":time_interval", $data["interval"]);
        $this->db->bind(":amount", $data["amount"]);
        $this->db->bind(":collection_time", $data["collection_time"]);
        $this->db->bind(":duration", $data["duration"]);
        $this->db->bind(":thrift_id", $data["thrift_id"]);
        $this->db->bind(":active_thrift", $active);


        if ($this->db->execute()) {
            $activity = "CREATED A THRIFT";
            $processID = "log_" . md5(time());
            $this->db->query("INSERT INTO activity_log 
        (activity, user_id, activity_id, process_id, amount) 
        VALUES 
        (:activity, :user_id, :activity_id, :process_id, :amount)");

            $this->db->bind(":activity", $activity);
            $this->db->bind(":user_id", $data["user_id"]);
            $this->db->bind(":activity_id", $data["thrift_id"]);
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
    public function createMonthlyThrift($data)
    {

        $active = 1;

        $this->db->query(" INSERT INTO  monthly_thrift_record 
            SET 
                thrift_name = :thrift_name,
                thrift_type = :thrift_type,
                time_interval = :time_interval,
                amount = :amount,
                collection_time = :collection_time,
                duration = :duration,
                email = :email,
                user_id = :user_id,
                thrift_id = :thrift_id,
                active_thrift = :active_thrift
                ");
        $this->db->bind(":email", $data["email"]);
        $this->db->bind(":thrift_name", $data["thrift_name"]);
        $this->db->bind(":user_id", $data["user_id"]);
        $this->db->bind(":thrift_type", $data["thrift_type"]);
        $this->db->bind(":time_interval", $data["interval"]);
        $this->db->bind(":amount", $data["amount"]);
        $this->db->bind(":collection_time", $data["collection_time"]);
        $this->db->bind(":duration", $data["duration"]);
        $this->db->bind(":thrift_id", $data["thrift_id"]);
        $this->db->bind(":active_thrift", $active);
        $this->db->execute();
        $this->db->query(" INSERT INTO  thrift_record 
            SET 
                thrift_name = :thrift_name,
                thrift_type = :thrift_type,
                time_interval = :time_interval,
                amount = :amount,
                collection_time = :collection_time,
                duration = :duration,
                email = :email,
                user_id = :user_id,
                thrift_id = :thrift_id,
                active_thrift = :active_thrift
                ");
        $this->db->bind(":email", $data["email"]);
        $this->db->bind(":thrift_name", $data["thrift_name"]);
        $this->db->bind(":user_id", $data["user_id"]);
        $this->db->bind(":thrift_type", $data["thrift_type"]);
        $this->db->bind(":time_interval", $data["interval"]);
        $this->db->bind(":amount", $data["amount"]);
        $this->db->bind(":collection_time", $data["collection_time"]);
        $this->db->bind(":duration", $data["duration"]);
        $this->db->bind(":thrift_id", $data["thrift_id"]);
        $this->db->bind(":active_thrift", $active);


        if ($this->db->execute()) {
            $activity = "CREATED A THRIFT";
            $processID = "log_" . md5(time());
            $this->db->query("INSERT INTO activity_log 
        (activity, user_id, activity_id, process_id, amount) 
        VALUES 
        (:activity, :user_id, :activity_id, :process_id, :amount)");

            $this->db->bind(":activity", $activity);
            $this->db->bind(":user_id", $data["user_id"]);
            $this->db->bind(":activity_id", $data["thrift_id"]);
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
    public function createDailyOsusu($data)
    {

        $active = 1;

        $this->db->query(" INSERT INTO  daily_osusu_record 
            SET 
                osusu_name = :osusu_name,
                osusu_type = :osusu_type,
                amount = :amount,
                collection_time = :collection_time,
                time_interval = :time_interval,
                duration = :duration,
                email = :email,
                user_id = :user_id,
                osusu_id = :osusu_id,
                active_osusu = :active_osusu,
                player1 = :player1,
                player2 = :player2,
                player3 = :player3,
                player4 = :player4,
                player5 = :player5
                ");
        $this->db->bind(":email", $data["email"]);
        $this->db->bind(":osusu_name", $data["osusu_name"]);
        $this->db->bind(":user_id", $data["user_id"]);
        $this->db->bind(":osusu_type", $data["osusu_type"]);
        $this->db->bind(":amount", $data["amount"]);
        $this->db->bind(":collection_time", $data["collection_time"]);
        $this->db->bind(":time_interval", $data["interval"]);
        $this->db->bind(":duration", $data["duration"]);
        $this->db->bind(":osusu_id", $data["osusu_id"]);
        $this->db->bind(":active_osusu", $active);
        $this->db->bind(":player1", $data["player1"]);
        $this->db->bind(":player2", $data["player2"]);
        $this->db->bind(":player3", $data["player3"]);
        $this->db->bind(":player4", $data["player4"]);
        $this->db->bind(":player5", $data["player5"]);

        if ($this->db->execute()) {
            $activity = "CREATED A DAILY OSUSU";
            $processID = "log_" . md5(time());
            $this->db->query("INSERT INTO activity_log 
        (activity, user_id, activity_id, process_id, amount) 
        VALUES 
        (:activity, :user_id, :activity_id, :process_id, :amount)");

            $this->db->bind(":activity", $activity);
            $this->db->bind(":user_id", $data["user_id"]);
            $this->db->bind(":activity_id", $data["osusu_id"]);
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
    public function createWeeklyOsusu($data)
    {

        $active = 1;

        $this->db->query(" INSERT INTO  weekly_osusu_record 
            SET 
                osusu_name = :osusu_name,
                osusu_type = :osusu_type,
                amount = :amount,
                collection_time = :collection_time,
                time_interval = :time_interval,
                duration = :duration,
                email = :email,
                user_id = :user_id,
                osusu_id = :osusu_id,
                active_osusu = :active_osusu,
                player1 = :player1,
                player2 = :player2,
                player3 = :player3,
                player4 = :player4,
                player5 = :player5
                ");
        $this->db->bind(":email", $data["email"]);
        $this->db->bind(":osusu_name", $data["osusu_name"]);
        $this->db->bind(":user_id", $data["user_id"]);
        $this->db->bind(":osusu_type", $data["osusu_type"]);
        $this->db->bind(":amount", $data["amount"]);
        $this->db->bind(":collection_time", $data["collection_time"]);
        $this->db->bind(":time_interval", $data["interval"]);
        $this->db->bind(":duration", $data["duration"]);
        $this->db->bind(":osusu_id", $data["osusu_id"]);
        $this->db->bind(":active_osusu", $active);
        $this->db->bind(":player1", $data["player1"]);
        $this->db->bind(":player2", $data["player2"]);
        $this->db->bind(":player3", $data["player3"]);
        $this->db->bind(":player4", $data["player4"]);
        $this->db->bind(":player5", $data["player5"]);

        if ($this->db->execute()) {
            $activity = "CREATED A WEEKLY OSUSU";
            $processID = "log_" . md5(time());
            $this->db->query("INSERT INTO activity_log 
        (activity, user_id, activity_id, process_id, amount) 
        VALUES 
        (:activity, :user_id, :activity_id, :process_id, :amount)");

            $this->db->bind(":activity", $activity);
            $this->db->bind(":user_id", $data["user_id"]);
            $this->db->bind(":activity_id", $data["osusu_id"]);
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

    public function createMonthlyOsusu($data)
    {

        $active = 1;

        $this->db->query(" INSERT INTO  monthly_osusu_record 
            SET 
                osusu_name = :osusu_name,
                osusu_type = :osusu_type,
                amount = :amount,
                collection_time = :collection_time,
                time_interval = :time_interval,
                duration = :duration,
                email = :email,
                user_id = :user_id,
                osusu_id = :osusu_id,
                active_osusu = :active_osusu,
                player1 = :player1,
                player2 = :player2,
                player3 = :player3,
                player4 = :player4,
                player5 = :player5
                ");
        $this->db->bind(":email", $data["email"]);
        $this->db->bind(":osusu_name", $data["osusu_name"]);
        $this->db->bind(":user_id", $data["user_id"]);
        $this->db->bind(":osusu_type", $data["osusu_type"]);
        $this->db->bind(":amount", $data["amount"]);
        $this->db->bind(":collection_time", $data["collection_time"]);
        $this->db->bind(":time_interval", $data["interval"]);
        $this->db->bind(":duration", $data["duration"]);
        $this->db->bind(":osusu_id", $data["osusu_id"]);
        $this->db->bind(":active_osusu", $active);
        $this->db->bind(":player1", $data["player1"]);
        $this->db->bind(":player2", $data["player2"]);
        $this->db->bind(":player3", $data["player3"]);
        $this->db->bind(":player4", $data["player4"]);
        $this->db->bind(":player5", $data["player5"]);

        if ($this->db->execute()) {
            $activity = "CREATED A MONTHLY OSUSU";
            $processID = "log_" . md5(time());
            $this->db->query("INSERT INTO activity_log 
        (activity, user_id, activity_id, process_id, amount) 
        VALUES 
        (:activity, :user_id, :activity_id, :process_id, :amount)");

            $this->db->bind(":activity", $activity);
            $this->db->bind(":user_id", $data["user_id"]);
            $this->db->bind(":activity_id", $data["osusu_id"]);
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
    public function createOsusu($data)
    {

        $active = 1;

        $this->db->query(" INSERT INTO  osusu_record 
            SET 
                osusu_name = :osusu_name,
                osusu_type = :osusu_type,
                amount = :amount,
                collection_time = :collection_time,
                time_interval = :time_interval,
                duration = :duration,
                email = :email,
                user_id = :user_id,
                osusu_id = :osusu_id,
                active_osusu = :active_osusu,
                player1 = :player1,
                player2 = :player2,
                player3 = :player3,
                player4 = :player4,
                player5 = :player5
                ");
        $this->db->bind(":email", $data["email"]);
        $this->db->bind(":osusu_name", $data["osusu_name"]);
        $this->db->bind(":user_id", $data["user_id"]);
        $this->db->bind(":osusu_type", $data["osusu_type"]);
        $this->db->bind(":amount", $data["amount"]);
        $this->db->bind(":collection_time", $data["collection_time"]);
        $this->db->bind(":time_interval", $data["interval"]);
        $this->db->bind(":duration", $data["duration"]);
        $this->db->bind(":osusu_id", $data["osusu_id"]);
        $this->db->bind(":active_osusu", $active);
        $this->db->bind(":player1", $data["player1"]);
        $this->db->bind(":player2", $data["player2"]);
        $this->db->bind(":player3", $data["player3"]);
        $this->db->bind(":player4", $data["player4"]);
        $this->db->bind(":player5", $data["player5"]);

        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }
    public function editThrift($data)
    {

        $active = 1;

        $this->db->query(" UPDATE thrift_record 
            SET 
                thrift_name = :thrift_name,
                thrift_type = :thrift_type,
                time_interval = :time_interval,
                amount = :amount,
                collection_time = :collection_time,
                duration = :duration,
                active_thrift = :active_thrift
                WHERE
                email = :email,
                user_id = :user_id,
                thrift_id = :thrift_id
                ");
        $this->db->bind(":email", $data["email"]);
        $this->db->bind(":thrift_name", $data["thrift_name"]);
        $this->db->bind(":user_id", $data["user_id"]);
        $this->db->bind(":thrift_type", $data["thrift_type"]);
        $this->db->bind(":time_interval", $data["interval"]);
        $this->db->bind(":amount", $data["amount"]);
        $this->db->bind(":collection_time", $data["collection_time"]);
        $this->db->bind(":duration", $data["duration"]);
        $this->db->bind(":thrift_id", $data["thrift_id"]);
        $this->db->bind(":active_thrift", $active);


        if ($this->db->execute()) {
            $activity = "EDITED A THRIFT";
            $processID = "log_" . md5(time());
            $this->db->query("INSERT INTO activity_log 
        SET 

        activity = :activity, 
        process_id = :process_id, 
        amount =:amount,
         user_id = :user_id, 
        activity_id = :activity_id, 
       ");

            $this->db->bind(":activity", $activity);
            $this->db->bind(":user_id", $data["user_id"]);
            $this->db->bind(":activity_id", $data["thrift_id"]);
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
    public function activateThrift($data)
    {

        $active = 1;

        $this->db->query(" UPDATE thrift_record 
            SET 
                active_thrift = :active_thrift
                WHERE
                email = :email,
                user_id = :user_id,
                thrift_id = :thrift_id
                ");
        $this->db->bind(":email", $data["email"]);
        $this->db->bind(":user_id", $data["user_id"]);
        $this->db->bind(":thrift_id", $data["thrift_id"]);
        $this->db->bind(":active_thrift", $active);


        if ($this->db->execute()) {
            $activity = "ACTIVATED A THRIFT";
            $processID = "log_" . md5(time());
            $this->db->query("INSERT INTO activity_log 
        SET 

        activity = :activity, 
        process_id = :process_id, 
        amount =:amount,
         user_id = :user_id, 
        activity_id = :activity_id, 
       ");

            $this->db->bind(":activity", $activity);
            $this->db->bind(":user_id", $data["user_id"]);
            $this->db->bind(":activity_id", $data["thrift_id"]);
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
    public function deactivateThrift($data)
    {

        $active = 0;

        $this->db->query(" UPDATE thrift_record 
            SET 
                active_thrift = :active_thrift
                WHERE
                email = :email,
                user_id = :user_id,
                thrift_id = :thrift_id
                ");
        $this->db->bind(":email", $data["email"]);
        $this->db->bind(":user_id", $data["user_id"]);
        $this->db->bind(":thrift_id", $data["thrift_id"]);
        $this->db->bind(":active_thrift", $active);


        if ($this->db->execute()) {
            $activity = "DEACTIVATED A THRIFT";
            $processID = "log_" . md5(time());
            $this->db->query("INSERT INTO activity_log 
        SET 

        activity = :activity, 
        process_id = :process_id, 
        amount =:amount,
         user_id = :user_id, 
        activity_id = :activity_id, 
       ");

            $this->db->bind(":activity", $activity);
            $this->db->bind(":user_id", $data["user_id"]);
            $this->db->bind(":activity_id", $data["thrift_id"]);
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


    public function getAllThrift($date)
    {
        $this->db->query("SELECT * FROM thrift_record WHERE user_id = :user_id ");
        $this->db->bind(":user_id", $date);
       
        $rows = $this->db->resultSet();

        // Check roow
        return $rows;
    }
    public function getAllOsusu($date)
    {
        $this->db->query("SELECT * FROM osusu_record WHERE user_id = :user_id ");
        $this->db->bind(":user_id", $date);
        $rows = $this->db->resultSet();

        // Check roow
        return $rows;
    }
    public function getAllCampaign()
    {
        $this->db->query("SELECT * FROM campaign_details WHERE status = :status");
       $this->db->bind(":status", 1);
        $rows = $this->db->resultSet();

        // Check roow
        return $rows;
    }
    public function getAllTicket()
    {
        $this->db->query("SELECT * FROM ticket_details WHERE status = :status");
       $this->db->bind(":status", 1);
        $rows = $this->db->resultSet();

        // Check roow
        return $rows;
    }
    public function getAllMyCampaign($date)
    {
        $this->db->query("SELECT * FROM campaign_details WHERE user_id = :user_id AND status = :status");
        $this->db->bind(":user_id", $date);
          $this->db->bind(":status", 1);
        $rows = $this->db->resultSet();

        // Check roow
        return $rows;
    }
    public function getAllMyTicket($date)
    {
        $this->db->query("SELECT * FROM ticket_details WHERE user_id = :user_id AND status = :status ");
        $this->db->bind(":user_id", $date);
        $this->db->bind(":status", 1);
        $rows = $this->db->resultSet();

        // Check roow
        return $rows;
    }
    public function getCampaignDetails($date)
    {
        $this->db->query("SELECT * FROM campaign_details WHERE campaign_id = :campaign_id AND status = :status");
        $this->db->bind(":campaign_id", $date);
          $this->db->bind(":status", 1);
        $rows = $this->db->single();

        // Check roow
        return $rows;
    }
    public function endCampaign($date)
    {
        $this->db->query("UPDATE campaign_details SET status = :status WHERE campaign_id = :campaign_id");
        $this->db->bind(":campaign_id", $date);
          $this->db->bind(":status", 0);
        if($this->db->execute()){
            return true;
        }else{
            return false;
        }

    }
    public function endTicket($date)
    {
        $this->db->query("UPDATE ticket_details SET status = :status WHERE event_id = :event_id");
        $this->db->bind(":event_id", $date);
          $this->db->bind(":status", 0);
        if($this->db->execute()){
            return true;
        }else{
            return false;
        }

    }
    public function getTicketDetails($date)
    {
        $this->db->query("SELECT * FROM ticket_details WHERE event_id = :event_id AND status = :status");
        $this->db->bind(":event_id", $date);
        $this->db->bind(":status", 1);
        $rows = $this->db->single();

        // Check roow
        return $rows;
    }
         public function transferTicket($event_id, $new_user_id, $new_email, $old_user_id)
        {
            echo $this->db->query("UPDATE ticket_details 
                              SET email = :new_email, user_id = :new_user_id 
                              WHERE event_id = :event_id AND user_id = :old_user_id");
                              
            $this->db->bind(":event_id", $event_id);
            $this->db->bind(":new_user_id", $new_user_id);
            $this->db->bind(":old_user_id", $old_user_id);
            $this->db->bind(":new_email", $new_email);
            
            return $this->db->execute(); // No need for if-else, execute() already returns true/false
        }
                
    public function getDailyThriftRecords()
    {
        $this->db->query("SELECT * FROM daily_thrift_record");
        $rows = $this->db->resultSet();

        return $rows;
    }
    public function getWeeklyThriftRecords()
    {
        $this->db->query("SELECT * FROM weekly_thrift_record");
        $rows = $this->db->resultSet();
        return $rows;
    }
    public function getMonthlyThriftRecords()
    {
        $this->db->query("SELECT * FROM monthly_thrift_record");
        $rows = $this->db->resultSet();
        return $rows;
    }
    public function getThriftDetails($date, $ID)
    {
        $this->db->query("SELECT * FROM thrift_record WHERE user_id = :user_id  AND thrift_id = :thrift_id");
        $this->db->bind(":user_id", $date);
        $this->db->bind(":thrift_id", $ID);
        $rows = $this->db->single();
        return $rows;
    }
    public function getOsusuDetails($date, $ID)
    {
        $this->db->query("SELECT * FROM osusu_record WHERE user_id = :user_id  AND osusu_id = :osusu_id");
        $this->db->bind(":user_id", $date);
        $this->db->bind(":osusu_id", $ID);
        $rows = $this->db->single();
        return $rows;
    }
}
