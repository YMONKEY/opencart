<?php
use Test\Orders\OrdersCreateTest;

class ControllerTestpayPaypal extends Controller {

    public function index() {
        $Order = new OrdersCreateTest();
        $Order->testOrdersCreateRequest();
    }

}