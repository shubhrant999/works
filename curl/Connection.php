<?php
require_once('../affise/mailer/vendor/autoload.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


class Connection {

    function __construct() {

        $username = 'dbuser.tools';
        $password = 'k%$kfg^4gRtYdy$f$743S';
        $servername = "localhost";
        $dbname = "dbtool";

        try {
            $this->db = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }


    //fetch content from curl url.
    function fetch_curl($url, $data, $method = 'GET') {

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            $response = "cURL Error #:" . $err;
        }
        return $response;
    }

    function fetch_curlheader($url, $data, $method = 'GET', $header = array()) {

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => $header,
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            $response = "cURL Error #:" . $err;
        }
        return $response;
    }

    function exe_raw_query($query, $where = array()) {
        $stmt = $this->db->prepare($query);
        $stmt->execute($where);
        if ($stmt->rowCount() == 0) {
            return false;
        }
        return true;
    }

    function exe_count_query($query, $where = array()) {
        $stmt = $this->db->prepare($query);
        $stmt->execute($where);
        return $stmt->rowCount();
    }

    //record log data to database table "requestlogs".
    function insertLogger($params, $table = 'requestlogs') {

        $insert_query_key_array = array();
        $insert_query_val_array = array();
        foreach ($params as $key => $value) {
            $insert_query_val_array[] = $key;

            $key2 = str_replace(':', '', $key);
            $insert_query_key_array[] = $key2;
        }

        $insert_query_key_array = implode(',', $insert_query_key_array);
        $insert_query_val_array = implode(',', $insert_query_val_array);

        $query = 'INSERT INTO ' . $table . '(' . $insert_query_key_array . ') VALUES (' . $insert_query_val_array . ')';

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);

        if ($stmt->rowCount() == 1) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    // $value in single quote eg name='abc'
    function update($table = 'requestlogs', $params, $where) {

        $insert_query_key_array = array();
        $insert_query_val_array = array();
        foreach ($params as $key => $value) {
            $insert_query_val_array[] = $key . "='" . $value . "'";            
        }

        $insert_query_val_array = implode(',', $insert_query_val_array);

        $query = 'UPDATE ' . $table . ' SET ' . $insert_query_val_array . ' WHERE id=:id';
        $stmt = $this->db->prepare($query);
        $stmt->execute($where);
        if ($stmt->rowCount() == 1) {
            return $stmt->rowCount();
        }
        return false;
    }

    // $value in double quote eg name="abc"
    function update_new($table = 'requestlogs', $params, $where) {

        $insert_query_key_array = array();
        $insert_query_val_array = array();
        foreach ($params as $key => $value) {
            $insert_query_val_array[] = $key . '="' . $value . '"';
        }

        $insert_query_val_array = implode(',', $insert_query_val_array);

        $query = 'UPDATE ' . $table . ' SET ' . $insert_query_val_array . ' WHERE id=:id';
        $stmt = $this->db->prepare($query);
        $stmt->execute($where);
        if ($stmt->rowCount() == 1) {
            return $stmt->rowCount();
        }
        return false;
    }

    //fetch and return schema of input table.
    public function schema($table) {
        $q = $this->db->prepare("SHOW COLUMNS FROM `$table`");
        $q->execute();
        return $q->fetchAll();
    }

    //run costom query from database.
    function sel_query($query, $where = array()) {
        $stmt = $this->db->prepare($query);
        $stmt->execute($where);

        if ($stmt->rowCount() == 0) {
            return false;
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    //run costom query from database.
    function exe_query($query, $where = array()){
        $stmt = $this->db->prepare($query);
        $stmt->execute($where);
        if($stmt->rowCount() == 0){
            return false;
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //run costom query from database.
    function exe_query_row($query, $where = array()){
        $stmt = $this->db->prepare($query);
        $stmt->execute($where);
        if($stmt->rowCount() == 0){
            return false;
        }
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    /* Make url query from params  */
    function change_query($url, $array) {
        $url_decomposition = parse_url($url);
        $cut_url = explode('?', $url);
        $queries = array_key_exists('query', $url_decomposition) ? $url_decomposition['query'] : false;
        $queries_array = array();
        if ($queries) {
            $cut_queries = explode('&', $queries);
            foreach ($cut_queries as $k => $v) {
                if ($v) {
                    $tmp = explode('=', $v);
                    if (sizeof($tmp) < 2)
                        $tmp[1] = true;
                    if (!isset($array[$tmp[0]]) || !empty($array[$tmp[0]])) {
                        $queries_array[$tmp[0]] = urlencode($tmp[1]);
                    }
                }
            }
        }
        if (isset($array['e']) && empty($array['e'])) {
            unset($array['e']);
        }
        $newQueries = array_merge($queries_array, $array);
        return $cut_url[0] . '?' . http_build_query($newQueries);
    }


    function compose_email($data, $to = '') {

        if ($to != '') {
            $_toEmail = $to;
        } else {
            $_toEmail = 'komal.s@xaprio.com';
        }

        $from = 'info@server.aarth.com';

        // To send HTML mail, the Content-type header must be set
        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

        // Create email headers
        $headers .= 'From: ' . $from . "\r\n" .
                'Reply-To: ' . $from . "\r\n" .
                'X-Mailer: PHP/' . phpversion();

        //"CC: komal.s@xaprio.com\r\n".


        $subject = $data['subject'];

        $msg = $data['message'];


        $msg = str_replace(':ip', $_SERVER['REMOTE_ADDR'], $msg);

        // use wordwrap() if lines are longer than 70 characters
        $msg = wordwrap($msg, 70);

        // send email
        return mail($_toEmail, $subject, $msg, $headers);
    }

    function cleanPhoneNumber($mobile_number, $maxdigit = 10) {
        $strlen = strlen($mobile_number);
        $newPhoneNumber = '';
        for ($i = $strlen - 1; $i >= 0; $i--) {
            if (strlen($newPhoneNumber) >= 10) {
                break;
            }
            if (is_numeric($mobile_number[$i])) {
                $newPhoneNumber .= $mobile_number[$i];
            }
        }
        return strrev($newPhoneNumber);
    }

    function print_number_count($num) {
        if ($num > 1000) {
            $x = round($num);
            $x_number_format = number_format($x);
            $x_array = explode(',', $x_number_format);
            $x_parts = array('K', 'M', 'B', 'T');
            $x_count_parts = count($x_array) - 1;
            $x_display = $x;
            $x_display = $x_array[0] . ((int) $x_array[1][0] !== 0 ? '.' . $x_array[1][0] : '');
            $x_display .= $x_parts[$x_count_parts - 1];
            return $x_display;
        }
        return $num;
    }


    function smtp_email($fromEmail, $toemail, $subject, $body, $filename = ''){

        $mail = new PHPMailer(true);                                                // Passing `true` enables exceptions
        try {
            //Server settings
            $mail->SMTPDebug = false;                                               // Enable verbose debug output
            $mail->isSMTP();                                                        // Set mailer to use SMTP
            $mail->Host         = 'smtp.sendgrid.net';  //'smtp.gmail.com';                 // Specify main and backup SMTP servers
            $mail->SMTPAuth     = true;                                                 // Enable SMTP authentication
            $mail->Username     = 'rohit_arora';//   'shubhrant999@gmail.com';           // SMTP username
            $mail->Password     = 'e%^7YhgFR5^7u65Rf'; //'shubh@9999';                  // SMTP password
            $mail->SMTPSecure   = 'tls';                                             // Enable TLS encryption, `ssl` also accepted
            $mail->Port         = 587;                                                     // TCP port to connect to

            //Recipients
            $mail->setFrom($fromEmail, '');
            
            if(is_array($toemail)){
                foreach ($toemail as $key => $toemailId) {
                    $mail->addAddress($toemailId);
                }
            }else{
                $mail->addAddress($toemail);
            }
             

            //Attachments
            if(file_exists($filename) == 1){
                $mail->addAttachment($filename);         // Add attachments
            }            

            //Content
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = $subject;              

            $mail->Body = $body;
            return $mail->send();

        } catch (Exception $e) {
            return $mail->ErrorInfo;
        }   
    }

}
