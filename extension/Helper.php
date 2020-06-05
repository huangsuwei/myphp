<?php
/**
 * Created by phpstorm
 * User: hsw
 * Date: 2020/6/5 15:36
 */

class Helper
{
    /**
     * 根据key获取value
     * @param $key
     * @param $data
     * @param string $field
     * @param string $default
     * @return mixed|string
     * @author hsw
     */
    public static function getValueByKey($key, $data, $field = '', $default = '')
    {
        return $field ? $data[$key][$field] ?? $default : $data[$key] ?? $default;
    }

    /**
     * 根据需要的下标组装数组
     * @param $keyField
     * @param array $list
     * @param string $columnField
     * @param bool $isMulti 同样的key下是否有多个value值，默认只有一个，需要多个传true
     * @return array
     * @author hsw
     */
    public static function combinationKeyValue($keyField, array $list, string $columnField = '', bool $isMulti = false) : array
    {
        $data = [];
        foreach ($list as $temp) {
            $value = $columnField ? $temp[$columnField] : $temp;
            if ($isMulti) {
                $data[$temp[$keyField]][] = $value;
            } else {
                $data[$temp[$keyField]] = $value;
            }
        }

        return $data;
    }

    /**
     * 检测字段是否为空
     * @param array $data
     * @param string $field
     * @param string $chinese
     * @param int $limit
     * @throws Exception
     * @author hsw
     */
    public static function checkRequireEmpty(array $data, string $field, string $chinese, int $limit = 255)
    {
        if (empty($data[$field])) {
            throw new Exception($chinese . '不得为空');
        }
        if (mb_strlen($data[$field]) > $limit) {
            throw new Exception($chinese . '限制在' . $limit . '位以内');
        }
    }

    /**
     * 检测字段是否被设置
     * @param array $data
     * @param string $field
     * @param string $chinese
     * @param int $limit
     * @throws Exception
     * @author hsw
     */
    public static function checkRequireIsset(array $data, string $field, string $chinese, int $limit = 255)
    {
        if (!isset($data[$field])) {
            throw new Exception($chinese . '不得为空');
        }
        if (mb_strlen($data[$field]) > $limit) {
            throw new Exception($chinese . '限制在' . $limit . '位以内');
        }
    }

    /**
     * 检测长度
     * @param array $data
     * @param string $field
     * @param string $chinese
     * @param int $limit
     * @return void
     * @throws Exception
     * @author hsw
     */
    public static function checkLength(array $data, string $field, string $chinese, int $limit = 255)
    {
        if (isset($data[$field]) && mb_strlen($data[$field]) > $limit) {
            throw new Exception($chinese . '限制在' . $limit . '位以内');
        }
    }

    /**
     * 检测手机号
     * @param $mobile
     * @return false|int
     * @author hsw
     */
    public static function checkMobile($mobile)
    {
        return preg_match("/^1[345789]\d{9}$/", trim($mobile));
    }

    /**
     * 状态码判断
     * @param array $result
     * @throws Exception
     * @author hsw
     */
    public static function checkCode(array $result)
    {
        if (!empty($result['code'])) {
            if ($result['code'] != 200) {
                throw new Exception($result['msg'] ?? '未知错误，请联系技术人员');
            }
        }

        throw new Exception('返回状态有误');
    }

    /**
     * 内部url地址请求
     * @param array $data
     * @param string $url
     * @author hsw
     * @throws
     */
    public static function inRequestUrl(array $data, string $url)
    {
        checkCode(json_decode(setCurl($data, $url), true));
    }

    /**
     * 根据需要的字段扩展数组
     * @param array $aimArray
     * @param array $expandFields
     * @param array $expandArray
     * @return array
     * @author hsw
     */
    public static function expandArray(array $aimArray, array $expandFields, array $expandArray = [])
    {
        foreach ($expandFields as $expandField) {
            if (isset($aimArray[$expandField])) {
                $expandArray[$expandField] = $aimArray[$expandField];
            }
        }

        return $expandArray;
    }

    /**
     * 根据需要设置的字段设置默认值
     * @param array $array
     * @param array $needSetFields
     * @param string $default
     * @return array
     * @author hsw
     */
    public static function setDefault(array $array, array $needSetFields, $default = '')
    {
        foreach ($needSetFields as $field) {
            if (!isset($array[$field])) $array[$field] = $default;
        }

        return $array;
    }

    /**
     * 格式化日期
     * @param $time
     * @param string $format
     * @return string
     * @author hsw
     */
    public static function formatTime($time, string $format = 'Y:m:d H:i:s') : string
    {
        return $time ? date($format, $time) : '';
    }

    /**
     * 接收参数
     * @param null $name
     * @param null $defaultValue
     * @param null $method get|post
     * @return array|mixed|null
     * @author hsw
     */
    public static function request($name = null, $defaultValue = null, $method = null)
    {
        $data = $_REQUEST;
        if ($method) {
            $data = $method == 'get' ? $_GET : $_POST;
        }

        if ($data) {
            $data = self::checkSecurity($data);
        }
        if ($name === null) {
            return $data;
        }

        return isset($data[$name]) ? $data[$name] : $defaultValue;
    }

    /**
     * 过滤传过来的参数
     * @param $data
     * @return array|void
     * @author hsw
     */
    public static function checkSecurity(&$data)
    {
        if (!is_array($data)) {
            $data = self::cleanJs($data);
        } else {
            foreach ($data as &$v) {
                if (is_array($v)) {
                    $v = self::checkSecurity($v);
                } else {
                    $v = self::cleanJs($v);
                }
            }
        }

        return $data;
    }

    /**
     * 过滤前端的标签
     * @param $string
     * @return string|string[]|null
     * @author hsw
     */
    public static function cleanJs($string)
    {
        if (is_string($string)) {
            if (trim($string)) $string = trim($string);
            $html = str_replace(['<?', '?>', '\''], '', $string);
            $pattern = [
                "'<script[^>]*?>.*?<\/script>'si",
                "'<style[^>]*?>.*?<\/style>'si",
                "'<frame[^>]*?>'si",
                "'<iframe[^>]*?>.*?<\/iframe>'si",
                "'<link[^>]*?>'si"
            ];

            return preg_replace($pattern, "", $html);
        }

        return $string;
    }

    /**
     * 导出(导出一个工作表，且无循环)
     * @param array $data 导出的二维数组
     * @param array $exportFields 导出的中文和英文字段 example  [
     *     ['fieldChinese' => '订单id', 'fieldEnglish' => 'pay_ordersn'],
     * ]
     * @param string $fileName 导出的文件名
     * @return void
     * @author hsw
     */
    public static function commonExport(array $data, array $exportFields, string $fileName)
    {
        $fileName = urlencode($fileName);
        header("Cache-Control: public");
        header("Pragma: public");
        header("Content-type:application/vnd.ms-excel ; charset=utf-8");
        header("Content-Disposition:attachment;filename={$fileName}.csv");
        header('Content-Type:APPLICATION/OCTET-STREAM');
        ob_start();

        //特殊字符转义
        $headChinese = implode(',', array_column($exportFields, 'fieldChinese')) . PHP_EOL;
        $headerStr = iconv("utf-8",'gbk', $headChinese);

        $fileStr = "";
        $englishFields = array_column($exportFields, 'fieldEnglish');
        if (!empty($data)) {
            foreach ($data as $v) {
                $export = array_values(expandArray($v, $englishFields));
                $fileStr .= implode(",", $export) . PHP_EOL;
            }
        }

        $fileStr = mb_convert_encoding($fileStr, 'gbk', 'utf-8');
        ob_end_clean();
        echo $headerStr;
        echo $fileStr;
        exit;
    }

    /**
     * 把返回的数据集转换成Tree
     * @param array $list 要转换的数据集
     * @param string $primaryKey 主键id
     * @param string $pid parent标记字段
     * @param string $child
     * @param int $root
     * @return array
     * @author hsw
     */
    public static function listToTree($list, string $primaryKey = 'id', string $pid = 'pid', string $child = 'child', $root = 0) {
        //创建Tree
        $tree = [];
        if (is_array($list)) {
            //创建基于主键的数组引用
            $refer = [];
            foreach ($list as $key => $data) {
                $refer[$data[$primaryKey]] = &$list[$key];
            }
            foreach ($list as $key => $data) {
                //判断是否存在parent
                $parentId = $data[$pid];
                if ($root == $parentId) {
                    $tree[] = &$list[$key];
                } else {
                    if (isset($refer[$parentId])) {
                        $parent = &$refer[$parentId];
                        $parent[$child][] = &$list[$key];
                    }
                }
            }
        }

        return $tree;
    }

    /**
     * 将树形图转成数组
     * @param $tree
     * @param string $child
     * @param bool $isFirst
     * @return array
     * @author hsw
     */
    public static function treeToList($tree, string $child = 'child', bool $isFirst = true)
    {
        if ($isFirst) {
            $list = [];
        }

        if (is_array($tree)) {
            foreach ($tree as $key => $val) {
                $children = $val[$child] ?? [];
                unset($val[$child]);
                $list[] = $val;
                if ($children) {
                    $list = array_merge($list, self::treeToList($children, $child, false));
                }
            }
        } else {
            $list[] = $tree;
        }

        return $list ?? [];
    }

    /**
     * 获取用户ip
     * @return mixed
     * @author hsw
     */
    public static function getUserIpAddr()
    {
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }

    /**
     * 根据列表组装子级，返回格式为[
     *      '8'//父级id
     *      => [1, 2, 3]//(子级ids)
     * ]
     * @param string $primaryKey
     * @param string $parentKey
     * @param array $cacheList
     * @param bool $includeSelf
     * @return array
     * @author hsw
     */
    public static function getChildren(string $primaryKey, string $parentKey, array $cacheList = [], bool $includeSelf = false) : array
    {
        static $data = [];
        if ($cacheList) {
            $list = combinationKeyValue($parentKey, $cacheList, $primaryKey, true);
            foreach ($list as $parent => $children) {
                foreach ($children as $id) {
                    $data[$parent] = array_unique(
                        array_merge(
                            $data[$parent] ?? [],
                            self::getChildrenIds($id, $list)
                        )
                    );
                }
            }
        }
        if ($includeSelf) {
            foreach (array_column($cacheList, $primaryKey) as $self) {
                if (!isset($data[$self])) $data[$self] = [];
                $data[$self][] = $self;
            }
        }

        return $data;
    }

    /**
     * 根据id获取子级所有id，cacheArray格式必须为[
     *      '8'//父级id
     *      => [1, 2, 3]//(子级ids)
     * ]
     * @param int $parentId
     * @param array $cacheArray
     * @return array
     * @see getChildren()
     * @author hsw
     */
    public static function getChildrenIds(int $parentId, array $cacheArray = [])
    {
        $ids[] = $parentId;
        $cacheList = getValueByKey($parentId, $cacheArray, '', []);
        if ($cacheList) {
            foreach ($cacheList as $id) {
                $ids[] = $id;
                $ids = array_merge($ids, self::getChildrenIds($id, $cacheArray));
            }
        }

        return $ids;
    }

    /**
     * 基础的条件
     * @param string $tableName
     * @return array
     * @author hsw
     */
    public static function baseWhere(string $tableName = '')
    {
        $field = $tableName ? $tableName . '.is_delete' : 'is_delete';

        return [$field, '=', 0];
    }

    /**
     * 下划线转驼峰
     * 思路：
     * step1.原字符串转小写,原字符串中的分隔符用空格替换,在字符串开头加上分隔符
     * step2.将字符串中每个单词的首字母转换为大写,再去空格,去字符串首部附加的分隔符.
     * @param $unCamelizeWords
     * @param string $separator
     * @return string
     * @author hsw
     */
    public static function camelize(string $unCamelizeWords, string $separator = '_') : string
    {
        $unCamelizeWords = $separator . str_replace($separator, " ", strtolower($unCamelizeWords));

        return ltrim(str_replace(" ", "", ucwords($unCamelizeWords)), $separator);
    }

    /**
     * 驼峰命名转下划线命名,
     * 思路:小写和大写紧挨一起的地方,加上分隔符,然后全部转小写
     * @param $camelCaps
     * @param string $separator
     * @return string
     * @author hsw
     */
    public static function unCamelize(string $camelCaps, string $separator = '_') : string
    {
        return strtolower(
            preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $camelCaps)
        );
    }

    /**
     * 是否有大写字母
     * @param string $str
     * @return false|int
     * @author hsw
     */
    public static function hasUppercase(string $str)
    {
        return preg_match('/[A-Z]+/', $str);
    }
}