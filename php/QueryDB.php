<?php
/*
 * Class to connect and query DB
 */

class QueryDB {

    public function query($sql) {

        // connect
        define("DB_HOST", "127.0.0.1");
        define("DB_USER", "root");
        define("DB_PASS", "");
        define("DB_NAME", "moviedb");

        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS,DB_NAME);

        //print_r($mysqli);

        if ($mysqli->connect_errno) {
            printf("Connect failed: %s\n", $mysqli->connect_error);
            exit();
        }

        // query
        foreach($sql as $query)
        {
            $result = $mysqli->query($query);
            if($result->num_rows > 0)
            {
                debugEcho("Using query $query");
                break;
            }
        }

        $db_results = null;
        if (!$result) {
            $db_results = false;
            //$db_results .= "DB Error, could not query the database\n";
            //$db_results .= 'MySQL Error: ' . mysql_error();
            //$db_results .= "query: $sql";
        }
        else
        {
            $db_results = array();
            while ($row = $result->fetch_assoc()) {
                $db_results[] = $row;
                //echo $row[$class] . "\n";
                //echo "<br/>";
            }
            $result->free();
        }

        $mysqli->close();

        return $db_results;
    }

}
