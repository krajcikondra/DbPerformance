<?php

namespace DbPerformance;

use Nette\Database\Context;

class MultiUpdate extends Multi
{

	/** @var  array */
	private $buffer;

	/** @var  array */
	private $args;

	/** @var  string */
	private $column;

	/** @var  string */
	private $columnId = 'id';

	/** @var  array */
	public $onFailure = array();

	public function __construct(Context $database, $table, $column, $limit = MULTI::DEFAULT_LIMIT)
	{
		parent::__construct($database, $table,  $limit);
		$this->column = $column;
	}

	/**
	 * Set name of primary key column
	 * @param string $name
	 */
	public function setColumnId($name)
	{
		$this->columnId = $name;
	}

	/**
	 * Add updated item to buffer
	 * @param int $id
	 * @param string $value
	 */
	public function add($id, $value)
	{
		if (count($this->buffer) >= $this->limit) {
			$this->save();
		}
		$this->buffer[$id] = $value;
	}

	/**
	 * Function save records from buffer to database
	 */
	public function save()
	{
		try {
			$this->database->beginTransaction();
			$this->database->queryArgs($this->buildMultiUpdate(), $this->getArgs());
			$this->database->commit();
			$this->deleteBuffer();
		} catch (\PDOException $e) {
			$this->database->rollBack();
			$this->saveIndividually();
		}
	}

	/**
	 * Function build multi-update from buffer
	 * @return string
	 */
	private function buildMultiUpdate()
	{
		$query = "UPDATE " . $this->table ." SET " . $this->column . " = CASE ". $this->columnId . " ";
		foreach ($this->buffer as $id => $value) {
			$query .= "WHEN ? THEN ? ";
			$this->addArg($id);
			$this->addArg($value);
		}
		$query .= "END ";
		$query .= "WHERE " . $this->columnId . " IN (" .  implode(",", array_keys($this->buffer)). ")";
		return $query;
	}

	/**
	 * Function clean out buffer one record by one
	 */
	private function saveIndividually()
	{
		$this->database->beginTransaction();
		foreach ($this->buffer as $id => $value) {
			try {
				$this->database->table($this->table)->where($this->columnId, $id)->update(array(
					$this->column => $value,
				));
			} catch (\PDOException $e) {
				$this->onFailure($e);
			}
		}
		$this->database->commit();
		$this->deleteBuffer();
	}

	/**
	 * Return all arguments of update
	 * @return array
	 */
	private function getArgs()
	{
		return  $this->args;
	}

	/**
	 * Save argument of update
	 * @param mixed $arg
	 */
	private function addArg($arg)
	{
		$this->args[] = $arg;
	}

	/**
	 * Delete buffer
	 */
	private function deleteBuffer()
	{
		$this->buffer = array();
		$this->args = array();
	}
}
