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

