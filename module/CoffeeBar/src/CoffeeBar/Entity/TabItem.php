<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace CoffeeBar\Entity ;

class TabItem
{
    protected $menuNumber;
    protected $description;
    protected $price;

    public function getMenuNumber() {
        return $this->menuNumber;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getPrice() {
        return $this->price;
    }

    public function setMenuNumber($menuNumber) {
        $this->menuNumber = $menuNumber;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function setPrice($price) {
        $this->price = $price;
    }

}