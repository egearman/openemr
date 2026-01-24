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

const IN_PROCESS_DIR = __DIR__ . "/../../../../../sites/default/x12_in_process";
const PROCESSED_DIR = __DIR__ . "/../../../../../sites/default/x12_processed";

class x12GetFiles {
    public function __construct() {
    }

    public function x12_processed_files_error_log($id, $file, ?string $msg = null) {
        //insert into the x12_file_process_log
        $sql = 'insert into x12_processed_files_error_log (x,y,z) values(?,?,?)';
    }

    public function updateX12FileDownloadLog($id, $file, $mtime, ?string $status = 'Retrieved') {
        $now = gmdate('Y-m-d H:i:s');
        $dt_obj = new \DateTime();
        $dt_obj->setTimestamp($mtime);
        $db_mtime = $dt_obj->format('Y-m-d H:i:s');
        $sql = 'select * from x12_downloaded_files where x12_partner_id = ? and trim($file_name) = ?';
        $res = sqlQuery($sql, [$id, $file]);
        if ($res) {
            $sql = 'update x12_downloaded_files set file_process_status = ?, retrieved_date_time = ? where x12_partner_id = ? and trim($file_name) = ?';
            $res = sqlQuery($sql, [$status, $mtime, $id, $file]);
        } else {
            $sql = 'insert into x12_downloaded_files columns(file_processed_status, retrieved_date_time, x12_partner_id, retrieved_date_time) values (?, ?, ?, ?)';
            $res = sqlQuery($sql, [$status, $mtime, $id, $file]);
        }
    }

    public function updateX12FileProcess($id, $mtime) {
        $sql = "select * from x12_file_process where id = ?";
        $res = sqlQuery($sql, [$id]);
        $now = gmdate('Y-m-d H:i:s');

        $dt_obj = new \DateTime();
        $dt_obj->setTimestamp($mtime);
        $db_mtime = $dt_obj->format('Y-m-d H:i:s');
        if ($res) {
            $sql2 = "update x12_file_process set last_datetime_file_modified = '$db_mtime', last_datetime_run = '$now' where id = '$id'";
            echo $sql2 . "\n";
            $res2 = sqlStatement($sql2);
            if ($res2) { echo "success\n"; }
        } else {
            $sql2 = "insert into x12_file_process values ('$id', '$now', '$db_mtime')";
            echo $sql2 . "\n";
            $res2 = sqlStatement($sql2);
            if ($res2) { echo "success 2 \n"; }
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
        $id = $row['id'];

        $local_path = IN_PROCESS_DIR . "/" . "x12_partner_" . strval($id);

        $sql = "select * from x12_file_process where id = ?";
        $res = sqlQuery($sql, [$id]);
        if (!$res) {
            $last_mod_dt = 0;
        } else {
            $last_mod_dt = $res[0]['last_datetime_file_modified'];
        }
        if (!is_dir($local_path)) {
            mkdir($local_path, 0777, true);
        }

        $sftp = new SFTP($host, $port);
        if (!$sftp->login($user, $password)) {
            //replace with actual logging
            echo "unable to connect to remote sftp server {$name} at {$host}:{$port}";
        }
        $files = $sftp->nlist($remote_dir, false); // we don't want to recurse here
        $new_mod_dt = 0;
        clearstatcache();
        foreach ($files as $file) {
            $retrieved_file = false;
            if ($file == '.' || $file == '..') {continue;}

            $fp = rtrim($remote_dir, "/") . "/" . $file;
            if($sftp->is_dir($fp)) {continue;}

            $stat = $sftp->stat($fp);
            $mtime = $stat['mtime'];
            if ($mtime > $last_mod_dt) {
                file_put_contents($local_path . "/" . $file, $sftp->get($fp));
                $retrieved_file = true;
                if ($mtime > $new_mod_dt) {
                    $new_mod_dt = $mtime;
                }
            }
            if ($retrieved_file) {
                $this->updateX12FileDownloadLog($id, $file, $mtime, 'Retrieved');
            }
        }
        $this->updateX12FileProcess($id, $new_mod_dt);
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