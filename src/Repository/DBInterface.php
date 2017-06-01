<?php
namespace Net\Repository;

interface DBInterface
{
    /**
     * Returns true if the table exists.
     *
     * @param $table
     *
     * @return bool
     */
    public function tableExists(string $table) : bool;

    /**
     * @param string $table
     * @param array  $where
     * @param int    $offset
     * @param int    $limit
     *
     * @return array
     */
    public function selectAllFrom(string $table, array $where, int $offset, int $limit) : array;

    /**
     * @param string $table
     * @param string $primaryKey
     * @param mixed  $primaryKeyValue
     *
     * @return array
     */
    public function selectOneFrom(string $table, string $primaryKey, $primaryKeyValue) : array;

    /**
     * @return int
     */
    public function getLastInsertId() : int;

    /**
     * @param string $table
     * @param string $primaryKey
     * @param mixed  $primaryKeyValue
     *
     * @return bool
     */
    public function deleteFromTable(string $table, string $primaryKey, $primaryKeyValue) : bool;

    /**
     * @param string $tableName
     * @param array  $fields
     *
     * @return mixed
     */
    public function insertInto(string $tableName, array $fields) : int;

    /**
     * @param string $table
     * @param array  $where
     * @param array  $parameters
     */
    public function update(string $table, array $where, array $parameters);
}