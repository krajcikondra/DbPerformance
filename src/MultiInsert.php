<?php

namespace Helbrary\DbPerformance;

use Nette\Database\Context;

class MultiInsert extends Multi
{

	/** @var  array */
	private $buffer;

	/** @var  array */
	public $onFailure = array();

	public function __construct(Context $database, $table, $limit = Multi::DEFAULT_LIMIT)
	{
		parent::__construct($database, $table,  $limit);
	}

	/**
	 * Add record to buffer
	 * @param array $data
	 */
	public function add(array $data)
	{
		if (count($this->buffer) >= $this->limit) {
			$this->save();
		}
		$this->buffer[] = $data;
	}

	/**
	 * Function save records from buffer to database
	 */
	public function save()
	{
		try {
			$this->database->beginTransaction();
			$this->database->table($this->table)->insert($this->buffer);
			$this->database->commit();
			$this->deleteBuffer();
		} catch (\PDOException $e) {
			$this->database->rollBack();
			$this->saveIndividually();
		}
	}

	/**
	 * Function save records from buffer one by one record
	 */
	private function saveIndividually()
	{
		$this->database->beginTransaction();
		foreach ($this->buffer as $itemData) {
			try {
				$this->database->table($this->table)->insert($itemData);
			} catch (\PDOException $e) {
				$this->onFailure($e);
			}
		}
		$this->database->commit();
		$this->deleteBuffer();
	}

	/**
	 * Delete buffer
	 */
	private function deleteBuffer()
	{
		$this->buffer = array();
	}


}
