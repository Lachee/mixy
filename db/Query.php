<?php
namespace db;

class Query {

    protected $conn;
    
    protected $query;
    protected $from;
    protected $values = [];
    protected $fields = [];
    protected $limit = -1;
    protected $orderBy = null;
    protected $order = 'DESC';
    protected $includeNull = false;
    
    /** @var mixed An array of arrays. Each sub array represents the joiner, field, operator, value */
    protected $wheres = [];

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    /** The current DB connection */
    public function db() : Connection { return $this->conn; }

    /** Sets the table.
     * @param string $tableName name of the table.
     * @return Query
     */
    public function from($tableName) {
        $this->from = $tableName;
        return $this;
    }

    /** Performs a SQL SELECT
     * @param string $from name of the table.
     * @return Query
     */
    public function select($from = null)
    {
        $this->query = "SELECT";
        $this->from = $from ?? $this->from;
        $this->fields = [ "*" ];
        return $this;
    }

    /** Sets only specific fields
     * @param string|string[] $fields the columns to select
     * @return Query */
    public function fields($fields) {
        if (is_array($fields)) {
            $this->fields = $fields;
        } else {
            $this->fields = [ $fields ];
        }

        return $this;
    }

    /** sets if null parameters should be included. 
     * @param bool $state
     * @return Query
    */
    public function withNull($state = true) {
        $this->includeNull = $state;
        return $this;
    }

    /** sets if null parameters should be included. 
     * @param string $from name of the table.
     * @return Query
    */
    public function delete($from = null) {
        $this->query = "DELETE";
        $this->from = $from ?? $this->from;
        return $this;
    }

    /** Updates a table
     * @param string[] $values the values to update
     * @param string $from name of the table.
     * @return Query
    */
    public function update($values, $from = null) {
        $this->query = "UPDATE";
        $this->values = $values;
        $this->from = $from ?? $this->from;
        return $this;
    }

    /** Insert into a table
     * @param string[] $values the values to update
     * @param string $from name of the table.
     * @return Query
    */
    public function insert($values, $from = null) {
        $this->query = "INSERT";
        $this->values = $values;
        $this->from = $from ?? $this->from;
        return $this;
    }

    /** Inserts or Updates a table
     * @param string[] $values the values to update
     * @param string $from name of the table.
     * @return Query
    */
    public function insertOrUpdate($values, $from = null) {
        $this->query = "INSERT_OR_UPDATE";
        $this->values = $values;
        $this->from = $from ?? $this->from;
        return $this;
    }

    /** Where condition. 
     * @param array[] $params parameters. eg: [ [ key, value ], [ key, op, value ] ]
     * @param string $method operator, ie and.
     * @return Query
    */
    public function where($params, $method = 'and') {
        if (!is_array($params))
            throw new \Exception("where parameter is not an array");

        if (count($params) == 0)
            throw new \Exception("where parameter cannot be empty");

        if (is_array($params[0])) {
            //We are an array of AND WHERES
            // so we will recursively add them
            foreach($params as $p) {
                $this->where($p, $method);
            }

            return $this;
        }

        $field = ''; $operator = '='; $value = '';
        if (count($params) == 2) {       
            $field = $params[0];
            $value = $params[1];
        } else {
            $field = $params[0];
            $operator = $params[1];
            $value = $params[2];
        }


        $this->wheres[] = [$method, $field, $operator, $value ];
        return $this;
    }

    /** And Where on the query
     * @param mixed[] $params parameters.
     * @return Query 
    */
    public function andWhere($params) { return $this->where($params, 'and'); }

    /** Or Where on the query
     * @param mixed[] $params parameters.
     * @return Query 
    */
    public function orWhere($params) { return $this->where($params, 'or'); }

    /** Limit the count of values returned
     * @param int $count the number of rows to limit
     * @return Query 
    */
    public function limit($count) { 
        $this->limit = $count;
        return $this;
    }

    /** Order the query by the value */
    public function orderByDesc($field) {
        $this->orderBy = $field;
        $this->order = 'DESC';
        return $this;
    }

    /** Order the query by the value ascending. */
    public function orderByAsc($field) { 
        $this->orderBy = $field;
        $this->order = "ASC";
        return $this;
    }

    /** Builds the query 
     * @return array Array containing the query and an array of binding values.
    */
    public function build() {
        $query = "";

        $bindings = [];

        $value_fields   = [];
        $value_binds    = [];

        foreach($this->values as $key => $pair) {
            if ($pair !== null || $this->includeNull) {
                $value_fields[] = $key;
                $value_binds[] = "?";
                if (is_bool($pair)) $pair = $pair === true ? 1 : 0;
                $bindings[]     = $pair;
            }
        }


        switch ($this->query) {
            case "SELECT":
                $fields = join(", ", $this->fields);
                $query = "SELECT {$fields} FROM {$this->from}";
                break;

            case "DELETE":
                $query = "DELETE FROM {$this->from}";
                break;

            case "UPDATE":
                $query = "UPDATE {$this->from} (".join(',', $value_fields).") VALUES (".join(',', $value_binds).")";
                break;

            case "INSERT":
                $query = "INSERT INTO {$this->from} (".join(',', $value_fields).") VALUES (".join(',', $value_binds).")";
                break;            
                
            case "INSERT_OR_UPDATE":
                $dupe = [];
                foreach($this->values as $key => $pair) {
                    if ($pair !== null || $this->includeNull) {
                        $dupe[] = $key . " = ?";
                        if (is_bool($pair)) $pair = $pair === true ? 1 : 0;
                        $bindings[]     = $pair;
                    }
                }

                $query = "INSERT INTO {$this->from} (".join(',', $value_fields).") VALUES (".join(',', $value_binds).") ON DUPLICATE KEY UPDATE " . join(', ', $dupe );
                $this->wheres = null;
                $this->limit = -1;
                $this->orderBy = null;
                break;
        }

        //Create the where statement
        $wheres = "";
        if ($this->wheres != null && is_array($this->wheres)) {
            foreach ($this->wheres as $w) {
                if (is_bool($w[3])) $w[3] = $w[3] === true ? 1 : 0;
                if (empty($wheres)) { 
                    $wheres .= " WHERE {$w[1]} {$w[2]} ?";
                } else {
                    $wheres .= " {$w[0]} {$w[1]} {$w[2]} ?";
                }
                $bindings[] = $w[3];
            }
        }
        $query .= $wheres;

        //Create the order statement
        if ($this->orderBy != null) {
            $query .= " ORDER BY {$this->orderBy} {$this->order}";
        }

        //Create the limit
        if ($this->limit > 0) {
            $query .= " LIMIT {$this->limit}";
        }

        //Return the query and binding
        return array($query, $bindings);
    }

    /** Builds the query and executes it, returning the result of the execute.
     * @return array|int|false  If the query is select then it will return an associative array of the object; otherwise it will return the last auto incremented id.
     */
    public function execute() {
        list($query, $bindings) = $this->build();
        
        $stm = $this->conn->prepare($query);
        for($i = 0; $i < count($bindings); $i++) {
            $stm->bindParam($i + 1, $bindings[$i]);
        }

        //Execute and check if we fail or not
        $result = $stm->execute();
        if (!$result) {
            $err = $stm->errorInfo();
            throw new \Exception("SQL Exception: ( " . join(" : ", $err) . " )");
            return false;
        }

        //Select is the only one where we want to return the object
        if ($this->query == "SELECT") 
            return $stm->fetchAll(\PDO::FETCH_ASSOC);

        //Everything else returns the last inserted ID
        return $this->conn->lastInsertId();
    }
}
