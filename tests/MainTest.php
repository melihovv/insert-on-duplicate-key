<?php

class MainTest extends BootstrapTest
{
    /**
     * @var UserMysqlTest
     */
    private $userMysql;

    /**
     * @var UserSqliteTest
     */
    private $userSqlite;

    public function setUp()
    {
        parent::setUp();

        $this->userMysql = new UserMysqlTest();
        $this->userSqlite = new UserSqliteTest();
    }

    public function getDataForInsert()
    {
        return [
            ['id' => 1, 'email' => 'user1@email.com', 'name' => 'User One'],
            ['id' => 2, 'email' => 'user2@email.com', 'name' => 'User Two'],
            ['id' => 3, 'email' => 'user3@email.com', 'name' => 'User Three'],
        ];
    }

    public function testGetTableName()
    {
        $this->assertEquals('test_user_table', UserMysqlTest::getTableName());
    }

    public function testGetTablePrefix()
    {
        $this->assertEquals('prefix_', UserMysqlTest::getTablePrefix());
    }

    public function testGetPrimaryKey()
    {
        $this->assertEquals('uuid', UserMysqlTest::getPrimaryKey());
    }

    public function testGetDriverNameMysql()
    {
        $this->assertEquals('mysql', UserMysqlTest::getDriverName());
    }

    public function testGetDriverNameSqlite()
    {
        $this->assertEquals('sqlite', UserSqliteTest::getDriverName());
    }

    // test private functions

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetColumnListEmptyDataException()
    {
        $data = [];

        $this->invokeMethod($this->userMysql, 'getColumnList', [$data]);
    }

    public function testGetColumnList()
    {
        $data = $this->getDataForInsert();

        $expected = '`id`,`email`,`name`';

        $result = $this->invokeMethod($this->userMysql, 'getColumnList', [$data[0]]);

        $this->assertEquals($expected, $result);
    }

    public function testBuildValuesList()
    {
        $data = $this->getDataForInsert();

        $expected = '`id` = VALUES(`id`), `email` = VALUES(`email`), `name` = VALUES(`name`)';

        $result = $this->invokeMethod($this->userMysql, 'buildValuesList', [$data[0]]);

        $this->assertEquals($expected, $result);
    }

    public function testInLineArraySimple()
    {
        $data = [
            ['id' => 1, 'email' => 'user1@email.com', 'name' => 'User One']
        ];

        $expected = [1, 'user1@email.com', 'User One'];

        $result = $this->invokeMethod($this->userMysql, 'inLineArray', [$data]);

        $this->assertEquals($expected, $result);
    }

    public function testBuildQuestionMarks()
    {
        $data = $this->getDataForInsert();

        $expected = '(?,?,?), (?,?,?), (?,?,?)';

        $result = $this->invokeMethod($this->userMysql, 'buildQuestionMarks', [$data]);

        $this->assertEquals($expected, $result);
    }

    public function testInLineArrayThreeRows()
    {
        $data = $this->getDataForInsert();

        $expected = [
            1, 'user1@email.com', 'User One',
            2, 'user2@email.com', 'User Two',
            3, 'user3@email.com', 'User Three',
        ];

        $result = $this->invokeMethod($this->userMysql, 'inLineArray', [$data]);

        $this->assertEquals($expected, $result);
    }

    public function testBuildInsertOnDuplicateSqlSimple()
    {
        $data = [
            ['id' => 1, 'email' => 'user1@email.com', 'name' => 'User One']
        ];

        $expected = 'INSERT INTO `prefix_test_user_table`(`id`,`email`,`name`) VALUES
(?,?,?)
ON DUPLICATE KEY UPDATE `id` = VALUES(`id`), `email` = VALUES(`email`), `name` = VALUES(`name`)';

        $result = $this->invokeMethod($this->userMysql, 'buildInsertOnDuplicateSql', [$data]);

        $this->assertEquals($expected, $result);
    }

    public function testBuildInsertOnDuplicateSqlMultiple()
    {
        $data = $this->getDataForInsert();

        $expected = 'INSERT INTO `prefix_test_user_table`(`id`,`email`,`name`) VALUES
(?,?,?), (?,?,?), (?,?,?)
ON DUPLICATE KEY UPDATE `id` = VALUES(`id`), `email` = VALUES(`email`), `name` = VALUES(`name`)';

        $result = $this->invokeMethod($this->userMysql, 'buildInsertOnDuplicateSql', [$data]);

        $this->assertEquals($expected, $result);
    }

    public function testBuildInsertOnDuplicateSqlMultipleWithUpdateColumn()
    {
        $data = $this->getDataForInsert();

        $expected = 'INSERT INTO `prefix_test_user_table`(`id`,`email`,`name`) VALUES
(?,?,?), (?,?,?), (?,?,?)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`)';

        $result = $this->invokeMethod($this->userMysql, 'buildInsertOnDuplicateSql', [$data, ['name']]);

        $this->assertEquals($expected, $result);
    }

    public function testBuildInsertIgnoreSqlSimpleMysql()
    {
        $data = [
            ['id' => 1, 'email' => 'user1@email.com', 'name' => 'User One']
        ];

        $expected = 'INSERT IGNORE INTO `prefix_test_user_table`(`id`,`email`,`name`) VALUES
(?,?,?)';

        $result = $this->invokeMethod($this->userMysql, 'buildInsertIgnoreSql', [$data]);

        $this->assertEquals($expected, $result);
    }

    public function testBuildInsertIgnoreSqlSimpleSqlite()
    {
        $data = [
            ['id' => 1, 'email' => 'user1@email.com', 'name' => 'User One']
        ];

        $expected = 'INSERT OR IGNORE INTO `prefix_test_user_table`(`id`,`email`,`name`) VALUES
(?,?,?)';

        $result = $this->invokeMethod($this->userSqlite, 'buildInsertIgnoreSql', [$data]);

        $this->assertEquals($expected, $result);
    }

    public function testBuildInsertIgnoreSqlMulitpleMysql()
    {
        $data = $this->getDataForInsert();

        $expected = 'INSERT IGNORE INTO `prefix_test_user_table`(`id`,`email`,`name`) VALUES
(?,?,?), (?,?,?), (?,?,?)';

        $result = $this->invokeMethod($this->userMysql, 'buildInsertIgnoreSql', [$data]);

        $this->assertEquals($expected, $result);
    }

    public function testBuildInsertIgnoreSqlMulitpleSqlite()
    {
        $data = $this->getDataForInsert();

        $expected = 'INSERT OR IGNORE INTO `prefix_test_user_table`(`id`,`email`,`name`) VALUES
(?,?,?), (?,?,?), (?,?,?)';

        $result = $this->invokeMethod($this->userSqlite, 'buildInsertIgnoreSql', [$data]);

        $this->assertEquals($expected, $result);
    }

    public function testBuildReplaceSqlSimple()
    {
        $data = [
            ['id' => 1, 'email' => 'user1@email.com', 'name' => 'User One']
        ];

        $expected = 'REPLACE INTO `prefix_test_user_table`(`id`,`email`,`name`) VALUES
(?,?,?)';

        $result = $this->invokeMethod($this->userMysql, 'buildReplaceSql', [$data]);

        $this->assertEquals($expected, $result);
    }

    public function testBuildReplaceSqlMulitple()
    {
        $data = $this->getDataForInsert();

        $expected = 'REPLACE INTO `prefix_test_user_table`(`id`,`email`,`name`) VALUES
(?,?,?), (?,?,?), (?,?,?)';

        $result = $this->invokeMethod($this->userMysql, 'buildReplaceSql', [$data]);

        $this->assertEquals($expected, $result);
    }

    public function testInLineArray()
    {
        $rows = [
            ['id' => 1, 'email' => '1@email.com'],
            ['id' => 2, 'email' => '2@email.com'],
        ];

        $result = $this->invokeMethod($this->userMysql, 'inLineArray', [$rows]);

        $expected = [
            1,
            '1@email.com',
            2,
            '2@email.com',
        ];

        $this->assertEquals($expected, $result);
    }
}
