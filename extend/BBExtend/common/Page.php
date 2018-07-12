<?php

/**
 * 本类是分页对象select适配器的子类,也是select按条件分页的父类
 *
 * @author 谢烨
 */
abstract class Public_Page extends Zend_Paginator_Adapter_DbSelect
{
    protected  $cond_all;  //总条件，含cond,order,扩展列
    protected  $cond; //具体的条件
    
    protected  $_order;
    protected  $_expand_columns;
    
    private $select;
    
    /**
     * 构造方法
     * 
     * @param array $arr 数组，要求必须有一个键cond，值为查询条件，为空允许
     */
    public function __construct(array $arr=array('cond' => array()))
    {
        if (!array_key_exists('cond', $arr)) {
            throw new Zend_Exception('条件cond不存在');
        }
    //  debug($arr)  ;
        //对条件充值
        $this->cond_all = $arr;
        $this->cond = $arr['cond'];
        
        if (array_key_exists('order', $arr)) {
            $this->setOrder($arr['order']);
        }
        if (array_key_exists('expand_columns', $arr)) {
            $this->setExpandColumns($arr['expand_columns']);
        }elseif (array_key_exists('columns', $arr)){
            $this->setExpandColumns($arr['columns']);
        }
    }
     
    /**
     * Returns the total number of rows in the result set.
     *
     * @return integer
     */
    public function count()
    {
        if ($this->_rowCount === null) {
            $select = $this->getCountSelect();
            $select->from(null, 'count(*)');
            $this->_rowCount = Sys::get_container_db()->fetchOne($select);
        }
        return $this->_rowCount;
    }
    
    /**
     * 设置结果集的排序
     * 
     * @param string $order sql语句中的排序，最好带上表名，如member.id asc
     */
    public function setOrder($order)
    {
        $this->_order = $order;
    }
    
    /**
     * 得到结果集的排序
     * 
     */
    public function getOrder()
    {
        return $this->_order;
    }
    
    /**
     * 设置扩展列
     * 
     * @param array $columns 用户想要在结果集中获得的字段，但不是本表的固有列
     */
    public function setExpandColumns($columns)
    {
        if (is_array($columns)) {
            $this->_expand_columns = $columns;
        } else {
            $this->_expand_columns = array($columns);
        }
    }
    
    /**
     * 返回扩展列
     * 
     * @return array 扩展列
     */
    public function getExpandColumns()
    {
        return $this->_expand_columns;
    }
    
    public function hasExpand($name)
    {
        $arr = $this->getExpandColumns();
     // debug($arr);  
        if (is_array($arr)) {
            if ( in_array($name, $arr ) ) {
                return true;
            }
        }
        return false;
    }
    
   
}//end class
