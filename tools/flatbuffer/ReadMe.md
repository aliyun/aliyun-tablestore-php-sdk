# flatc
flatc基于源码构建，当前使用最新版本（22.9.29）
```bash
git clone https://github.com/google/flatbuffers.git
cd flatbuffers; git checkout v22.9.29
```

## 生成代码
```bash
flatc --version # flatc version 22.9.29
flatc --php sql.fbs
```