<?php

require __DIR__ . '/../vendor/autoload.php';

/**
 * Class MultiInsertTest
 * @author Ondrej Krajcik <o.krajcik@seznam.cz>
 * @package Helbrary\DbPerformance
 */
class MultiInsertTest extends \Tester\TestCase
{
	const DB_NAME = 'testdb';
	const TABLE_NAME = 'test';

	const LIMIT = 100;

	/** @var  \Helbrary\DbPerformance\MultiInsert */
	private $multiInsert;

	/** @var  \Nette\Database\Context */
	private $context;

	public function setUp()
	{
		$dsn = "mysql:host=127.0.0.1;dbname=" . self::DB_NAME;
		$connection = new \Nette\Database\Connection($dsn, 'root');
		$nullStorage = new \Nette\Caching\Storages\DevNullStorage();
		$structure = new \Nette\Database\Structure($connection, $nullStorage);
		$this->context = $context = new \Nette\Database\Context($connection, $structure);
		$this->multiInsert = new \Helbrary\DbPerformance\MultiInsert($context, self::TABLE_NAME, self::LIMIT);
	}


	public function testMultiInsert() {
		$this->createTable();
		\Tester\Assert::equal(0, $this->getCount());
		for ($i = 0; $i < 15000; $i++) {
			$this->multiInsert->add(array(
				'id' => $i,
			));

			if ($i !== 0 && ($i % self::LIMIT) === 0) {
				\Tester\Assert::equal($i, $this->getCount());
			}
		}
		$this->multiInsert->save();
		\Tester\Assert::equal(15000, $this->getCount());
	}

	/**
	 * Create table in database
	 */
	private function createTable() {
		$tableName = self::DB_NAME . '.' . self::TABLE_NAME;
		$this->context->query('DROP TABLE IF EXISTS ' . $tableName . ';');
		$this->context->query('CREATE TABLE ' . $tableName . ' (id INT);');
	}

	/**
	 * Return count of inserted rows
	 * @return int
	 */
	private function getCount() {
		return $this->context->table(self::TABLE_NAME)->count();
	}
}

$multiInsertTest = new MultiInsertTest();
$multiInsertTest->run();