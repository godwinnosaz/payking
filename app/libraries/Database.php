<?php 
/*
*PDO Database Class
*Connect to database
*Create prepare statements
*Bind values
*Return rows and results
*/


class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;

    private $dbh;
    private $stmt;
    private $error;

    public function __construct(){
        // Set DSN
        $dsn = 'mysql:host=' . $this->host. ';dbname=' . $this->dbname;

        $options = array(
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        );

        //Create PDO instance
        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
           echo "Invalid RequestID !"; //$this->error;
          exit;
        }
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
    

    //Prepare Statement
    public function query($sql){
        
        try {
            $this-> stmt = $this->dbh->prepare($sql);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();

            $res = ['status_code' => 504,'status' => false, 'message' => $this->error];
           $this->handleResponse($res);
          exit;
        }
    }


    //Bind values
    public function bind($param, $value, $type= null){
        if(is_null($type)){
            switch(true){
                case is_int($value);
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value);
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value);
                    $type = PDO::PARAM_NULL;
                    break;
               default:
                    $type = PDO::PARAM_STR;
            }
        }

        

        try {
            $this->stmt->bindValue($param, $value, $type);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();

            $res = ['status_code' => 504,'status' => false, 'message' => $this->error];
           $this->handleResponse($res);
          exit;
        }
    }

    //Execute the prepared stateent
    public function execute(){
       

        try {
            return  $this->stmt->execute();
        } catch (PDOException $e) {
            $this->error = $e->getMessage();

            $res = ['status_code' => 504,'status' => false, 'message' => $this->error];
           $this->handleResponse($res);
          exit;
        }

    }

    //Get result set as array of objects
    public function resultSet(){
     
        try {
            $this->execute();
            return $this->stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();

            $res = ['status_code' => 504,'status' => false, 'message' => $this->error];
           $this->handleResponse($res);
          exit;
        }
    }

    // Get single record as object
    public function single(){
       

        
        try {
            $this->execute();
            return $this->stmt->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();

            $res = ['status_code' => 504,'status' => false, 'message' => $this->error];
           $this->handleResponse($res);
          exit;
        }
    }

    public function rowCount(){
       
        try {
            return $this->stmt->rowCount();
        } catch (PDOException $e) {
            $this->error = $e->getMessage();

            $res = ['status_code' => 504,'status' => false, 'message' => $this->error];
           $this->handleResponse($res);
          exit;
        }
    }

}
        

?>