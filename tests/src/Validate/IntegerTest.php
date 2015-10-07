<?php

namespace Phramework\Validate;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015-10-05 at 20:04:03.
 */
class IntegerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Integer
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new Integer(-1000,1000,true);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {

    }

    public function validateSuccessProvider()
    {
        //input, expected
        return [
            ['100', 100],
            [124, 124],
            [0, 0],
            [-10, -10],
            [-99, -99]
        ];
    }

    public function validateFailureProvider()
    {
        //input
        return [
            ['-0x'],
            ['abc'],
            ['+xyz'],
            ['++30'],
            [-1000], //should fail becaus of exclusiveMinimum
            [-10000000],
            [10000000],
            ['-1000000000']
        ];
    }

    /**
     * @covers Phramework\Validate\Integer::createFromJSON
     * @dataProvider validateSuccessProvider
     */
    public function testCreateFromJSON($input, $expected)
    {
        $json = '{
            "type": "integer",
            "minimum" : -1000,
            "maximum" : 1000
        }';

        $object = Integer::createFromJSON($json);

        $this->validateSuccess($object, $input, $expected);
    }

    /**
     * Helper method
     */
    private function validateSuccess(Integer $object, $input, $expected)
    {
        $return = $object->validate($input);

        $this->assertEquals(true, $return->status);
        $this->assertInternalType('integer', $return->value);
        $this->assertEquals($expected, $return->value);
    }

    /**
     * @covers Phramework\Validate\Integer::validate
     * @dataProvider validateSuccessProvider
     */
    public function testValidateSuccess($input, $expected)
    {
        $this->validateSuccess($this->object, $input, $expected);
    }


    /**
     * @covers Phramework\Validate\Integer::validate
     * @dataProvider validateFailureProvider
     */
    public function testValidateFailure($input)
    {
        $return = $this->object->validate($input);

        $this->assertEquals(false, $return->status);

        $this->markTestIncomplete(
                'Test Exclusive and multipleOf'
        );
    }

    /**
     * @covers Phramework\Validate\Integer::getType
     */
    public function testGetType()
    {
        $this->assertEquals('integer', $this->object->getType());
    }


}