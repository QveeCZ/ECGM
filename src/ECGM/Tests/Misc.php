<?php
namespace ECGM\Tests;


use ECGM\Exceptions\InvalidValueException;
use ECGM\Model\BaseArray;
use ECGM\Model\Customer;
use ECGM\Model\CustomerGroup;
use PHPUnit\Framework\TestCase;

class Misc extends TestCase
{

    public function runAll(){

    }

    public function testBaseArray(){

        //Validate bad required class
        $this->expectException(InvalidValueException::class);
        new BaseArray(null, "afsasaff");

        //Validate bad insert value
        $this->expectException(InvalidValueException::class);
        $arrayTest = new BaseArray(null, Customer::class);
        $arrayTest->add(new CustomerGroup(1));
    }
}