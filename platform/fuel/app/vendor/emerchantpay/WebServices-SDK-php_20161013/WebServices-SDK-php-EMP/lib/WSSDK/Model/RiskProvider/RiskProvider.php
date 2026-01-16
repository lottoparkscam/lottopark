<?php

namespace WSSDK\Model;

require_once __DIR__."/Infocapture.php";
require_once __DIR__."/ThreatMetrix.php";
require_once __DIR__."/IovationRM.php";

use \WSSDK\Model as Model;

abstract class RiskProvider extends Model\BaseModel {}

