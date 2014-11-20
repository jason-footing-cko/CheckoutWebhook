<?php
interface  Controller_Interface
{
    public function getData();
    public function createCharge($config,$order_info);
}