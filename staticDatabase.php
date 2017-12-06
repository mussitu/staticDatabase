<?php
/**
 * Created by Sublime.
 * User: Musu Turay
 * Date: 10/31/17
 * Time: 9:48 PM
 */

//turn on debugging messages
ini_set('display_errors', 'On');
error_reporting(E_ALL);

define('DATABASE', 'mt444');
define('USERNAME', 'mt444');
define('PASSWORD', 'ug1Ts82iV');
define('CONNECTION', 'sql2.njit.edu');

class dbConn{
    //variable to hold connection object.
    protected static $db;

    //private construct - class cannot be instatiated externally.
    private function __construct() {
        try {
            // assign PDO object to db variable
            self::$db = new PDO( 'mysql:host=' . CONNECTION .';dbname=' . DATABASE, USERNAME, PASSWORD );
            self::$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        }
        catch (PDOException $e) {
            //Output error - would normally log this to error file rather than output to user.
            echo "Connection Error: " . $e->getMessage();
        }
    }

    // get connection function. Static method - accessible without instantiation
    public static function getConnection() {
        //Guarantees single instance, if no connection object exists then create one.
        if (!self::$db) {
            //new connection object.
            new dbConn();
        }
        //return connection.
        return self::$db;
    }
}

class collection {
    static public function create() {
      $model = new static::$modelName;

      return $model;
    }

    static public function findAll() {

        $db = dbConn::getConnection();
        $tableName = get_called_class();
        $sql = 'SELECT * FROM ' . $tableName;
        $statement = $db->prepare($sql);
        $statement->execute();
        $class = static::$modelName;
        $statement->setFetchMode(PDO::FETCH_CLASS, $class);
        $recordsSet =  $statement->fetchAll();
        return $recordsSet;
    }

    static public function findOne($id) {

        $db = dbConn::getConnection();
        $tableName = get_called_class();
        $sql = 'SELECT * FROM ' . $tableName . ' WHERE id =' . $id;
        $statement = $db->prepare($sql);
        $statement->execute();
        $class = static::$modelName;
        $statement->setFetchMode(PDO::FETCH_CLASS, $class);
        $recordsSet =  $statement->fetchAll();
        return $recordsSet[0];
    }
    
    static public function insertOne($keyValuePairArray) {
        $db = dbConn::getConnection();
        $tableName = get_called_class();
        $sql = 'INSERT INTO ' . $tableName;
        $columnNames = '(';
        $values = '(';
        $firstIteration = TRUE;
        foreach ($keyValuePairArray as $key => $value) {
            if (! $firstIteration) {
                $columnNames .= ', ';
                $values .= ', ';
            } else {
                $firstIteration = FALSE;
            }
            
            $columnNames .= $key;
            
            if ($key == 'isdone') {
                $values .= $value;
            } else {
                $values .= "'$value'";
            }
        }
        
        $columnNames .= ')';
        $values .= ')';
        
        $sql .= ' ' . $columnNames . ' VALUES ' . $values;
        
        $statement = $db->prepare($sql);
        $statement->execute();
    }
    
    static public function updateOne($id, $keyValuePairs) {
        $db = dbConn::getConnection();
        $tableName = get_called_class();
        $sql = 'UPDATE ' . $tableName . " SET";
        $firstIteration = TRUE;
        foreach ($keyValuePairs as $key => $value) {
            if (! $firstIteration) {
                $sql .= ', ';
            } else {
                $firstIteration = FALSE;
                $sql .= ' ';
            }
            
            if ($key == 'isdone') {
                $sql .= "$key = $value";
            } else {
                $sql .= "$key = '$value'";
            }
        }
        
        $sql .= " WHERE id = $id";
        
        echo "<p>$sql</p>";
        
        $statement = $db->prepare($sql);
        $statement->execute();
    }
    
    static public function deleteOne($id) {
        $db = dbConn::getConnection();
        $tableName = get_called_class();
        $sql = 'DELETE FROM ' . $tableName . ' WHERE id =' . $id;
        $statement = $db->prepare($sql);
        $statement->execute();
    }
}

class accounts extends collection {
    protected static $modelName = 'account';
}

class todos extends collection {
    protected static $modelName = 'todo';
}

abstract class model {
    protected $tableName;
    public function save()
    {
        if ($this->id == '') {
            $sql = $this->insert();
        } else {
            $sql = $this->update();
        }

        $db = dbConn::getConnection();
        $statement = $db->prepare($sql);
        $statement->execute();

        $this->tableName = get_called_class();

        $array = get_object_vars($this);
        $columnString = implode(',', $array);
        $valueString = ":".implode(',:', $array);
       // echo "INSERT INTO $this->tableName (" . $columnString . ") VALUES (" . $valueString . ")</br>";

        echo 'I just saved record: ' . $this->id;
    }

    public function insert() {
        $sql = 'sometthing';
        return $sql;
    }

    public function update() {
        $sql = 'sometthing';
        return $sql;
        echo 'I just updated record' . $this->id;
    }

    public function delete() {
        echo 'I just deleted record' . $this->id;
    }
    
    protected abstract static function format_to_html_table_header();
    protected abstract function format_to_html_table_row();
    public abstract static function format_to_html($rows);
}

class account extends model {
    public $id;
    public $email;
    public $fname;
    public $lname;
    public $phone;
    public $birthday;
    public $gender;
    public $password;



    public function __construct()
    {
        $this->tableName = 'accounts';
    
    }

    public function insert() {
        $record = todos::insertOne(array('ownerid' => $this->email,
                                         fname=> $this->fname,
                                         lname=> $this->lname,
                                         phone=> $this->phone,
                                         birthday=> $this->birthday,
                                         gender=> $this->gender,
                                         password=> $this->password));
        return $record;
    }

    public function update() {
        todos::updateOne($this->id,
                              array'ownerid' => $this->email,
                                         fname=> $this->fname,
                                         lname=> $this->lname,
                                         phone=> $this->phone,
                                         birthday=> $this->birthday,
                                         gender=> $this->gender,
                                         password=> $this->password));
        return $this;
    }

    public function delete() {
       todos::deleteOne($this->id);
    }
    
    protected static function format_to_html_table_header() {
        return "<tr><td>ownerid</td><td>personalname</td>"
                . "<td>familyname</td></tr>";
    }
    protected function format_to_html_table_row() {
        return "<tr><th>$this->id</th><th>$this->personalname</th>"
                . "<th>$this->familyname</th></tr>";
    }
    public static function format_to_html($rows) {
        $result = '<table>' . account::format_to_html_table_header();
        
        if ($rows != NULL) {
            foreach ($rows as $row) {
                $result .= $row->format_to_html_table_row();
            }
        }
        
        $result .= '</table>';
        
        return $result;
    }
}

class todo extends model {
    public $id;
    public $owneremail;
    public $ownerid;
    public $createddate;
    public $duedate;
    public $message;
    public $isdone;

    public function __construct()
    {
        $this->tableName = 'todos';
    
    }
    
    protected static function format_to_html_table_header() {
        return "<tr><td>id</td><td>owneremail</td>"
                . "<td>ownerid</td><td>createddate</td>"
                . "<td>duedate</td><td>message</td>"
                . "<td>isdone</td></tr>";
    }
    
    protected function format_to_html_table_row() {
        return "<tr><th>$this->id</th><th>$this->owneremail</th>"
                . "<th>$this->ownerid</th><th>$this->createddate</th>"
                . "<th>$this->duedate</th><th>$this->message</th>"
                . "<th>$this->isdone</th></tr>";
    }
    
    public static function format_to_html($something) {
        $result = '<table>' . todo::format_to_html_table_header();
        
        if (is_a($something, 'todo')) {
            $result .= $something->format_to_html_table_row();
        } elseif ($something != NULL) {
            foreach ($something as $row) {
                $result .= $row->format_to_html_table_row();
            }
        }
        
        $result .= '</table>';
        
        return $result;
    }

    public function insert() {
        $record = todos::insertOne(array('owneremail' => $this->owneremail,
                       'ownerid' => $this->ownerid,
                       'createddate' => $this->createdate,
                       'duedate' => $this->duedate,
                       'message' => $this->message,
                       'isdone' => $this->isdone));
        return $record;
    }

    public function update() {
        todos::updateOne($this->id,
                array('owneremail' => $this->owneremail,
                      'ownerid' => $this->ownerid,
                      'createddate' => $this->createdate,
                      'duedate' => $this->duedate,
                      'message' => $this->message,
                      'isdone' => $this->isdone));
        return $this;
    }

    public function delete() {
       todos::deleteOne($this->id);
    }
}

// this would be the method to put in the index page for accounts
echo '<h1>Find All Account Records</h1>';

$records = accounts::findAll();
echo account::format_to_html($records);
echo '<br>';

echo '<h1>Find All ToDo Records</h1>';
// this would be the method to put in the index page for todos
$records = todos::findAll();
echo todo::format_to_html($records);

// print_r($records);

echo '<h1>Find ToDo Record with ID 1</h1>';
//this code is used to get one record and is used for showing one record or updating one record
$record = todos::findOne(1);
echo todo::format_to_html($record);
//print_r($record);

echo '<h1>Insert New ToDo Record</h1>';
$record = todos::insertOne(array('owneremail' => 'hera@greece.gov',
                       'ownerid' => 'aDAWi77l6SWWFJkdC1lX',
                       'createddate' => "20171010",
                       'duedate' => "20171010",
                       'message' => 'Do something about Aphrodite.',
                       'isdone' => 0));
echo '<h1>Find All ToDo Records After Inserting</h1>';
// this would be the method to put in the index page for todos
$records = todos::findAll();
echo todo::format_to_html($records);

$lastInTable = end($records);

echo "<h1>Delete Record With ID $lastInTable->id</h1>";

todos::deleteOne($lastInTable->id);

echo '<h1>Find All ToDo Records After Deleting</h1>';
// this would be the method to put in the index page for todos
$records = todos::findAll();
echo todo::format_to_html($records);

$records = todos::findAll();
$lastInTable = end($records);
echo "<h1>Update Record With ID $lastInTable->id</h1>";
todos::updateOne($lastInTable->id, array('owneremail' => 'random@person.earth'));

echo '<h1>Find All ToDo Records After Updating</h1>';
// this would be the method to put in the index page for todos
$records = todos::findAll();
echo todo::format_to_html($records);

//this is used to save the record or update it (if you know how to make update work and insert)


// $record->save();

//$record = accounts::findOne(1);
/*
//This is how you would save a new todo item
$record = new todo();
$record->message = 'some task';
$record->isdone = 0;
//$record->save();

print_r($record);

$record = todos::create();
print_r($record);
*/



