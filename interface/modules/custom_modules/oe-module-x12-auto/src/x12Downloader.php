<?php

namespace OpenEMR\Modules\X12AutoProcess;

class x12Downloader {
    private $partner_id;
    private $path;
    private $config;

    public function __construct($partner_id) {
        $this->partner_id = $partner_id;
        $this->getConfig();
        $this->download_path = $GLOBALS['OE_SITE_DIR'] . '/documents/edi/';
        
        if (!is_dir($this->download_path)) {
            mkdir($this->download_path, 0755, true);
        }
    }

    public function getConfig() {
        $sql = "select * from x12_partners where id = ?";
        $res = sqlQuery($sql, [$partner_id]);
        if (!$res) {
            throw new Exception("Partner not found."); //how we got this since we had a partner_id from x12_partners to start with...
        }
        $this->config = $res;
    }

}