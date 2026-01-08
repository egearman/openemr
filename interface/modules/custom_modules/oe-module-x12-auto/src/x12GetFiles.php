<?php



namespace OpenEMR\Modules\X12AutoProcess;

//use OpenEMR\Modules\x12Downloader;

#use what we need for ssh, sftp, and getter
$_SESSION['site_id'] = 'default';
$ignoreAuth = true;
$_SERVER['HTTP_HOST'] = 'localhost';
require_once(__DIR__ . '/../../../../globals.php');

class x12GetFiles {
    public function __construct() {
    }

    public function updateX12FileProcess($mtime) {
        $sql = "select * from x12_file_process where id = ?";
        $res = sqlQuery($sql, [$this->partner_id]);
        if ($res) {
            $sql2 = "update x12_file_process set last_dl_file_dt = ? and last_dl_dt = ? where id = ?";
            $res2 = sqlStatement($sql2, [$mtime, now(), $id]);
        } else {
            $sql2 = "insert into x12_file_process values (?, ?, ?)";
            $res2 = sqlStatement($sql2, [$id, now(), $mtime]);
        }
    }

    public function getFilesFromPartner($row) {
        // we have partner information here
        //set up connection, download files and close connection
    }

    public function downloadFiles($exclude_test = true) {
        $sql = "select id from x12_partners ";
        if ($exclude_test) {
            $sql = $sql . "where x12_isa15 != 'T'";
        }
        $res = sqlStatement($sql);
        $x = sqlNumRows($res);
        print(strval($x) . "\n\n");
        while ($x > 0) {
            $r = $res->FetchRow();
            $this->getFilesFromPartner($r);
            $x = $x - 1;
        }
    }
}