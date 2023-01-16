# 版本 5.1.1 发布于 2023/01/16
1. 支持自增列

# 版本 5.1.0 发布于 2022/10/20
1. 支持表AllowUpdate修改
2. 支持SQLQuery（FlatBuffer）
3. 多元索引
   - 创建索引：支持创建灰度索引、TTL
   - 索引详情：支持获取索引计量信息、灰度索引名、索引创建时间、TTL
   - 更新索引：支持修改索引TTL
   - 索引导出：ParallelScan
   - 索引查询：支持Agg、GroupBy
   

# 版本 5.0.5 发布于 2021/11/01
1. 支持多元索引基础功能，所以的crud，索引查询（不包含聚合）
2. 支持二级索引（仅全局二级索引）
3. 支持分区事务
4. 支持 guzzlehttp/guzzle ^7.2.0

# 版本 4.1.0 发布于 2018/06/05
1. 完善文档，修复bug.
2. 增加 ComputeSplitPointsBySize 接口，并增加样例
3. 增加流相关接口，ListStream, DescribeStream, GetShardIterator, GetStreamRecord.
4. 变更：GetRow, BatchGetRow, GetRange支持TimeRange过滤.
5. 变更：CreateTable, UpdateTable, DescribeTable 支持stream设置.

# 版本 4.0.0 发布于 2018/05/15
1. 支持5.5以上php版本，包括5.5、5.6、7.0、7.1、7.2等版本，只支持64位的PHP系统，推荐使用PHP7.
2. 新功能：支持TTL设置，createTable, updateTable新增table_options参数
3. 新功能：支持多版本，putRow, updateRow, deleteRow, batchGetRow均支持timestamp设置，getRow, getRange, BatchGet等接口支持max_versions过滤
4. 新功能：支持主键列自增功能, 接口新增return_type, 返回新增primary_key，返回对应操作的primary_key
5. 变更：底层protobuf升级成Google官方版本protobuf-php库
6. 变更：各接口的primary_key变更成list类型,保证顺序性
7. 变更：各接口的attribute_columns变更成list类型，以支持多版本功能

# 版本 2.0.3 发布于 2016/05/15

1. 删除无用的代码

# 版本 2.0.2 发布于 2016/04/11

1. BugFix：错误的将有符号整数解析为无符号整数。


# 版本 2.0.1 发布于 2015/12/29

1. 所有示例代码中创建的表的预留CU设置为0.

# 版本 2.0.0 发布于 2015/09/21

1. SDK 正式发布，包含以下接口：

   ListTable
   CreateTable
   DescribeTable
   UpdateTable
   DeleteTable

   GetRow
   PutRow
   UpdateRow
   DeleteRow

   BatchGetRow
   BatchWriteRow
   GetRange

2. 兼容PHP 5.3, 5.4, 5.5 和 5.6 版本。
3. 包含标准的重试策略。
4. 使用 GuzzleHttp Client 作为网络库。
5. 使用 composer 作为依赖管理和工程组织工具。
6. 暂不包含异步接口、连接池。
7. GetRange接口占用内存较高，用户需要设置PHP内存限制到512M。
8. 使用 phpDocumentor 2 生成HTML格式的编程文档。

