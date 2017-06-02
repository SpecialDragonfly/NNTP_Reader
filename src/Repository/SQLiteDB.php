<?php
namespace Net\Repository;

use SQLite3;

class SQLiteDB extends SQLite3 implements DBInterface
{
    /**
     * Returns true if the table exists.
     *
     * @param $table
     *
     * @return bool
     */
    public function tableExists(string $table) : bool
    {
        $result = $this->querySingle("SELECT name from sqlite_master WHERE type='table' AND name='$table'");
        return $result !== null;
    }

    /**
     * @param string $table
     * @param array  $where
     * @param int    $offset
     * @param int    $limit
     *
     * @return array
     */
    public function selectAllFrom(string $table, array $where, int $offset, int $limit) : array
    {
        $results = array();
        $sql = "SELECT * FROM ".$table;
        if (!empty($where)) {
            $sql .= " WHERE ";
            $sql .= implode(" AND ", array_map(function($k){return $k.' = :'.$k;}, array_keys($where)));
        }
        if ($limit !== 0) {
            $sql .= " limit ".$offset.", ".$limit;
        }
        $statement = $this->prepare($sql);
        foreach ($where as $key => $value) {
            $statement->bindValue(':'.$key, $value);
        }
        $existingItems = $statement->execute();

	    $row = $existingItems->fetchArray(SQLITE3_ASSOC);
        if ($row === false) {
            return $results;
        }

	    $results[] = $row;
        while($row = $existingItems->fetchArray(SQLITE3_ASSOC)) {
            $results[] = $row;
        }

        return $results;
    }

    public function selectOneFrom(string $table, string $primaryKey, $primaryKeyValue) : array
    {
        $results = array();
        $sql = "SELECT * FROM $table WHERE ".$primaryKey." = :".$primaryKey;
        $statement = $this->prepare($sql);
        $statement->bindValue(':'.$primaryKey, $primaryKeyValue);

        $item = $statement->execute();
        $row = $item->fetchArray(SQLITE3_ASSOC);
        if ($row === false) {
            return $results;
        }

        return $row;
    }

    public function getLastInsertId() : int
    {
        return parent::lastInsertRowID();
    }

    /**
     * @param string $table
     * @param string $primaryKey
     * @param mixed  $primaryKeyValue
     *
     * @return bool
     */
    function deleteFromTable(string $table, string $primaryKey, $primaryKeyValue) : bool
    {
        $statement = $this->prepare("DELETE FROM ".$table." WHERE ".$primaryKey." = :primaryKeyValue");
        $statement->bindParam(':primaryKeyValue', $primaryKeyValue);
        /** @var SQLite3Result $result */
        $statement->execute();

        $statement = $this->prepare("SELECT * FROM $table WHERE $primaryKey = :primaryKeyValue");
        $statement->bindParam(':primaryKeyValue', $primaryKeyValue);
        $item = $statement->execute();
        $row = $item->fetchArray(SQLITE3_ASSOC);

        return $row === false;
    }

    /**
     * @param string $tableName
     * @param array  $fields    Associative array of fieldName => fieldValue
     *
     * @return mixed
     */
    public function insertInto(string $tableName, array $fields): int
    {
        $sql = "INSERT INTO ".$tableName;
        $sql .= " (".implode(",", array_keys($fields)).")";
        $sql .= " VALUES (";
        $sql .= implode(",", array_map(function($f){ return ":".$f;}, array_keys($fields)));
        $sql .= ")";
        $statement = $this->prepare($sql);
        foreach ($fields as $fieldName => $fieldValue) {
            $statement->bindValue(':'.$fieldName, $fieldValue);
        }
        $statement->execute();

        return $this->getLastInsertId();
    }

    /**
     * @param string $table
     * @param array  $where
     * @param array  $parameters
     */
    public function update(string $table, array $where, array $parameters)
    {
        $sql = "UPDATE ".$table;
        $sql .= " SET ";
        $sql .= implode(
            ",",
            array_map(
                function($k){
                    return $k.'=:'.$k;
                },
                array_keys($parameters)
            )
        );
        $sql .= " WHERE ";
        $sql .= implode(
            " AND ",
            array_map(
                function($k){
                    return $k."=:_".$k;
                },
                array_keys($where)
            )
        );
        $statement = $this->prepare($sql);
        foreach ($parameters as $key => $value) {
            $statement->bindValue(':'.$key, $value);
        }
        foreach ($where as $key => $value) {
            $statement->bindValue(':_'.$key, $value);
        }

        $statement->execute();
    }

    public function raw(string $sql, array $parameters) : array
    {
        $statement = $this->prepare($sql);
        foreach ($parameters as $key => $value) {
            $statement->bindValue(':'.$key, $value);
        }

        $results = [];
        $existingItems = $statement->execute();
        $row = $existingItems->fetchArray(SQLITE3_ASSOC);
        if ($row === false) {
            return $results;
        }

        $results[] = $row;
        while($row = $existingItems->fetchArray(SQLITE3_ASSOC)) {
            $results[] = $row;
        }
        return $results;
    }
}
