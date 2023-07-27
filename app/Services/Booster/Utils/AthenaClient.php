<?php
namespace App\Services\Booster\Utils;

use Aws\Athena\AthenaClient as Athena;
use Exceptions\Booster\AthenaQueryException;
use Illuminate\Support\Facades\Log;


class AthenaClient
{
    protected $client;
    protected $db_name;
    protected $output_location;

    public function __construct($version, $region, $key, $secret)
    {

        $this->client = new Athena([
            'version'   => $version,
            'region'    => $region,
            'credentials' => [
                'key'       => $key,
                'secret'    => $secret
            ]
        ]);
    }


    public function setDb($db_name)
    {
        $this->db_name = $db_name;
    }

    public function getDb($db_name)
    {
        return $this->db_name = $db_name;
    }

    public function setOutputLocation($s3_location)
    {
        $this->output_location = $s3_location;
    }

    public function getOutputLocation()
    {
        return $this->output_location;
    }

    public function getData($query)
    {
        $result1 = $this->client->startQueryExecution([
            'QueryExecutionContext' => [ 'Database' => $this->db_name ],
            'QueryString'   => $query,
            'ResultConfiguration' => [
                'EncryptionConfiguration' => [ 'EncryptionOption' => 'SSE_S3' ],
                'OutputLocation' => $this->output_location
            ]
        ]);

        $queryExecutionId = $result1->get('QueryExecutionId');

        $this->waitForQueryToComplete($queryExecutionId);

        $result1 = $this->client->GetQueryResults([
            'QueryExecutionId' => $queryExecutionId,
            'MaxResults' => 500
        ]);

        $data = $result1->get('ResultSet');
        $res = $data['Rows'];

        while (($result1->get('NextToken') != null)) {
            $result1 = $this->client->GetQueryResults([
                'QueryExecutionId' => $queryExecutionId, // REQUIRED
                'NextToken' => $result1->get('NextToken'),
                'MaxResults' => 500
            ]);

            $data = $result1->get('ResultSet');
            $res = array_merge($res, $data['Rows']);
        }

        $resData = $this->processResultRows($res);
        return $resData;
    }

    protected function waitForQueryToComplete($queryExecutionId)
    {
        while (1) {
            $result = $this->client->getQueryExecution(array('QueryExecutionId' => $queryExecutionId));
            $res = $result->toArray();

            //echo $res[‘QueryExecution’][‘Status’][‘State’].'<br/>’;
            if ($res['QueryExecution']['Status']['State'] == 'FAILED') {
                throw new AthenaQueryException(
                    'Query Failed',
                    $queryExecutionId,
                    $res
                );
            } else if ($res['QueryExecution']['Status']['State'] == 'CANCELED') {
                throw new AthenaQueryException(
                    'Query cancelled',
                    $queryExecutionId,
                    $res
                );
            } else if ($res['QueryExecution']['Status']['State'] == 'SUCCEEDED') {
                return true;
            }
            usleep(500000); //500ms
        }
    }


    protected function processResultRows($res)
    {
        $result = [];
        $result_array = [];

        for ($i = 0; $i < count($res); $i++) {
            for ($n = 0; $n < count($res[$i]['Data']); $n++) {
                if ($i == 0)
                    $result[] = $res[$i]['Data'][$n]['VarCharValue'];
                else {
                    if(isset($res[$i]['Data'][$n]['VarCharValue'])) {
                        $result_array[$i][$result[$n]] = $res[$i]['Data'][$n]['VarCharValue'];
                    } else {
                        $result_array[$i][$result[$n]] = null;
                    }
                }
            }
        }

        return $result_array;
    }
}