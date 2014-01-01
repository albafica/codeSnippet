<?php

/**
 * Description of DbMysql
 *  mysql基本操作
 * @author albafica
 * @todo 添加主从服务器配置
 */
class DbMysql {

    protected $config = array();        //默认数据库连接信息
    protected $linkID = array();        //数据库连接ID
    protected $pConnected = false;      //长连接标志位
    protected $dbCharset = 'UTF8';     //数据库默认存取编码
    protected $connected = false;       //数据库连接成功标志位
    protected $sqlStr = '';             //执行的sql
    protected $_linkedID = null;        //当前连接标志
    protected $queryID = null;          //sql执行结果资源标志
    protected $cluster = false;         //定义主从服务器
    protected $error = '';              //数据库操纵错误信息
    protected $numRows = 0;             //mysql查询结果返回条数
    protected $lastInsID = null;           //最近一条插入记录的id

    /**
     * @description 构造函数，读取数据库配置信息
     * @access public
     * @param array $config     数据库配置信息数组
     */

    public function __construct($config) {
        if (!empty($config)) {
            $this->config = $config;
            if (empty($this->config['params'])) {
                $this->config['params'] = '';
            }
        }
    }

    /**
     * @description 根据配置信息连接数据库
     * @access public
     * @param array $config 配置信息数组
     * @param array $linkNum    连接标志
     * @return source   连接资源标志
     * @throws Exception    数据库连接失败，抛出异常
     */
    public function connect($config = '', $linkNum = 0) {
        if (!isset($this->linkID[$linkNum])) {
            if (empty($config)) {
                $config = $this->config;
            }
            //处理带端口号的主机地址
            $host = $config['hostname'] . ($config['hostport'] ? ':' . $config['hostport'] : '');
            $pconnect = !empty($config['params']['pconnect']) ? $config['params']['pconnect'] : $this->pConnected;
            if ($pconnect) {
                $this->linkID[$linkNum] = mysql_pconnect($host, $config['username'], $config['password']);
            } else {
                //定义mysql连接的第三个参数为true，每次开启新的连接,即每一个linkNum创建一个连接，即使这些连接完全一致
                $this->linkID[$linkNum] = mysql_connect($host, $config['username'], $config['password'], TRUE);
            }
            if (!$this->linkID[$linkNum] || (!empty($config['database']) && !mysql_select_db($config['database'], $this->linkID[$linkNum]))) {
                throw new Exception('数据库连接失败');
            }
            //定义数据库存储编码
            mysql_query("SET NAMES '" . (!empty($config['params']['charset']) ? $config['params']['charset'] : $this->dbCharset ) . "'", $this->linkID[$linkNum]);
        }
        $this->connected = true;
        return $this->linkID[$linkNum];
    }

    /**
     * @desctiption 初始化服务器连接,支持服务器集群
     */
    public function initConnect() {
        if ($this->cluster) {
            //配置主从服务器，目前缺省
        } else {
            if (!$this->connected) {
                $this->_linkedID = $this->connect();
            }
        }
    }

    /**
     * @description 执行查询并返回数据集
     * @param string $sql   查询的sql
     * @return mixed    查询的结果集或者false
     */
    public function select($sql) {
        $this->queryID = $this->query($sql);
        if (false === $this->queryID) {
            $this->error();
            return false;
        }
        $this->numRows = mysql_num_rows($this->queryID);
        return $this->getAll();
    }

    /**
     * 执行语句并返回受影响行数
     * @access public
     * @param string $str  sql指令
     * @return integer|false
     */
    public function execute($sql) {
        $result = $this->query($sql);
        if (false === $result) {
            $this->error();
            return false;
        }
        $this->numRows = mysql_affected_rows($this->_linkedID);
        $this->lastInsID = mysql_insert_id($this->_linkedID);
        return $this->numRows;
    }

    /**
     * 获得所有的查询数据
     * @access private
     * @return array
     */
    private function getAll() {
        //返回数据集
        $result = array();
        if ($this->numRows > 0) {
            while ($row = mysql_fetch_assoc($this->queryID)) {
                $result[] = $row;
            }
            mysql_data_seek($this->queryID, 0);
        }
        return $result;
    }

    /**
     * @description 执行sql语句，单独辟为方法，为了debug使用
     * @param string $sql       待执行的sql语句
     * @param source $linkId    数据库连接标志
     * @return source           查询结果集标志
     */
    private function query($sql) {
        $this->initConnect();
        if (!$this->_linkedID) {
            return false;
        }
        $this->sqlStr = $sql;
        if ($this->queryID) {
            $this->free();
        }
        return mysql_query($sql, $this->_linkedID);
    }

    /**
     * @description 释放sql查询结果集
     */
    public function free() {
        mysql_free_result($this->queryID);
        $this->queryID = NULL;
    }

    /**
     * 关闭数据库
     * @access public
     * @return void
     */
    public function close() {
        if ($this->_linkID) {
            mysql_close($this->_linkID);
        }
        $this->_linkID = null;
    }

    /**
     * @description 记录mysql出错信息
     * @return string   mysql的错误信息
     */
    public function error() {
        $this->error = mysql_errno() . ':' . mysql_error($this->_linkedID);
        if ('' !== $this->sqlStr) {
            $this->error .= "\r\n[SQL语句]" . $this->sqlStr;
        }
        return $this->error;
    }

}
