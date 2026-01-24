<?php

namespace OpenEMR\Modules\X12AutoProcess;

function getX12LineValue(?string $line = null, $position = 0, $delim = '*') {
    // not that x12 appears to support anything BUT '*' as a segment seperator...
    if (!is_null($line)) {
        $data = explode($delim, $line);
        return $data[$position];
    }
    return "";
}

function logNewX12APError($iid, $lno, $msg = "") {
    try {
        $sql = "insert into x12_processed_files_error_log (internal_file_id, error_line_number, error_message) values(?, ?, ?)";
        $res = sqlQuery($sql, [$row['internal_file_id'], 1, "Attached file does not begin/end with ISA/IEA"]);
    } catch (Exception $e) {
        //log somewhere in openEMR
    }
}

function updateFileStatus($x12_partner_id, ?string $status = null, $file_name) {
    if (is_null($status)) { return; }
    try {
        $sql = "update x12_downloaded_files set file_process_status = ? where x12_partner_id = ? and file_name = ?";
        $res = sqlQuery($sql, [$status, $x12_partner_id, $file_name]);
    } catch (Exception $e) {
        //log somewhere in openEMR
    }
}

function findSegmentsByStart($internal_id, $rows, $s_with) {
    $segments = [];
    if (trim($s_with) == "") {
        return $segments;
    }
    $x = 0;
    foreach ($rows as $row) {
        $lead = getX12LineValue($row);
        if ($lead == trim($s_with)) {
            $segments[] = $x;
        }
        $x = $x + 1;
    }
    return $segments;
}

function findSegmentsByStartEnd($internal_id, $rows, $s_with, $e_with) {
    $segments = [];
    if ((trim($s_with) == "") || (trim($e_with) == "")) {
        return $segments;
    }
    $searchStart = true;
    $searchEnd = false;
    $x = 0;
    $start_point = -1;
    $end_point = -1;
    foreach ($rows as $row) {
        $lead = getX12LineValue($row);
        if ($searchStart) {
            if ($lead == $s_with) {
                $start_point = $x;
                $searchStart = false;
                $searchEnd = true;
            }
        } else if ($searchEnd) {
            if ($lead == $s_end) {
                $end = $x;
                $searchStart = true;
                $searchEnd = false;
                $segments[] = [$start, $end];
                $start_point = -1;
                $end_point = -1;
            }
        }
        $x = $x + 1;
    }
    if (($start_point !== -1) && ($end_point == -1)) {
        // we got a start, but not an appropriate end
        $msg = "Start segment {$s_with} found, but ending segment {$e_with} not found";
        logNewX12APError($internal_id, $start, $msg);
        //we do not add this to the segment # to return
    }
    return $segments;
}