# csv-convertor

## 概要

本リポジトリは、CSVファイルのデータ変換や差分チェック等、便利ツールを揃えることを目的としています。
なお、本ツールは、故あって全てPHPで実現しています。

現実点でのツールとしては、以下があります。

* cp_normal.php
* cp_by_pkey.php
* diff.php
* complex_convert.php
* add_double_quote.php

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

## cp_normal.php

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
  * https://github.com/tmori/csv-convertor/blob/main/data/normal/test-data-src.csv
* コピー先データ
  * https://github.com/tmori/csv-convertor/blob/main/data/normal/test-data-dst.csv

ツール実行方法は以下のとおりです。

```
 php ./cp_normal.php ./config/cp-normal.json ./data/normal/test-data-src.csv ./data/normal/test-data-dst.csv 
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
    "parent_src_col": 0,
    "child_fkey_col": 2,
    "child_dst_col": 0
}
```

* start_line
  * 子のCSVファイルの開始行を指定します。
* parent_pkey_col
  * 親の主キー列番号を指定します。
* parent_src_col
  * 親のコピー元列番号を指定します。
* child_fkey_col
  * 子の外部参照キー列番号を指定します。
* child_dst_col
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

あるCSVファイルを更新しているときに、どこをどう直したのかわからなくなる場合があります。
diffツールを使えば差分チェックはできますが、修正量が多いと、どこが追加でどこが削除でどこが更新なのか見分けるのが辛くなるケースです。
こういう場合に本ツールが本領を発揮します。

本ツールでは、主キーベースで行比較をします。
引数として、主キーの列番号と更新前のCSVファイルと更新後のCSVファイルを与えると、差分情報をを出力してくれます。

なお、本ツールの様々なパラメータは以下の書式で定義します。

```json
{
    "start_line": 1,
    "pkeys": [
        0,
        1
    ]
}
```

* start_line
  * 差分チェックの開始行を指定します。
* pkeys
  * 主キー列番号を列挙します。

イメージを膨らめせるために、サンプルデータを以下に用意しています。

* 変更前のCSVファイル
  * https://github.com/tmori/csv-convertor/blob/main/data/diff/old.csv
* 変更後のCSVファイル
  * https://github.com/tmori/csv-convertor/blob/main/data/diff/new.csv
  
ツール実行方法は以下のとおりです。

```
php ./diff.php ./config/table-pkey.json ./data/diff/old.csv ./data/diff/new.csv ./diff_csv
```

成功すると、以下のファイルが、カレントディレクトリ直下に生成され、差分情報が出力されます。

### update-old.csv
変更があった行を出力します。ただし、出力されるのは修正前のデータ一覧となります。
```csv
p1,p2,col1,col2,col3
1,9920,data1-1,data1-2,data1-3
```

### update-new.csv
変更があった行を出力します。ただし、出力されるのは修正後のデータ一覧となります。
```csv
p1,p2,col1,col2,col3
1,9920,data1-1,data1-2,data1-updated
```
### create.csv
修正後に追加されたデータ一覧が出力されます。
```csv
p1,p2,col1,col2,col3
3,9920,created-1,created-2,created-3
```

### delete.csv
修正後に削除されたデータ一覧が出力されます。
```csv
p1,p2,col1,col2,col3
1,9921,delete-1,delete-2,delete-3
```

### same.csv
変更がなかったデータ一覧が出力されます。
```csv
p1,p2,col1,col2,col3
2,9920,data3-1,data3-2,data3-3
2,9921,data4-1,data4-2,data4-3
```



## complex_convert.php

本ツールは、主キーベースで連携されたCSVファイルがあった場合に、入力元のCSVファイルの様々な列を合成して、CSVファイルを生成します。

なお、生成のための様々なパラメータは以下の書式で定義します。

```json
{
    "src_pkeys": [
        0
    ],
    "dst_pkeys": [
        1
    ],
    "start_line_src": 1,
    "start_line_dst": 1,
    "conv_mapping" : [
        {
            "conv_type": "fixed",
            "value": 0,
            "dst": 5
        },
        {
            "conv_type": "normal",
            "src": 2,
            "dst": 4
        },
        {
            "conv_type": "split",
            "split_key": "@",
            "split_index": 0,
            "src": 2,
            "dst": 2
        },
        {
            "conv_type": "combine",
            "combine_format": "%s(st_%s)",
            "srcs": [ 1, 0 ],
            "dst": 3
        }
    ]
}
```

* src_pkeys
  * 入力元の主キー列を列挙します。
* dst_pkeys
  * 出力先の主キー列を列挙します。
* start_line_src
  * 入力元の開始行を指定します。
* start_line_dst
  * 出力先の生成開始行を指定します。
* conv_mapping
  * 様々な合成するための情報を列挙します。現時点では以下のものがあります。
    * conv_type=fixed
      * 出力先の列を`dst`で指定すると、`value` の値が設定されます。
    * conv_type=normal
      * 入力元の列を`src`で、出力先の列を`dst`で指定すると、そのままコピーされます。
    * conv_type=split
      * 入力元の列`src`の値をキーワード`split_key`で分割し、`split_index`番目の値を`dst`の列にコピーします。
    * conv_type=combine
      * 入力元の列`srcs`の値(2個のみ)を`combine_format`の書式でマージして、`dst`の列にコピーします。

イメージを膨らめせるために、サンプルデータを以下に用意しています。

* 入力元データ
  * https://github.com/tmori/csv-convertor/blob/main/data/complex/test-data-src.csv
* 出力先データ
  * https://github.com/tmori/csv-convertor/blob/main/data/complex/test-data-dst.csv

ツール実行方法は以下のとおりです。

```
php ./complex_convert.php ./config/complex_conv.json ./data/complex/test-data-src.csv ./data/complex/test-data-dst.csv  
```

成功すると、`dump.csv`ファイルが、カレントディレクトリ直下に生成され、コピー後のデータが出力されます。

```csv
id,user_id,user_code,user_name,email,bool
1,99119,trajiro,寅次郎(st_99119),trajiro@example.com,0
,99120,yasujiro,小津安二郎(st_99120),yasujiro@example.com,0
```

## add_double_quote.php

本ツールは、単純にCSVファイル内の全データをダブルクォートで括るだけです。
Exmentのインポート対象ファイルのCSVファイルの書式がこうなっていたので、この機能を作りました。

ツール実行方法は以下のとおりです。

```
php ./add_double_quote.php ./dump.csv  
```

成功すると、`dump.csv`ファイルが、カレントディレクトリ直下に生成され、コピー後のデータが出力されます。

```csv
"id","user_id","user_code","user_name","email","bool"
"1","99119","trajiro","寅次郎(st_99119)","trajiro@example.com","0"
"","99120","yasujiro","小津安二郎(st_99120)","yasujiro@example.com","0"
```
