# csv-convertor

## 概要

本リポジトリは、CSVファイルのデータ変換や差分チェック等、便利ツールを揃えることを目的としています。
なお、本ツールは、故あって全てPHPで実現しています。

現実点でのツールとしては、以下があります。

* convert.php
* cp_by_pkey.php
* diff.php
* complex_convert.php

## 前提とする環境

* OS
  * Ubuntu系のOS

## インストール手順

PHPをインストールする必要があります。

```
sudo apt install php8.1-cli
```

```
sudo apt-get install php-mbstring
```

## convert.php

CSVファイルの特定列のデータを別のCSVファイルの特定列にコピーします。
なお、コピーするための様々なパラメータは以下の書式で定義します。

```json
{
    "line_range": {
        "start": 1,
        "end": -1
    },
    "column_mapping" : [
        {
            "src": 1,
            "dst": 2
        },
        {
            "src": 2,
            "dst": 3
        }
    ]
}
```

* line_range
  * コピー行の範囲を `start`, `end` で指定します。
* column_mapping
  * コピーする列情報を `src`, `dst` で指定します。
  * 複数列を指定したい場合は、それらのデータを列挙します。

イメージを膨らめせるために、サンプルデータを以下に用意しています。

* コピー元データ
  * https://github.com/tmori/csv-convertor/blob/main/data/test-data-src.csv
* コピー先データ
  * https://github.com/tmori/csv-convertor/blob/main/data/test-data-dst.csv

ツール実行方法は以下のとおりです。

```
php ./convert.php ./config/conv.json ./data/test-data-src.csv ./data/test-data-dst.csv 
```

成功すると、`dump.csv`ファイルが、カレントディレクトリ直下に生成され、コピー後のデータが出力されます。

```csv
col1,col2,col3,col4,col5
dst1-1,dst1-2,src1-2,src1-3,dst1-5
dst2-1,dst2-2,src2-2,src2-3,dst2-5
dst3-1,dst3-2,src3-2,src3-3,dst3-5
```

## cp_by_pkey.php

親子関係のあるCSVファイルがあった場合、子のCSVファイルの各行は、特定列(外部参照キー)で親のCSVファイルの主キーと紐づけているとします。
本ツールは、その紐づけられた親のCSVファイルの特定列のデータを、子のCSVファイルの特定列にコピーするツールです。
なお、コピーするための様々なパラメータは以下の書式で定義します。

```json
{
    "start_line": 1,
    "parent_pkey_col": 1,
    "parent_ref_col": 0,
    "child_fkey_col": 2,
    "child_pkey_col": 0
}
```

* start_line
  * 子のCSVファイルの開始行を指定します。
* parent_pkey_col
  * 親の主キー列番号を指定します。
* parent_ref_col
  * 親のコピー元列番号を指定します。
* child_fkey_col
  * 子の外部参照キー列番号を指定します。
* child_pkey_col
  * 子のコピー先列番号を指定します。

イメージを膨らめせるために、サンプルデータを以下に用意しています。

* 親データ
  * https://github.com/tmori/csv-convertor/blob/main/data/pkey/test-data-parent.csv
* 子データ
  * https://github.com/tmori/csv-convertor/blob/main/data/pkey/test-data-child.csv

ツール実行方法は以下のとおりです。

```
php ./cp_by_pkey.php ./config/pkey.json ./data/pkey/test-data-parent.csv ./data/pkey/test-data-child.csv 
```

成功すると、`dump.csv`ファイルが、カレントディレクトリ直下に生成され、コピー後のデータが出力されます。

```csv
col1,col2,col3,col4,col5
5,child1-2,99184,child1-4,child1-5
2,child2-2,99183,child2-4,child2-5
1,child3-2,99182,child3-4,child3-5
```

## diff.php

## complex_convert.php


