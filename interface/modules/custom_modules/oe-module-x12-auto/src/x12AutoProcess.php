<?php

namespace OpenEMR\Modules\X12AutoProcess;


const IN_PROCESS_DIR = __DIR__ . "/../../../../../sites/default/x12_in_process";
const PROCESSED_DIR = __DIR__ . "/../../../../../sites/default/x12_processed";

use OpenEMR\Modules\X12AutoProcess\x12ApFunctions;
use OpenEMR\Billing\ParseERA;

require_once("library/edihistory/edih_csv_parse.php");
require_once("library/edihistory/codes/edih_271_code_class.php");

class x12AutoProcess {
    public function __construct() {
    }

    public function processFile($row) { //operating with the premise this is a row from x12_downloaded_files
        //read file
        $local_file = IN_PROCESS_DIR . "/" . $row['file_name'];
        $x12_file_type = $row['x12_file_type'];
        switch (trim($x12_file_type)) {
            case '835':
                $ret = ParseERA::ParseERAForCheck($local_file) . ParseERA::ParseERA($local_file, 'era_callback');
                if ($ret) {
                    // $ret is blank if no error, so log what it did return.
                }
                break;
            case '277':
                //find or create parser
                break;
            case '271':
                $data = file_get_contents($local_file);
                $ret = edih_271_csv_data($data);
                break;
            default:
                break;
        }
    }

    public function processDownloadedFiles($id = 0) {
        if ($id === 0) {
            //error, no id supplied
        }
        $sql = "select * from x12_downloaded_files where x12_partner_id = ? and file_process_status = 'Retrieved'";
        $res = sqlStatement($sql, [$id]);
        $x = sqlNumRows($res);
        while ($x > 0) {
            $r = $res->FetchRow();
            $this->processFile($r);
            $x = $x - 1;
        }
    }

    public function processFiles($exclude_test = true) {
        $sql = "select * from x12_partners";
        if ($exclude_test) {
            $sql = $sql . " where x12_isa15 != 'T'";
        }
        $res = sqlStatement($sql);
        $x = sqlNumRows($res);
        while ($x > 0) {
            $r = $res->FetchRow();
            $this->processDownloadedFiles($r['id']);
            $x = $x - 1;
        }
    }
}