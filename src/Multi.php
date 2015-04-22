<?php

namespace DbPerformance;

use Nette\Database\Context;
use Nette\Object;

abstract class Multi extends Object
{

	const DEFAULT_LIMIT = 100;

	/** @var  Context */
	protected $database;

	/** @var  string */
	protected $table;

	/** @var  int */
	protected $limit;

	public function __construct(Context $database, $table, $limit = self::DEFAULT_LIMIT)
	{
		$this->database = $database;
		$this->table = $table;
		$this->limit = $limit;
	}

	/**
	 * Save data in buffer and change target table
	 * @param string $table
	 */
	public function setTable($table)
	{
		$this->save();
		$this->table = $table;
	}

	/**
	 * Function save records from buffer to database
	 */
	abstract public function save();

}
