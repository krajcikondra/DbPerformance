<?php

require __DIR__ . '/../vendor/autoload.php';

/**
 * Class MultiUpdateTest
 * @author Ondrej Krajcik <o.krajcik@seznam.cz>
 * @package Helbrary\NodeItemTree
 */
class MultiUpdateTest extends \Tester\TestCase
{
	const DB_NAME = 'testdb';
	const TABLE_NAME = 'testUpdate';

	const LIMIT = 40;

	/** @var  \Helbrary\DbPerformance\MultiUpdate */
	private $multiUpdate;

	/** @var  \Nette\Database\Context */
	private $context;

	public function setUp()
	{
		$dsn = "mysql:host=127.0.0.1;dbname=" . self::DB_NAME;
		$connection = new \Nette\Database\Connection($dsn, 'root');
		$nullStorage = new \Nette\Caching\Storages\DevNullStorage();
		$structure = new \Nette\Database\Structure($connection, $nullStorage);
		$this->context = $context = new \Nette\Database\Context($connection, $structure);
		$this->multiUpdate = new \Helbrary\DbPerformance\MultiUpdate($context, self::TABLE_NAME, 'type', self::LIMIT);
		$this->createTable();
	}



	public function testMultiUpdate() {
		$this->insertData();
		\Tester\Assert::equal(40, $this->getCount('first_type'));
		\Tester\Assert::equal(40, $this->getCount('second_type'));
		\Tester\Assert::equal(40, $this->getCount('third_type'));
		\Tester\Assert::equal(40, $this->getCount('fourth_type'));
		\Tester\Assert::equal(40, $this->getCount('fifth_type'));

		for ($i = 0; $i < 120; $i++) {
			$this->multiUpdate->update($i, 'fifth_type');
		}
		$this->multiUpdate->save();

		\Tester\Assert::equal(0, $this->getCount('first_type'));
		\Tester\Assert::equal(0, $this->getCount('second_type'));
		\Tester\Assert::equal(0, $this->getCount('third_type'));
		\Tester\Assert::equal(40, $this->getCount('fourth_type'));
		\Tester\Assert::equal(160, $this->getCount('fifth_type'));
	}

	/**
	 * Create table in database
	 */
	private function createTable() {
		$tableName = self::DB_NAME . '.' . self::TABLE_NAME;
		$this->context->query('DROP TABLE IF EXISTS ' . $tableName . ';');
		$this->context->query('CREATE TABLE ' . $tableName . ' ('
			. '`id` INT,'
			. ' `type` VARCHAR(150), `value` VARCHAR(150);'
		);
	}

	private function insertData() {
		$type = 'first_type';
		for ($i = 0; $i < 200; $i++) {

			if ($i == 40) $type = 'second_type';
			if ($i == 80) $type = 'third_type';
			if ($i == 120) $type = 'fourth_type';
			if ($i == 160) $type = 'fifth_type';

			$value = $type . '_value';

			$this->context->table(self::TABLE_NAME)->insert(array(
				'id' => $i,
				'type' => $type,
				'value' => $value,
			));
		}
	}

	/**
	 * Return count of rows by type
	 * @param string $type
	 * @return int
	 */
	private function getCount($type) {
		return $this->context->table(self::TABLE_NAME)
			->where('type', $type)
			->count();
	}



}

$multiUpdateTest = new MultiUpdateTest();
$multiUpdateTest->run();