<?php
namespace Aliyun\OTS\Handlers;

use Aliyun\OTS\Consts\PrimaryKeyOptionConst;
use Aliyun\OTS\PlainBuffer\PlainBufferCodedInputStream;
use Aliyun\OTS\PlainBuffer\PlainBufferInputStream;
use Aliyun\OTS\ProtoBuffer\Protocol\BatchGetRowResponse;
use Aliyun\OTS\ProtoBuffer\Protocol\BatchWriteRowResponse;
use Aliyun\OTS\ProtoBuffer\Protocol\DeleteRowResponse;
use Aliyun\OTS\ProtoBuffer\Protocol\DescribeTableResponse;
use Aliyun\OTS\ProtoBuffer\Protocol\GetRangeResponse;
use Aliyun\OTS\ProtoBuffer\Protocol\GetRowResponse;
use Aliyun\OTS\ProtoBuffer\Protocol\ListTableResponse;
use Aliyun\OTS\ProtoBuffer\Protocol\PrimaryKeyOption;
use Aliyun\OTS\ProtoBuffer\Protocol\PrimaryKeyType;
use Aliyun\OTS\ProtoBuffer\Protocol\PutRowResponse;
use Aliyun\OTS\ProtoBuffer\Protocol\RowInBatchWriteRowResponse;
use Aliyun\OTS\ProtoBuffer\Protocol\UpdateRowResponse;
use Aliyun\OTS\ProtoBuffer\Protocol\UpdateTableResponse;

//use CreateTableResponse;
//use DeleteTableResponse;


class ProtoBufferDecoder
{
    public function handleBefore($context)
    {
        // empty
    }

    public function decodeListTableResponse($body)
    {
        $pbMessage = new ListTableResponse();
        $pbMessage->mergeFromString($body);
        $response = array();
        $tableNames = $pbMessage->getTableNames();
        for ($i = 0; $i < count($tableNames); $i++)
        {
            array_push($response, $tableNames[$i]);
        }
        return $response;
    }

    public function decodeCreateTableResponse($body)
    {
        return array();
    }

    public function decodeDeleteTableResponse($body)
    {
        return array();
    }

    private function parseCapacityUnit($pbMessage)
    {
        return array(
            "capacity_unit" => array(
                "read" => $pbMessage->getRead(),
                "write" => $pbMessage->getWrite()
            ),
        );
    }

    private function parseReservedThroughputDetails($pbMessage)
    {
        $capacityUnit = $this->parseCapacityUnit($pbMessage->getCapacityUnit());

        return array(
            "capacity_unit" => $capacityUnit['capacity_unit'],
            "last_increase_time" => $pbMessage->getLastIncreaseTime(),
            "last_decrease_time" => $pbMessage->getLastDecreaseTime()
        );
    }

    private function parseTableOptions($pbMessage)
    {
        return array(
            "time_to_live" => $pbMessage->getTimeToLive(),
            "max_versions" => $pbMessage->getMaxVersions(),
            "deviation_cell_version_in_sec" => $pbMessage->getDeviationCellVersionInSec()
        );
    }

    public function decodeDescribeTableResponse($body)
    {
        $pbMessage = new DescribeTableResponse();
        $pbMessage->mergeFromString($body);
        $tableMeta = $pbMessage->getTableMeta();
         
        $pkSchema = array();
        $primaryKeys = $tableMeta->getPrimaryKey();
        for ($i = 0; $i < count($primaryKeys); $i++) {
            $pkColumn = $primaryKeys[$i];
            $column = array();
            $type = null;
            $pkSchema[$i] = array();
            $pkSchema[$i][] = $pkColumn->getName();
            switch ($pkColumn->getType())
            {
                case PrimaryKeyType::INTEGER:
                    $type = "INTEGER";
                    break;
                case PrimaryKeyType::STRING:
                    $type = "STRING";
                    break;
                case PrimaryKeyType::BINARY:
                    $type = "BINARY";
                    break;
                default:
                    throw new OTSClientException("Invalid column type in response.");
            }
            $pkSchema[$i][] = $type;
            if($pkColumn->hasOption()) {
                $column['type'] = $type;
                switch($pkColumn->getOption()) {
                    case PrimaryKeyOption::AUTO_INCREMENT:
                        $column['option'] = PrimaryKeyOptionConst::CONST_PK_AUTO_INCR;
                        break;
                    default:
                        throw new OTSClientException("Invalid column option in response.");
                }
                $pkSchema[$i][] = $column['option'];
            }
        }

        $response = array(
            "table_meta" => array(
                "table_name" => $tableMeta->getTableName(),
                "primary_key_schema" => $pkSchema,
            ),
            "capacity_unit_details" => $this->parseReservedThroughputDetails($pbMessage->getReservedThroughputDetails()),
            "table_options" => $this->parseTableOptions($pbMessage->getTableOptions())
        );
        return $response;
    }

    public function decodeUpdateTableResponse($body)
    {
        $pbMessage = new UpdateTableResponse();
        $pbMessage->mergeFromString($body);
        $response = array(
            "capacity_unit_details" => $this->parseReservedThroughputDetails($pbMessage->getReservedThroughputDetails()),
            "table_options" => $this->parseTableOptions($pbMessage->getTableOptions())
        );
        return $response;
    }

    private function parseRow($row)
    {
        if(strlen($row) != 0) {
            $inputStream = new PlainBufferInputStream($row);
            $codedInputStream = new PlainBufferCodedInputStream($inputStream);
            return $codedInputStream->readRow();
        }
    }

    private function parseConsumed($pbMessage)
    {
        return $this->parseCapacityUnit($pbMessage->getCapacityUnit());
    }

    public function decodeGetRowResponse($body)
    {
        $pbMessage = new GetRowResponse();
        $pbMessage->mergeFromString($body);
        $rawRow = $this->parseRow($pbMessage->getRow());
        $response = array(
            "consumed" => $this->parseConsumed($pbMessage->getConsumed()),
            "primary_key" => $rawRow["primary_key"],
            "attribute_columns" => $rawRow["attribute_columns"],
            "token" => $pbMessage->getNextToken()
        );

        return $response;
    }

    public function decodePutRowResponse($body)
    {
        $pbMessage = new PutRowResponse();
        $pbMessage->mergeFromString($body);
        $rawRow = $this->parseRow($pbMessage->getRow());
        $response = array(
            "consumed" => $this->parseConsumed($pbMessage->getConsumed()),
            "primary_key" => $rawRow["primary_key"],
            "attribute_columns" => $rawRow["attribute_columns"]
        );
        return $response;
    }

    public function decodeUpdateRowResponse($body)
    {
        $pbMessage = new UpdateRowResponse();
        $pbMessage->mergeFromString($body);
        $rawRow = $this->parseRow($pbMessage->getRow());
        $response = array(
            "consumed" => $this->parseConsumed($pbMessage->getConsumed()),
            "primary_key" => $rawRow["primary_key"],
            "attribute_columns" => $rawRow["attribute_columns"]
        );
        return $response;
    }

    public function decodeDeleteRowResponse($body)
    {
        $pbMessage = new DeleteRowResponse();
        $pbMessage->mergeFromString($body);
        $rawRow = $this->parseRow($pbMessage->getRow());
        $response = array(
            "consumed" => $this->parseConsumed($pbMessage->getConsumed()),
            "primary_key" => $rawRow["primary_key"],
            "attribute_columns" => $rawRow["attribute_columns"]
        );
        return $response;
    }

    private function parseIsOK($isOK)
    {
        // PB library will treat bool as int
        // we need to convert it back to bool
        if ($isOK) {
            return true;
        } else {
            return false;
        }
    }

    public function decodeBatchGetRowResponse($body)
    {
        $pbMessage = new BatchGetRowResponse();
        $pbMessage->mergeFromString($body);

        $tables = array();
        $inTable = $pbMessage->getTables();
        for ($i = 0; $i < count($inTable); $i++) {
            $tableInBatchGetRow = $inTable[$i];
            $rowList = array();
            $inRows = $tableInBatchGetRow->getRows();
            for ($j = 0; $j < count($inRows);$j++) {
                $rowInBatchGetRow = $inRows[$j];
                $consumed = $rowInBatchGetRow->getConsumed();
                $error = $rowInBatchGetRow->getError();
                $isOK = $this->parseIsOK($rowInBatchGetRow->getIsOk());

                if($isOK)
                {
                    $rawRow = $this->parseRow($rowInBatchGetRow->getRow());
                    $rowData = array(
                        "is_ok" => $isOK,
                        "consumed" => $this->parseConsumed($consumed),
                        "primary_key" => $rawRow["primary_key"],
                        "attribute_columns" => $rawRow["attribute_columns"]
                    );
                }
                else
                {
                    $rowData = array(
                        "is_ok" => $isOK,
                        "error" => array(
                            "code" => $error->getCode(),
                            "message" =>$error->getMessage()
                        ),
                    );
                }
                array_push($rowList, $rowData);
            }

            array_push($tables, array(
                "table_name" => $tableInBatchGetRow->getTableName(),
                "rows" => $rowList,
            ));
        }

        return array("tables" => $tables);
    }


    private function decodeWriteRowItem(RowInBatchWriteRowResponse $rowItem)
    {
        $consumed = $rowItem->getConsumed();
        $error = $rowItem->getError();
        $isOK = $this->parseIsOK($rowItem->getIsOk());

        if ($isOK) {
            $rawRow = $this->parseRow($rowItem->getRow());
            $row = array(
                "is_ok" => $isOK,
                "consumed" => $this->parseConsumed($consumed),
                "primary_key" => $rawRow["primary_key"],
                "attribute_columns" => $rawRow["attribute_columns"]
            );
        } else {
            $row = array(
                "is_ok" => $isOK,
                "error" => array(
                    "code" => $error->getCode(),
                    "message" => $error->getMessage()
                ),
            );
        }
        return $row;
    }

    public function decodeBatchWriteRowResponse($body)
    {
        $pbMessage = new BatchWriteRowResponse();
        $pbMessage->mergeFromString($body);
        $ret = array();
        $ret['tables'] = array();
        $tables = $pbMessage->getTables();
        for($i = 0; $i < count($tables); $i++) {
            $table = array();
            $table['rows'] = array();
            $tableItem = $tables[$i];
            $tableName = $tableItem->getTableName();
            $table['table_name'] = $tableName;
            $rows = $tableItem->getRows();
            for($j = 0; $j < count($rows); $j++) {
                $rowItem = $rows[$j];
                $row = self::decodeWriteRowItem($rowItem);
                $table['rows'][] = $row;
            }
            $ret['tables'][] = $table;
        }
        return $ret;
    }

    //TODO:
    public function decodeGetRangeResponse($body)
    {
        $pbMessage = new GetRangeResponse();
        $pbMessage->mergeFromString($body);
        $consumed = $pbMessage->getConsumed();

        $rowList = array();
        $row = $pbMessage->getRows();
        if(strlen($row) != 0) {
            $inputStream = new PlainBufferInputStream($row);
            $codedInputStream = new PlainBufferCodedInputStream($inputStream);
            $rowList = $codedInputStream->readRows();
        }

        $nextStartPrimaryKey = null;
        $nextPK = $pbMessage->getNextStartPrimaryKey();
        if(strlen($nextPK) != 0) {
            $inputStream = new PlainBufferInputStream($nextPK);
            $codedInputStream = new PlainBufferCodedInputStream($inputStream);
            $row = $codedInputStream->readRow();
            $nextStartPrimaryKey = $row['primary_key'];
        }

        return array(
            "consumed" => $this->parseConsumed($consumed),
            "next_start_primary_key" => $nextStartPrimaryKey,
            "rows" => $rowList,
            "next_token" => $pbMessage->getNextToken()
        );
    }

    public function handleAfter($context)
    {
        if ($context->otsServerException != null) {
            return;
        }

        $apiName = $context->apiName;
        $methodName = "decode" . $apiName . "Response";
        $response = $this->$methodName($context->responseBody);
        $context->response = $response;

        $debugLogger = $context->clientConfig->debugLogHandler;
        if ($debugLogger != null) {
            $debugLogger("$apiName Response " . json_encode($response));
        }
    }
}

