<?php

/*
 *
 *
 */

namespace OpenEMR\Modules\X12AutoProcess;

require_once(__DIR__ . "/../../../../../vendor/autoload.php");

use OpenEMR\Modules\X12AutoProcess\x12GetFiles;
use OpenEMR\Modules\X12AutoProcess\x12AutoProcess;

class x12Cli {
    public $x12GetFiles;
    public $x12AutoProcess;

    public function __construct() {
        $this->x12GetFiles = new x12GetFiles(null);
        $this->x12AutoProcess = new x12AutoProcess(null);
    }
    
}

$x12Cli = new x12Cli();
$x12Cli->x12GetFiles->downloadFiles($exclude_test = false);
$x12Cli->x12AutoProcess->processFiles($exclude_test = false);
