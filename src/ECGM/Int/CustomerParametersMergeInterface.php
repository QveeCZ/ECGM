<?php
/**
 * Created by PhpStorm.
 * User: qvee
 * Date: 4.3.18
 * Time: 13:22
 */

namespace ECGM\Int;


use ECGM\Model\BaseArray;

interface CustomerParametersMergeInterface
{

    /**
     * @param BaseArray $customerHistory
     * @return BaseArray
     */
    public function mergeCustomerHistory(BaseArray $customerHistory);
}