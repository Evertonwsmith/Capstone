<?php
// A local instance of my_sql_cred.php is REQUIRED for this script to work. 
// The required variables are $mysql_servername, $mysql_username, $mysql_password, and $mysql_dbname

require_once 'search_catalog.php';

class my_sql
{
    public static $primary_keys = [
        "product" => ["productID"],
        "useraccount" => ["userEmail"],
        "blogpost" => ["blogID"],
        "eventpost" => ["eventID"],
        "artistaccount" => ["artistName"],
        "venueaccount" => ["venueName"],
        "orders" => ["orderID"]
    ];

    public static function select($attr, $table, $where = null, $where_attr = null, $order = null, $group = null, $having = null, $limit = null)
    {
        global $mysql_servername, $mysql_username, $mysql_password, $mysql_dbname;
        try {
            $conn = new PDO("mysql:host=$mysql_servername;dbname=$mysql_dbname", $mysql_username, $mysql_password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $query = "SELECT $attr FROM $table";
            if ($where != null) $query .= " WHERE " . $where;
            if ($group != null) $query .= " GROUP BY " . $group;
            if ($having != null) $query .= " HAVING " . $having;
            if ($order != null) $query .= " ORDER BY " . $order;
            if ($limit != null) $query .= " LIMIT " . $limit;
            $query .= ";";
            $stmt = $conn->prepare($query);

            if (isset($where_attr)) {
                //Bind where attributes
                for ($i = 0; $i < count($where_attr); $i++) {
                    $where_attr[$i] = my_sql::query_proof($where_attr[$i]);
                    $stmt->bindParam(":$i", $where_attr[$i]);
                }
            }

            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $result = array();
            // output data of each row
            foreach ($stmt->fetchAll() as $row) {
                foreach ($row as $name => $value) {
                    $row[$name] = stripslashes($value);
                }
                array_push($result, $row);
            }
            $conn = null;
            return $result;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            $conn = null;
            return false;
        }
        $conn = null;
    }

    public static function insert($table, $attr, $values, $allow_tags = false)
    {
        global $mysql_servername, $mysql_username, $mysql_password, $mysql_dbname;
        //output error if $attr and $values have different sizes
        if (count($attr) != count($values)) {
            echo "<strong>Insert Syntax Error: (my_sql.php) </strong>attribute count does not match value count.<br>";
            return false;
        }
        try {
            //Create the connection:
            $conn = new PDO("mysql:host=$mysql_servername;dbname=$mysql_dbname", $mysql_username, $mysql_password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            //Create the statement:
            $query = "INSERT INTO $table (";
            foreach ($attr as $column) {
                $query .= $column . ",";
            }
            $query = rtrim($query, ','); //trim last comma
            $query .= ") VALUES (";
            foreach ($attr as $column) {
                $query .= ":" . $column . ",";
            }
            $query = rtrim($query, ","); //trim last comma
            $query .= ");";
            $stmt = $conn->prepare($query);

            //Bind the Parameters to the prepared statement:
            for ($i = 0; $i < count($attr); $i++) {
                $values[$i] = my_sql::query_proof($values[$i]);
                if (isset($values[$i]) && !$allow_tags) $values[$i] = strip_tags($values[$i]);
                $stmt->bindParam(':' . $attr[$i], $values[$i]);
            }

            //Execute the Statement
            $result = $stmt->execute();

            // call catalog update
            search_catalog::update_catalog($table);

            $conn = null;
            return $result;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            $conn = null;
            return false;
        }
        $conn = null;
    }

    public static function insert_get_last_id($table, $attr, $values, $allow_tags = false)
    {
        global $mysql_servername, $mysql_username, $mysql_password, $mysql_dbname;
        //output error if $attr and $values have different sizes
        if (count($attr) != count($values)) {
            echo "<strong>Insert Syntax Error: (my_sql.php) </strong>attribute count does not match value count.<br>";
            $conn = null;
            return;
        }
        try {
            //Create the connection:
            $conn = new PDO("mysql:host=$mysql_servername;dbname=$mysql_dbname", $mysql_username, $mysql_password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            //Create the statement:
            $query = "INSERT INTO $table (";
            foreach ($attr as $column) {
                $query .= $column . ",";
            }
            $query = rtrim($query, ','); //trim last comma
            $query .= ") VALUES (";
            foreach ($attr as $column) {
                $query .= ":" . $column . ",";
            }
            $query = rtrim($query, ","); //trim last comma
            $query .= ");";
            $stmt = $conn->prepare($query);

            //Bind the Parameters to the prepared statement:
            for ($i = 0; $i < count($attr); $i++) {
                $values[$i] = my_sql::query_proof($values[$i]);
                if (isset($values[$i]) && !$allow_tags) $values[$i] = strip_tags($values[$i]);
                $stmt->bindParam(':' . $attr[$i], $values[$i]);
            }

            //Execute the Statement
            $stmt->execute();

            // Return the last auto-incremented ID
            $last_id = $conn->lastInsertID();

            // call catalog update
            search_catalog::update_catalog($table);

            $conn = null;
            return $last_id;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            $conn = null;
            return false;
        }
        $conn = null;
    }

    public static function update($table, $attr, $values, $where, $where_attr, $allow_tags = false)
    {
        global $mysql_servername, $mysql_username, $mysql_password, $mysql_dbname;
        if (count($attr) != count($values)) {
            echo "<strong>Update Syntax Error: (my_sql.php) </strong>attribute count does not match value count.<br>";
            $conn = null;
            return false;
        }
        try {
            $conn = new PDO("mysql:host=$mysql_servername;dbname=$mysql_dbname", $mysql_username, $mysql_password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $set = "";
            // for each attr, append value = :value
            foreach ($attr as $count) {
                //update useraccount set 'useremail'='whatever',
                $set .= " $count = :$count,";
            }

            $set = rtrim($set, ','); //trim last comma

            //Query setup
            $query = "UPDATE $table SET $set";
            $query .= " WHERE " . $where;

            $stmt = $conn->prepare($query);

            //Bind values
            for ($x = 0; $x < count($attr); $x++) {
                $values[$x] = my_sql::query_proof($values[$x]);
                if (isset($values[$x]) && !$allow_tags) $values[$x] = strip_tags($values[$x]);
                $stmt->bindParam(':' . $attr[$x], $values[$x]);
            }

            //Bind where attributes
            for ($i = 0; $i < count($where_attr); $i++) {
                $where_attr[$i] = my_sql::query_proof($where_attr[$i]);
                $stmt->bindParam(":$i", $where_attr[$i]);
            }

            $result = $stmt->execute();

            // call catalog update
            search_catalog::update_catalog($table);

            //Execute statement
            $conn = null;
            return $result;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            $conn = null;
            return false;
        }
        $conn = null;
    }

    public static function delete($table, $where, $where_attr)
    {
        global $mysql_servername, $mysql_username, $mysql_password, $mysql_dbname;
        try {
            $conn = new PDO("mysql:host=$mysql_servername;dbname=$mysql_dbname", $mysql_username, $mysql_password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Create statement
            $query = "DELETE FROM $table WHERE $where";
            $stmt = $conn->prepare($query);

            //Bind where attributes
            for ($i = 0; $i < count($where_attr); $i++) {
                $where_attr[$i] = my_sql::query_proof($where_attr[$i]);
                $stmt->bindParam(":$i", $where_attr[$i]);
            }

            $result = $stmt->execute();

            // call catalog update
            search_catalog::update_catalog($table);

            //Execute the statement
            $conn = null;
            return $result;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            $conn = null;
            return false;
        }
    }

    public static function exists($table, $where, $where_attr = null)
    {
        global $mysql_servername, $mysql_username, $mysql_password, $mysql_dbname;
        try {
            $conn = new PDO("mysql:host=$mysql_servername;dbname=$mysql_dbname", $mysql_username, $mysql_password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $query = "SELECT EXISTS(SELECT 1 FROM $table";
            if ($where != null) $query .= " WHERE " . $where;
            $query .= ") LIMIT 1;";
            $stmt = $conn->prepare($query);

            if (isset($where_attr)) {
                //Bind where attributes
                for ($i = 0; $i < count($where_attr); $i++) {
                    $where_attr[$i] = my_sql::query_proof($where_attr[$i]);
                    $stmt->bindParam(":$i", $where_attr[$i]);
                }
            }

            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            // output data of each row
            foreach ($stmt->fetchAll() as $row) {
                foreach ($row as $name => $value) {
                    $conn = null;
                    return $row[$name];
                }
            }
            $conn = null;
            return false;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
        $conn = null;
    }

    public static function query_proof($str)
    {
        if (isset($str)) $str = addslashes($str);
        return $str;
    }
}
