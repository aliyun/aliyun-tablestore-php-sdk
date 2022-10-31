<?php

namespace Aliyun\OTS;

use Aliyun\OTS\Handlers;

/**
 * OTSClient.php 是 Aliyun OTS SDK for PHP 的入口。更多关于OTS的信息，请参考阿里云官网OTS文档 https://docs.aliyun.com/?/pub/ots#/pub/ots
 *
 * OTSClient这个类实现了OTS服务的所有接口。用户可以通过创建OTSClient的对象，并调用它的方法来访问OTS服务的所有功能。
 *
 * 创建OTSClient对象时，你需要指定EndPoint, AccessKeyID, AccessKeySecret, 和InstanceName等参数。
 *
 * OTSClient提供的每个API都接受一个array作为请求，并返回一个array代表返回。当遇到OTS客户端或者服务端错误时，OTSClientException或OTSServerException会抛出。我们提供了详细的样例来说明每个API的使用方法。
 *
 * OTSClient会默认输出日志到屏幕（标准输出文件），你可以通过自定义日志处理函数来改变日志输出方式，或者关闭日志。为了让你的应用逻辑运行得更平稳，我们在OTSClient中实现了标准重试逻辑；如果需要改变重试逻辑，你可以参照RetryPolicy的文档。
 *
 * @package \Aliyun\OTS\OTSClient
 */
class OTSClient
{
    /** @var OTSClientConfig */
    private $config;

    /**
     * OTSClient的构造函数
     * @api
     * @param [] $args OTS客户端配置，包括EndPoint, AccessKeyID, AccessKeySecret, 和InstanceName等参数。
     * @example "examples/NewClient.php" 20 创建一个OTSClient对象
     * @example "examples/ErrorHandling.php" 20 错误处理样例
     * @example "examples/NewClient2.php" 20 所有可选参数
     */
    public function __construct(array $args) 
    {
        $this->config = new \Aliyun\OTS\OTSClientConfig($args);
        $this->handlers = new \Aliyun\OTS\Handlers\OTSHandlers($this->config);
    }

    /** 返回 OTSClientConfig 对象 
     * @example "examples/NewClientLogDefined.php" 20 指定日志输出方式
     * @example "examples/NewClientLogClosed.php" 20 关闭OTSClient中的日志
     */
    public function getClientConfig()
    {
        return $this->config;
    }

    /**
     * 创建表，并设定主键的个数、名称、顺序和类型，以及预留读写吞吐量。
     * API说明：https://help.aliyun.com/document_detail/27312.html
     * @api
     * @param [] $request 请求参数
     * @return [] 返回为空。CreateTable成功时不返回任何信息，这里返回一个空的array，与其他API保持一致。
     * @throws OTSClientException 当参数检查出错或服务端返回校验出错时
     * @throws OTSServerException 当OTS服务端返回错误时
     * @example "examples/CreateTable.php" 20
     */
    public function createTable(array $request) 
    {
        return $this->handlers->doHandle("CreateTable", $request);
    }

    /**
     * 根据表名删除一个表。
     * API说明：https://help.aliyun.com/document_detail/27314.html
     * @api
     * @param [] $request 请求参数
     * @return [] 返回为空。DeleteTable成功时不返回任何信息，这里返回一个空的array，与其他API保持一致。
     * @throws OTSClientException 当参数检查出错或服务端返回校验出错时
     * @throws OTSServerException 当OTS服务端返回错误时
     * @example "examples/DeleteTable.php" 40
     */
    public function deleteTable(array $request) 
    {
        return $this->handlers->doHandle("DeleteTable", $request);
    }

    /**
     * 获取一个表的信息，包括主键设计以及预留读写吞吐量信息。
     * API说明：https://help.aliyun.com/document_detail/27316.html
     * @api
     * @param [] $request 请求参数
     * @return [] 请求返回 
     * @throws OTSClientException 当参数检查出错或服务端返回校验出错时
     * @throws OTSServerException 当OTS服务端返回错误时
     * @example "examples/DescribeTable.php" 40
     */
    public function describeTable(array $request) 
    {
        return $this->handlers->doHandle("DescribeTable", $request);
    }

    /**
     * 更新一个表，包括这个表的预留读写吞吐量。
     * 这个API可以用来上调或者下调表的预留读写吞吐量。
     * API说明：https://help.aliyun.com/document_detail/27315.html
     * @api
     * @param [] $request 请求参数
     * @return [] 请求返回 
     * @throws OTSClientException 当参数检查出错或服务端返回校验出错时
     * @throws OTSServerException 当OTS服务端返回错误时
     * @example "examples/UpdateTable.php" 40
     * @example "examples/UpdateTable2.php" 40 只更新读或写CU的其中一项
     */
    public function updateTable(array $request) 
    {
        return $this->handlers->doHandle("UpdateTable", $request);
    }

    /**
     * 获取该实例下所有的表名。
     * API说明：https://help.aliyun.com/document_detail/27313.html
     * @api
     * @param [] $request 请求参数，为空。
     * @return [] 请求返回 
     * @throws OTSClientException 当参数检查出错或服务端返回校验出错时
     * @throws OTSServerException 当OTS服务端返回错误时
     * @example "examples/ListTable.php" 40
     */
    public function listTable(array $request) 
    {
        return $this->handlers->doHandle("ListTable", $request);
    }

    /**
     * 将全表的数据在逻辑上划分成接近指定大小的若干分片，返回这些分片之间的分割点以及分片所在机器的提示。
     * 一般用于计算引擎规划并发度等执行计划。
     * API说明：https://help.aliyun.com/document_detail/53813.html
     * @api
     * @param [] $request 请求参数。
     * @return [] 请求返回
     * @throws OTSClientException 当参数检查出错或服务端返回校验出错时
     * @throws OTSServerException 当OTS服务端返回错误时
     * @example "examples/ComputeSplitPointsBySizeRequest.php" 40
     */
    public function computeSplitPointsBySize(array $request)
    {
        return $this->handlers->doHandle("ComputeSplitPointsBySize", $request);
    }

    /**
     * 写入一行数据。如果该行已经存在，则覆盖原有数据。返回该操作消耗的CU。
     * API说明：https://help.aliyun.com/document_detail/27306.html
     * @api
     * @param [] $request 请求参数
     * @return [] 请求返回 
     * @throws OTSClientException 当参数检查出错或服务端返回校验出错时
     * @throws OTSServerException 当OTS服务端返回错误时
     * @example "examples/PutRow.php" 40
     */
    public function putRow(array $request) 
    {
        return $this->handlers->doHandle("PutRow", $request);
    }

    /**
     * 读取一行数据。
     * API说明：https://help.aliyun.com/document_detail/27305.html
     * @api
     * @param [] $request 请求参数
     * @return [] 请求返回 
     * @throws OTSClientException 当参数检查出错或服务端返回校验出错时
     * @throws OTSServerException 当OTS服务端返回错误时
     * @example "examples/GetRow.php" 60
     * @example "examples/GetRow2.php" 60 指定读该行的某几列
     */
    public function getRow(array $request) 
    {
        return $this->handlers->doHandle("GetRow", $request);
    }

    /**
     * 更新一行数据。
     * API说明：https://help.aliyun.com/document_detail/27307.html
     * @api
     * @param [] $request 请求参数
     * @return [] 请求返回 
     * @throws OTSClientException 当参数检查出错或服务端返回校验出错时
     * @throws OTSServerException 当OTS服务端返回错误时
     * @example "examples/UpdateRow1.php" 60  更新或追加该行的某几列
     * @example "examples/UpdateRow2.php" 60  删除该行的某几列
     */
    public function updateRow(array $request) 
    {
        return $this->handlers->doHandle("UpdateRow", $request);
    }

    /**
     * 删除一行数据。
     * API说明：https://help.aliyun.com/document_detail/27308.html
     * @api
     * @param [] $request 请求参数
     * @return [] 请求返回 
     * @throws OTSClientException 当参数检查出错或服务端返回校验出错时
     * @throws OTSServerException 当OTS服务端返回错误时
     * @example "examples/DeleteRow.php" 60
     */
    public function deleteRow(array $request) 
    {
        return $this->handlers->doHandle("DeleteRow", $request);
    }

    /**
     * 读取指定的多行数据。
     * API说明：https://help.aliyun.com/document_detail/27310.html
     * 请注意，BatchGetRow在部分行读取失败时，会在返回的$response中表示，而不是抛出异常。请参见样例：处理BatchGetRow的返回。
     * @api
     * @param [] $request 请求参数
     * @return [] 请求返回 
     * @throws OTSClientException 当参数检查出错或服务端返回校验出错时
     * @throws OTSServerException 当OTS服务端返回错误时
     * @example "examples/BatchGetRow1.php" 60 读取一个表的多行数据
     * @example "examples/BatchGetRow2.php" 60 读取多个表的数据
     * @example "examples/BatchGetRow3.php" 60 指定读取某几列
     * @example "examples/BatchGetRow4.php" 60 处理BatchGetRow的返回
     */
    public function batchGetRow(array $request) 
    {
        return $this->handlers->doHandle("BatchGetRow", $request);
    }

    /**
     * 写入、更新或者删除指定的多行数据。
     * API说明：https://help.aliyun.com/document_detail/27311.html
     * 请注意，BatchWriteRow在部分行读取失败时，会在返回的$response中表示，而不是抛出异常。请参见样例：处理BatchWriteRow的返回。
     * @api
     * @param [] $request 请求参数
     * @return [] 请求返回 
     * @throws OTSClientException 当参数检查出错或服务端返回校验出错时
     * @throws OTSServerException 当OTS服务端返回错误时
     * @example "examples/BatchWriteRow1.php" 40 写入几行数据
     * @example "examples/BatchWriteRow2.php" 40 更新几行数据
     * @example "examples/BatchWriteRow3.php" 40 删除几行数据
     * @example "examples/BatchWriteRow4.php" 80 处理BatchWriteRow的返回
     */
    public function batchWriteRow(array $request) 
    {
        return $this->handlers->doHandle("BatchWriteRow", $request);
    }

    /**
     * 范围读取起始主键和结束主键之间的数据。
     * 请注意，这个范围有可能会被服务端截断，你需要判断返回中的 next_start_primary_key 来决定是否继续调用 GetRange。
     * 你可以指定最多读取多少行。
     * 在指定开始主键和结束主键时，你可以用 INF_MIN 和 INF_MAX 来代表最大值和最小值，详见下面的代码样例。
     * API说明：https://help.aliyun.com/document_detail/27309.html
     * @api
     * @param [] $request 请求参数
     * @return [] 请求返回 
     * @throws OTSClientException 当参数检查出错或服务端返回校验出错时
     * @throws OTSServerException 当OTS服务端返回错误时
     * @example "examples/GetRange1.php" 60 读取一个范围的数据，遇到截断继续读取
     * @example "examples/GetRange2.php" 60 读取一个范围的数据，并指定若干列
     * @example "examples/GetRange3.php" 60 指定最多读取多少行
     */
    public function getRange(array $request) 
    {
        return $this->handlers->doHandle("GetRange", $request);
    }

    /**
     * 获取当前实例下所有表的stream信息。
     * API说明：https://help.aliyun.com/document_detail/52568.html
     * @api
     * @param [] $request 请求参数
     * @return [] 请求返回
     * @throws OTSClientException 当参数检查出错或服务端返回校验出错时
     * @throws OTSServerException 当OTS服务端返回错误时
     */
    public function listStream(array $request)
    {
        return $this->handlers->doHandle("ListStream", $request);
    }

    /**
     * 获取当前stream的shard信息。
     * API说明：https://help.aliyun.com/document_detail/52590.html
     * @api
     * @param [] $request 请求参数
     * @return [] 请求返回
     * @throws OTSClientException 当参数检查出错或服务端返回校验出错时
     * @throws OTSServerException 当OTS服务端返回错误时
     */
    public function describeStream(array $request)
    {
        return $this->handlers->doHandle("DescribeStream", $request);
    }

    /**
     * 获取读取当前shard记录的起始iterator。
     * API说明：https://help.aliyun.com/document_detail/52600.html
     * @api
     * @param [] $request 请求参数
     * @return [] 请求返回
     * @throws OTSClientException 当参数检查出错或服务端返回校验出错时
     * @throws OTSServerException 当OTS服务端返回错误时
     */
    public function getShardIterator(array $request)
    {
        return $this->handlers->doHandle("GetShardIterator", $request);
    }

    /**
     * 读取当前shard的增量内容。
     * API说明：https://help.aliyun.com/document_detail/52604.html
     * @api
     * @param [] $request 请求参数
     * @return [] 请求返回
     * @throws OTSClientException 当参数检查出错或服务端返回校验出错时
     * @throws OTSServerException 当OTS服务端返回错误时
     */
    public function getStreamRecord(array $request)
    {
        return $this->handlers->doHandle("GetStreamRecord", $request);
    }

    /**
     * 获取该表下所有的索引名。
     * @api
     *
     * @param [] $request
     *            请求参数，表名。
     * @return [] 请求返回
     * @throws OTSClientException 当参数检查出错或服务端返回校验出错时
     * @throws OTSServerException 当OTS服务端返回错误时
     * @example "src/examples/ListSearchIndex.php" 20
     */
    public function listSearchIndex(array $request)
    {
        return $this->handlers->doHandle("ListSearchIndex", $request);
    }

    /**
     * 获取该该表的某个索引的详细信息。
     * @api
     *
     * @param [] $request
     *            请求参数，表名。
     * @return [] 请求返回
     * @throws OTSClientException 当参数检查出错或服务端返回校验出错时
     * @throws OTSServerException 当OTS服务端返回错误时
     * @example "src/examples/DescribeSearchIndex.php" 20
     */
    public function describeSearchIndex(array $request)
    {
        return $this->handlers->doHandle("DescribeSearchIndex", $request);
    }


    /**
     * 创建索引。
     * @api
     *
     * @param [] $request
     *            请求参数，表名，索引配置等。
     * @return [] 请求返回
     * @throws OTSClientException 当参数检查出错或服务端返回校验出错时
     * @throws OTSServerException 当OTS服务端返回错误时
     * @example "src/examples/CreateSearchIndex.php" 20
     */
    public function createSearchIndex(array $request)
    {
        return $this->handlers->doHandle("CreateSearchIndex", $request);
    }

    /**
     * 删除索引。
     * @api
     *
     * @param [] $request
     *            请求参数，表名。
     * @return [] 请求返回
     * @throws OTSClientException 当参数检查出错或服务端返回校验出错时
     * @throws OTSServerException 当OTS服务端返回错误时
     * @example "src/examples/DeleteSearchIndex.php" 20
     */
    public function deleteSearchIndex(array $request)
    {
        return $this->handlers->doHandle("DeleteSearchIndex", $request);
    }

    /**
     * 更新该该表的某个索引的TTL。
     * @api
     *
     * @param [] $request
     *            请求参数，表名。
     * @return [] 请求返回
     * @throws OTSClientException 当参数检查出错或服务端返回校验出错时
     * @throws OTSServerException 当OTS服务端返回错误时
     * @example "src/examples/UpdateSearchIndex.php" 20
     */
    public function updateSearchIndex(array $request)
    {
        return $this->handlers->doHandle("UpdateSearchIndex", $request);
    }

    /**
     * ComputeSplits。
     * @api
     *
     * @param [] $request
     *            请求参数，表名。
     * @return [] 请求返回
     * @throws OTSClientException 当参数检查出错或服务端返回校验出错时
     * @throws OTSServerException 当OTS服务端返回错误时
     * @example "src/examples/ParallelScan.php" 20
     */
    public function computeSplits(array $request)
    {
        return $this->handlers->doHandle("ComputeSplits", $request);
    }

    /**
     * parallelScan。
     * @api
     *
     * @param [] $request
     *            请求参数，表名。
     * @return [] 请求返回
     * @throws OTSClientException 当参数检查出错或服务端返回校验出错时
     * @throws OTSServerException 当OTS服务端返回错误时
     * @example "src/examples/ParallelScan.php" 20
     */
    public function parallelScan(array $request)
    {
        return $this->handlers->doHandle("ParallelScan", $request);
    }

    /**
     * 多元索引查询。
     * @api
     *
     * @param [] $request
     *            请求参数，表名。
     * @return [] 请求返回
     * @throws OTSClientException 当参数检查出错或服务端返回校验出错时
     * @throws OTSServerException 当OTS服务端返回错误时
     * @example "src/examples/Search.php" 20
     */
    public function search(array $request)
    {
        return $this->handlers->doHandle("Search", $request);
    }

    /**
     * 创建二级索引。
     * @api
     *
     * @param [] $request
     *            请求参数，表名。
     * @return [] 请求返回
     * @throws OTSClientException 当参数检查出错或服务端返回校验出错时
     * @throws OTSServerException 当OTS服务端返回错误时
     * @example "src/examples/CreateIndex.php" 20
     */
    public function createIndex(array $request)
    {
        return $this->handlers->doHandle("CreateIndex", $request);
    }

    /**
     * 删除二级索引。
     * @api
     *
     * @param [] $request
     *            请求参数，表名。
     * @return [] 请求返回
     * @throws OTSClientException 当参数检查出错或服务端返回校验出错时
     * @throws OTSServerException 当OTS服务端返回错误时
     * @example "src/examples/DropIndex.php" 20
     */
    public function dropIndex(array $request)
    {
        return $this->handlers->doHandle("DropIndex", $request);
    }


    /**
     * 开始事务，获取事务ID。
     * @api
     *
     * @param [] $request
     *            请求参数，表名。
     * @return [] 请求返回
     * @throws OTSClientException 当参数检查出错或服务端返回校验出错时
     * @throws OTSServerException 当OTS服务端返回错误时
     * @example "src/examples/StartLocalTransaction.php" 20
     */
    public function startLocalTransaction(array $request)
    {
        return $this->handlers->doHandle("StartLocalTransaction", $request);
    }


    /**
     * 提交事务。
     * @api
     *
     * @param [] $request
     *            请求参数，表名。
     * @return [] 请求返回
     * @throws OTSClientException 当参数检查出错或服务端返回校验出错时
     * @throws OTSServerException 当OTS服务端返回错误时
     * @example "src/examples/CommitTransaction.php" 20
     */
    public function commitTransaction(array $request)
    {
        return $this->handlers->doHandle("CommitTransaction", $request);
    }


    /**
     * 舍弃事务。
     * @api
     *
     * @param [] $request
     *            请求参数，表名。
     * @return [] 请求返回
     * @throws OTSClientException 当参数检查出错或服务端返回校验出错时
     * @throws OTSServerException 当OTS服务端返回错误时
     * @example "src/examples/AbortTransaction.php" 20
     */
    public function abortTransaction(array $request)
    {
        return $this->handlers->doHandle("AbortTransaction", $request);
    }

    /**
     * SQLQuery。
     * @api
     *
     * @param [] $request
     *            请求参数，query sql。
     * @return [] 请求返回
     * @throws OTSClientException 当参数检查出错或服务端返回校验出错时
     * @throws OTSServerException 当OTS服务端返回错误时
     * @example "src/examples/SQLQuery.php" 20
     */
    public function sqlQuery(array $request)
    {
        return $this->handlers->doHandle("SQLQuery", $request);
    }
}

