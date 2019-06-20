<?php
namespace Unifreak\Fetcher\Fetched;

abstract class Fetched
{
    protected $api = [];

    /**
     * Whether api respond ok
     *
     * @author FangHao <email:fanghao@xin.com, phone:15369997084>
     *
     * @return bool
     */
    abstract public function ok();

    /**
     * Response code
     *
     * @author FangHao <email:fanghao@xin.com, phone:15369997084>
     *
     * @return integer
     */
    abstract public function code();

    /**
     * Response message
     *
     * @author FangHao <email:fanghao@xin.com, phone:15369997084>
     *
     * @return string
     */
    abstract public function message();

    /**
     * Response data
     *
     * @author FangHao <email:fanghao@xin.com, phone:15369997084>
     *
     * @return array
     */
    abstract public function data();
}
