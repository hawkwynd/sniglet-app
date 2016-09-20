<?php

/**********************************************
 * Random snigletr Ajax Application
 * Database config file
 * scott fleming
 * v1.0 - April 2011
 ***********************************************/

require("database.class.php");
require("functions.php");
$iniconfig = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/../conf/' . php_uname('n') . '.ini', true);

//database server 
define('DB_SERVER', "localhost");
define('DB_SERVER', $iniconfig['sniglet']['db_server']);
//database login name
define('DB_USER', $iniconfig['sniglet']['db_user']);
//database login password
define('DB_PASS', $iniconfig['sniglet']['db_password']);
//database name
define('DB_DATABASE', $iniconfig['sniglet']['db_database']);
//smart to define your table names also
define('TABLE_SNIGLETS', $iniconfig['sniglet']['db_table']);


class sniglet
{

    function logout() {
        $_SESSION = array();

        // If it's desired to kill the session, also delete the session cookie.
        // Note: This will destroy the session, and not just the session data!
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        // Finally, destroy the session.
        session_destroy();
        return;
    }


    function login($login) {
        $db = new Database(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);
        $db->connect();
        $name = mysql_real_escape_string($login['username']); // The function mysql_real_escape_string() stops hackers!
        $pass = md5($login['password']);
        $sql = "SELECT * FROM sniglet_users WHERE users_name = '{$name}' AND users_password = '{$pass}'";
        return $db->fetch_all_array($sql);

    }


    function searchSniglet($searchTxt)
    {

        $db = new Database(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);
        $db->connect();
        $sql = "SELECT * FROM sniglets WHERE sniglet_term LIKE '%" . $searchTxt . "%' OR sniglet_definition like '%" . $searchTxt . "%'";

        return $db->fetch_all_array($sql);


    }

    function listAll()
    {
        $db = new Database(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);
        $db->connect();
        $sql = "Select sniglet_term, sniglet_phonetics, sniglet_type, sniglet_definition FROM " . TABLE_SNIGLETS .
            " ORDER BY sniglet_term";
        return json_encode($db->fetch_all_array($sql));
    }


    function getRandomsniglet()
    {

        $db = new Database(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);
        $db->connect();
        $num = 1; // random generator start

        // Count records approved only
        $sql = "SELECT COUNT(*) as TotalRecs from " . TABLE_SNIGLETS;
        $result = $db->query($sql);

        $totalCount = $db->fetch_array($result);


        // randomize the results from the count
        $rnd_record_id = rand($num, $totalCount['TotalRecs']);

        // select a record, return ID, sniglettxt, likes, dislikes and include the total number of records currently
        // in the entire database

        $sql = "SELECT * from " . TABLE_SNIGLETS . " where sniglet_id = " . $rnd_record_id;
        // get the first returned result

        $record = $db->query_first($sql);
        $db->close();

        return json_encode($record); // send it json_encoded as an array
    }


    function getSnigletStats()
    {
        $stat = array();
        $db = new Database(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);
        $db->connect();

        $sql = "SELECT sniglet_id, sniglet_term,sniglet_phonetics,sniglet_definition,
              DATE_FORMAT(sniglet_date, '%b %d, %Y') as sniglet_date,
                (select count(*) from sniglets where sniglet_type LIKE '%v.') as verbCount,
                (select count(*) from sniglets where sniglet_type LIKE '%n.') as nounCount,
                (select count(*) from sniglets where sniglet_type LIKE '%adj.') as adjCount,
                (select count(*) from sniglets where isActive = '1') as activeCount
             FROM sniglets
            ORDER by sniglet_id DESC LIMIT 1;";


        return json_encode($db->fetch_all_array($sql));

    }


    function getTopsniglets()
    {

        $db = new Database(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);
        $db->connect();
        $num = 1; // random generator start

        // Count records
        $sql = "SELECT recordid from " . TABLE_SNIGLETS . " where likes > 1";
        $result = $db->query($sql);

        $TopRecs = $db->fetch_array($result);


        // randomize the results from the count
        $rnd_record_id = array_rand($TopRecs);

        // select a record, return ID, quotetxt, likes, dislikes
        $sql = "SELECT recordid, QuoteTxt, likes, dislikes FROM `" . TABLE_ONELINERS . "`
	   	      WHERE recordid=$rnd_record_id";

        // get the first returned result
        $record = $db->query_first($sql);
        $db->close();

        return json_encode($record); // send it json_encoded as an array

    }


    function castVote($sniglet_id, $vote)
    {

        // Update the database with the vote cast and
        // return the results back to the display with
        // new results

        $db = new Database(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);
        $db->connect();

        switch ($vote) {
            case 'like':
                $data['likes'] = "INCREMENT(1)";
                break;
            case 'dislike':
                $data['dislikes'] = "INCREMENT(1)";
                break;
        }

            $db->query_update(TABLE_SNIGLETS, $data, "sniglet_id=$sniglet_id");


        // return with refreshed results
        $sql = "SELECT recordid, likes, dislikes FROM `" . TABLE_SNIGLETS . "`WHERE sniglet_id=$sniglet_id";
        $record = $db->query_first($sql);
        $db->close();

        return json_encode($record);

    }

    function addSniglet($data)
    {

        $message = array();
        $db = new Database(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);
        $db->connect();

        $query = "insert into sniglets (sniglet_term,sniglet_phonetics,sniglet_type,sniglet_definition)
                  select '". $data['sniglet_term'] . "',
                  '" . $data['sniglet_phonetics'] ."',
                  '" . $data['sniglet_type'] ."',
                  '" . $data['sniglet_definition'] . "'
                  FROM dual where not exists (SELECT * from sniglets where sniglet_term in ('" . $data['sniglet_term'] ."'))";
        $result = mysql_query($query);
        $primary_id = mysql_insert_id();

        //$primary_id = $db->query_insert(TABLE_SNIGLETS, $data);

        $message['successMsg'] = "A new sniglet has been added to the database.\n";
        $message['ID'] = $primary_id;
        $message['sniglet_term'] = stripslashes($data['sniglet_term']);
        $message['sniglet_phonetics'] = stripslashes($data['sniglet_phonetics']);
        $message['sniglet_type'] = $data['sniglet_type'];
        $message['sniglet_definition'] = stripslashes($data['sniglet_definition']);

        echo json_encode($message);

    }


    function approve_sniglet($sniglet_id)
    {

        $db = new Database(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);
        $db->connect();
        $data['approved'] = 1;
        $record = $db->query_update(TABLE_SNIGLETS, $data, "sniglet_id=$sniglet_id");

        return "Sniglet " . $id . " has been approved. ";

    }

    function getUnapproved()
    {

        $db = new Database(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);
        $db->connect();
        $sql = "SELECT sniglet_id, sniglet_term  FROM " . TABLE_SNIGLETS . " WHERE isActive < 1";
        $record = $db->fetch_all_array($sql);

        $db->close();

        return $record;
    }


    function makeLinks() {
        $out = '';
        $outTop = '<div id="find">Find Sniglets by letter</div>';
        $db = new Database(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);
        $db->connect();
        $sql = "SELECT distinct LEFT(UCASE(sniglet_term), 1) as alpha  FROM sniglets ORDER by sniglet_term ASC";

        $row = $db->fetch_all_array($sql);
        foreach($row as $record){
            $out .= '| <a href="javascript:fetchAlpha(\''. $record['alpha'] .'\')">'. $record['alpha'].'</a> ';
        }

        return $outTop. substr( $out, 2);
    }

    function searchByAlpha($letter) {
        $db = new Database(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);
        $db->connect();
        $sql="SELECT * from sniglets where UCASE(LEFT(sniglet_term, 1)) in('". $letter. "')";
        return json_encode($db->fetch_all_array($sql));
    }


}


?>
