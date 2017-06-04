<?php
namespace Net\Repository;

class MysqlDB extends \PDO implements DBInterface
{
    /**
     * Returns true if the table exists.
     *
     * @param string $table
     *
     * @return bool
     */
    public function tableExists(string $table): bool
    {
        $sql = <<<SQL
SELECT count(*) as `exists` FROM information_schema.TABLES WHERE SCHEMA = 'nntp_reader' AND TABLE_NAME = :table
SQL;

        $statement = $this->prepare($sql);
        $statement->bindValue(':table', $table);
        $statement->execute();
        $results = $statement->fetchAll(static::FETCH_ASSOC);
        return count($results) > 0 && $results[0]['exists'] = 1;
    }

    /**
     * @param string $table
     * @param array  $where
     * @param int    $offset
     * @param int    $limit
     *
     * @return array
     */
    public function selectAllFrom(string $table, array $where, int $offset, int $limit): array
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
        if (!$statement->execute()) {
            return $results;
        }

        return $statement->fetchAll(static::FETCH_ASSOC);
    }

    /**
     * @param string $table
     * @param string $primaryKey
     * @param mixed  $primaryKeyValue
     *
     * @return array
     */
    public function selectOneFrom(string $table, string $primaryKey, $primaryKeyValue): array
    {
        $results = array();
        $sql = "SELECT * FROM $table WHERE ".$primaryKey." = :".$primaryKey." LIMIT 1";
        $statement = $this->prepare($sql);
        $statement->bindValue(':'.$primaryKey, $primaryKeyValue);

        if (!$statement->execute()) {
            return $results;
        }

        return $statement->fetchAll(static::FETCH_ASSOC)[0];
    }

    /**
     * @return int
     */
    public function getLastInsertId(): int
    {
        return (int) $this->lastInsertId();
    }

    /**
     * @param string $table
     * @param string $primaryKey
     * @param mixed  $primaryKeyValue
     *
     * @return bool
     */
    public function deleteFromTable(string $table, string $primaryKey, $primaryKeyValue): bool
    {
        $sql = "DELETE FROM ".$table." WHERE ".$primaryKey." = :".$primaryKey;
        $statement = $this->prepare($sql);
        $statement->bindValue(':'.$primaryKey, $primaryKeyValue);
        return $statement->execute();
    }

    /**
     * @param string $tableName
     * @param array  $fields
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

    /**
     * @param string $sql
     * @param array  $parameters
     *
     * @return array
     */
    public function raw(string $sql, array $parameters): array
    {
        $statement = $this->prepare($sql);
        foreach ($parameters as $key => $value) {
            $statement->bindValue(':'.$key, $value);
        }

        $results = [];
        if (!$statement->execute()) {
            return $results;
        }
        return $statement->fetchAll(static::FETCH_ASSOC);
    }
}