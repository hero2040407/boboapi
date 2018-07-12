<?php
namespace  BBExtend\common;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Events\QueryExecuted;

/**
 * sql监听类，记录sql日志。
 */
class SqlListener implements Dispatcher
{
    /**
     * Fire an event and call the listeners.
     *
     * @param  string|object  $event
     * @param  mixed  $payload
     * @param  bool  $halt
     * @return array|null
     */
    public function fire($event, $payload = [], $halt = false){
        
        if ($event instanceof  QueryExecuted) {
            $sql=$event->sql;
            if ($event->bindings) {
                foreach($event->bindings as $v) {
                    $sql = preg_replace('/\\?/', "'". addslashes( $v)."'", $sql,1);
                }
            }
            $log = \BBExtend\Sys::getsql_log();
            $sql = \SqlFormatter::format($sql,false);
            $log->info($sql);
        }
        
    }

    public function firing(){}
    public function forget($event){}
    public function forgetPushed(){}
    public function listen($events, $listener, $priority = 0){}
    public function hasListeners($eventName){}
    public function push($event, $payload = []){}
    public function subscribe($subscriber){}
    public function until($event, $payload = []){}
    public function flush($event){}
    
}

