# protoc
当前，php代码中pb部分是基于protoc@3.7.1版本做的代码自动生成,出于兼容性考虑，建议统一先使用3.7.1保持一致

```bash
git clone https://github.com/protocolbuffers/protobuf.git
cd protobuf; git checkout v3.7.1
```

## protoc限制
1、3.X版本不支持proto2的代码生成
2、2.X不支持php的代码生成

## 方案
构建前使用支持proto2类型的php_generator.cc覆盖源码文件后，重新编译
代码位置：
```
src/google/protobuf/compiler/php/php_generator.cc
```

## 生成代码
```bash
protoc --version # libprotoc 3.7.1
# mkdir gen # if gen is not exists for generated codes
protoc --php_out=./gen table_store.proto
```