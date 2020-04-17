<?php namespace kiss\db;
class ActiveQuery extends Query {

    private $className;

    public function __construct(Connection $conn, $class) 
    {
        parent::__construct($conn);
        $this->className = $class;
        $this->select($class::tableName());
    }

    /** Fetches a single record 
     * @param $assoc Should associative arrays be returned instead? 
     * @param $extractScalar Should scalar values be removed from their object?
     * @return ActiveRecord|null|false the records
    */
    public function one($assoc = false, $extractScalar = false) {
        //Execute the query
        $result = $this->limit(1)->execute();
        if ($result !== false) {
            foreach($result as $r) {

                if ($assoc) { 
                    $instance =  $extractScalar && count($this->fields) == 1 ? $r[$this->fields[0]] : $r;
                } else {
                    //Create a new instance of the class
                    $instance = new $this->className;
                    $instance->setQueryResult($r);
                }
                
                return $instance; 
            }
        }

        return false;
    }

    /** Fetch all records.
     * @param $assoc Should associative arrays be returned instead?
     * @param $extractScalar Should scalar values be removed from their object?
     * @return ActiveRecord[]|false the records
     */
    public function all($assoc = false, $extractScalar = false) {

        //Prepare a list of instances
        $instances = [];

        //Execute the query
        $result = $this->execute();
        if ($result !== false) {

            foreach($result as $r) {
                if ($assoc) { 
                    $instance = $extractScalar && count($this->fields) == 1 ? $r[$this->fields[0]] : $r;
                } else {
                    //Create a new instance of the class
                    $instance = new $this->className;
                    $instance->setQueryResult($r);
                }

                $instances[] = $instance;
            }

            return $instances;
        }

        return false;
    }

}