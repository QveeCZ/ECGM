<?php

namespace ECGM\Tests;


use ECGM\Exceptions\InvalidValueException;
use ECGM\Model\BaseArray;
use ECGM\Model\Customer;
use ECGM\Model\CustomerGroup;
use ECGM\Model\CustomerParameter;
use PHPUnit\Framework\TestCase;

class MiscTests extends TestCase
{
    public function testInvalidValueExceptions()
    {
        //Validate bad required class
        $this->expectException(InvalidValueException::class);
        new BaseArray(null, "SomeNonexistentClassName");

        //Validate bad insert value
        $this->expectException(InvalidValueException::class);
        $arrayTest = new BaseArray(null, Customer::class);
        $arrayTest->add(new CustomerGroup(1));

        //Validate non numeric CustomerParameter value
        $this->expectException(InvalidValueException::class);
        new CustomerParameter(1, "fasfaf", new Customer(12, new CustomerGroup(1)));
    }
}