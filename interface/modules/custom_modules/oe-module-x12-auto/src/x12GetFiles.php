<?php



namespace OpenEMR\Modules\X12AutoProcess;

//use OpenEMR\Modules\x12Downloader;

#use what we need for ssh, sftp, and getter
$_SESSION['site_id'] = 'default';
$ignoreAuth = true;
$_SERVER['HTTP_HOST'] = 'localhost';

require_once(__DIR__ . '/../../../../globals.php');

use phpseclib3\Net\SFTP;
use OpenEMR\Common\Crypto\CryptoGen;

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
        $host = $row['x12_sftp_host'];
        $port = $row['x12_sftp_port'] ? $row['x12_sftp_port'] : 22;
        $user = $row['x12_sftp_login'];
        $password_enc = $row['x12_sftp_pass'];
        $remote_dir = $row['x12_sftp_remote_dir'];
        $cryptoGen = new CryptoGen();
        $password = $cryptoGen->decryptStandard($password_enc);

        $sftp = new SFTP($host, $port);
        if (!$sftp->login($user, $password)) {
            die("unable to connect to remote sftp server");
        }
        $files = $sftp->nlist($remote_dir, false); // we don't want to recurse here
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') continue;
            $fp = rtrim($remote_dir, "/") . "/" . $file;
            if($sftp->is_dir($fp)) continue;
            print($file . "\n");
        }
    }

    public function downloadFiles($exclude_test = true) {
        $sql = "select * from x12_partners ";
        if ($exclude_test) {
            $sql = $sql . "where x12_isa15 != 'T'";
        }
        $res = sqlStatement($sql);
        $x = sqlNumRows($res);
        while ($x > 0) {
            $r = $res->FetchRow();
            $this->getFilesFromPartner($r);
            $x = $x - 1;
        }
    }
}