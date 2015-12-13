# DbPerformance

This library is useful for bulk import to database.

Install in composer.json
```sh
require: "helbrary/db-performance": "dev-master"
```
Multi-insert
------------

Create instance
```sh
$multiInsert = new MultiInsert($this->context, 'table_name');
$multiInsert->onFailure[] = function(\Exception $e) {
 ....
};
```

Third parameter in constructor is buffer limit (optionaly). Default value is 100. 
It means that if buffer contains 100 records and we wont add next record, it's build multi insert and send to database.
Buffer is empty and to buffer is add new record.

Add record to buffer (function don't insert record to database yet)
```sh
$multiInsert->add(array(
  "firstname" => "John",
  "lastname" => "Smith",
));
```

If we add to buffer all records, it's necessary insert records to database from buffer.

```sh
$multiInsert->save();
```

Multi-insert Failed
------------

In case throw exception in database, for example constraint violations, is multi-insert divide into single inserts. 
These single inserts are send to database individually (one record = one insert).
When single insert throw exception, it's invoke callback $onFailure.

OnFailure callback
------------
```sh
$multiInsert->onFailure[] = function(\Exception $e) {
	Debugger::log($e);
};
```

Multi-update
------------

Create instance
```sh
		$multiUpdate = new MultiUpdate($context, 'page', 'title', 50);
		$multiUpdate->onFailure[] = function(\Exception $e) {
			Debugger::log($e);
		};
```

Add record to buffer (function don't update record in database yet)
```sh
	$multiUpdate->add(1, 'updated column text');
```

If we add to buffer all records to update, it's necessary send update records to database from buffer
```sh
	$multiUpdate->save();
```

Recommended buffer limit
------------

Buffer limit defines how many SQL commands will be joined to one multi-command. Default value of buffer limit is 100.
It's possible to change this value in constructor by variable $limit.
If SQL commands will throw some type of Exception often, it's better set buffer smaller.
If SQL commands will throw some type of Exception rarely it's better set buffer higher.

Testing - Insert 100 000 records (no SQL INSERT throw Exceptions)
------------
1) One by one record: **16 min 55 sec**
```sh
for ($i = 0; $i < 100000; $i++) {
	$this->database->table('tableName')->insert(array(
	"title" => "title" . $i,
	"description" => "description" . $i,
	));
}
```

2) One by one record in transaction: **4 min 50 sec**

```sh
$this->database->beginTransaction();
for ($i = 0; $i < 100000; $i++) {
	$this->database->table('tableName')->insert(array(
	"title" => "title" . $i,
	"description" => "description" . $i,
	));
}
$this->database->commit();
```

3) Using Helbrary\DbPerformance\MultiInsert with (default) buffer size 100: **0 min 20 sec**
```sh
$multiInsert = new MultiInsert($this->context, 'tableName', 100);
for ($i = 0; $i < 100000; $i++) {
	$multiInsert->add(array(
		"title" => "title" . $i,
		"description" => "description" . $i,
	));
}
$multiInsert->save();
```

4) Using Helbrary\DbPerformance\MultiInsert with buffer size 500: **0 min 14 sec**

5) Using Helbrary\DbPerformance\MultiInsert with buffer size 1000: **0 min 12 sec**

6) Using Helbrary\DbPerformance\MultiInsert with buffer size 10000: **0 min 11 sec**
