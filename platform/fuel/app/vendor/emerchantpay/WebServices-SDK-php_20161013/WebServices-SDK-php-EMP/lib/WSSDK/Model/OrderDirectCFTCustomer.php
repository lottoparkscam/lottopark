<?php
/**
  * @filesource
  */
namespace WSSDK\Model;

use \WSSDK\Model as Model;

/***************
* Model
***************/
class OrderDirectCFTCustomer extends Model\Customer {

	protected $required = ['customer_email'];

}
