<?php

function getSignature($client_id, $secret, $service, $market, $config_id, $timestamp, $api_prefix = 'ws', $api_version = 'v1')
{
    $string = implode('|', [$service, $market, $config_id, $timestamp, $api_prefix, $api_version]);
    return $client_id . ':' . base64_encode(hash_hmac('sha256', $string, $secret, true));
}

function generateCode()
{
    return mt_rand(1000000000, 9999999999);
}

function athena_processResultRows($res, $columnInfo, $isFirst)
{
    $result_array = [];


    foreach ($res as $idx => $row) {
        if ($isFirst && $idx == 0) continue;

        $result = [];
        if (isset($row['Data'])) {
            foreach ($row['Data'] as $idField => $value) {
                $value = $value['VarCharValue'] ?? null;
                $result[$columnInfo[$idField]['Label']] = $value;
            }
        }
        $result_array[] = $result;
    }
    return $result_array;
}

function getClientArcAssociationsOutputTable($clientArcAssociations)
{
    $clientArcAssociationsOutput = array();
    foreach ($clientArcAssociations as $row) {

        
        $row['info']    = json_encode($row['info'], JSON_PRETTY_PRINT);

        unset($row['created_at']);
        unset($row['updated_at']);
       
        $clientArcAssociationsOutput[] = $row;
    }

    return  $clientArcAssociationsOutput;
}

function isValidDate($date, $format = 'Y-m-d') {
    $dt = \DateTime::createFromFormat($format, $date);
    return $dt && $dt->format($format) === $date;
}
