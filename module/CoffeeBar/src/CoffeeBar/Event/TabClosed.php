<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace CoffeeBar\Event ;

use DateTime;

class TabClosed
{
    protected $id;
    protected $amountPaid; // double
    protected $orderValue; // double
    protected $tipValue; // double
    protected $date ;// DateTime

    function getId() {
        return $this->id;
    }

    function getAmountPaid() {
        return $this->amountPaid;
    }

    function getOrderValue() {
        return $this->orderValue;
    }

    function getTipValue() {
        return $this->tipValue;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setAmountPaid($amountPaid) {
        $this->amountPaid = $amountPaid;
    }

    function setOrderValue($orderValue) {
        $this->orderValue = $orderValue;
    }

    function setTipValue($tipValue) {
        $this->tipValue = $tipValue;
    }
    
    public function getDate() {
        return $this->date;
    }

    public function setDate(DateTime $date) {
        $this->date = $date;
    }
}