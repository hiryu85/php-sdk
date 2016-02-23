<?php
require_once "konduto.php";
require_once "vendor/autoload.php";
use Konduto\Models as KondutoModels;
use Konduto\Exceptions as KondutoExceptions;

class ModelsTest extends \PHPUnit_Framework_TestCase {

    public function testCustomer() {
        $customer = array(
            "id"         => "Customer-n03",
            "name"       => "Hiroyuki Endo",
            "email"      => "endo.hiroyuki@yahoo.jp",
            "tax_id"     => "XJ0000JX",
            "phone1"     => "151520030",
            "phone2"     => "151721295",
            "is_new"     => true,
            "vip"        => true,
            "dob"        => "1991-05-12",
            "created_at" => "2014-12-01"
        );

        $customerObj = new KondutoModels\Customer($customer);

        $this->assertTrue($customerObj->is_valid(), 'Is not a valid object');
        $this->assertEquals($customerObj->id(), $customer['id'], 'id');
        $this->assertEquals($customerObj->name(), $customer['name'], 'name');
        $this->assertEquals($customerObj->email(), $customer['email'], 'email');
        $this->assertEquals($customerObj->tax_id(), $customer['tax_id'], 'tax_id');
        $this->assertEquals($customerObj->phone1(), $customer['phone1'], 'phone1');
        $this->assertEquals($customerObj->phone2(), $customer['phone2'], 'phone2');
        $this->assertEquals($customerObj->is_new(), $customer['is_new'], 'is_new');
        $this->assertEquals($customerObj->vip(), $customer['vip'], 'vip');
        $this->assertEquals($customerObj->dob(), $customer['dob'], 'dob');
        $this->assertEquals($customerObj->created_at(), $customer['created_at'], 'created_at');
    }

    public function testGetSetObject() {
        $order = array(
            "id"           => "Order-18462",
            "total_amount"  => 1367.00,
            "customer" => array(
                "id"     => "Customer-n2936",
                "name"   => "Steve Matteson",
                "email"  => "matesson@typeface.com",
                "tax_id" => "SJ183650"
            )
        );

        $orderObj = new KondutoModels\Order($order);
        $this->assertInstanceOf("\Konduto\Models\Customer",
             $orderObj->customer(), "Customer() should return a Customer object");
    }


    public function testGetErrors1() {
        $customer = array(
            "id"     => "Customer-n2936",
            "name"   => "José da Silva",
            "tax_id" => "SJ183650",
            "vip" => 25
        );

        $custObj = new KondutoModels\Customer($customer);

        $this->assertFalse($custObj->is_valid(), "This customer shouldn't be valid.");
        $errors = $custObj->get_errors();

        $this->assertArrayHasKey("vip", $errors);
        $this->assertArrayHasKey("email", $errors);
        $this->assertNull($errors["email"]);
        $this->assertEquals(25, $errors["vip"]);
    }


    public function testGetErrors2() {
        $order = array(
            "id"           => "Order-18462",
            "customer" => array(
                "id"     => "Customer-n2936",
                "name"   => "Steve Matteson",
                "tax_id" => "SJ183650"
            ),
            "payment" => array(
                array(
                    "type" => "credit",
                    "bin" => "490172",
                    "last4"=> "0012",
                    "expiration_date" => "072015"
                )
            )
        );

        $orderObj = new KondutoModels\Order($order);

        $this->assertFalse($orderObj->is_valid(), "This order shouldn't be valid.");

        $errors = $orderObj->get_errors();

        $this->assertArrayHasKey("customer", $errors);
        $this->assertArrayHasKey("payment", $errors);
        $this->assertArrayHasKey("total_amount", $errors);
    }

    /**
     * Tests for the creation of an order object using a big array
     * @depends                 testCustomer
     */
    public function testFullOrderWithArray() {
        $o = new KondutoModels\Order(array(
            "id"           => "Order-90125",
            "total_amount"  => 312.71,
            "ip"           => "221.102.39.19",
            "customer"     => array(
                "id"    => "Customer n03",
                "name"  => "Hiroyuki Endo",
                "email" => "endo.hiroyuki@yahoo.jp"
            ),
            "payment" => array(
                array(
                    "type" => "credit",
                    "bin" => "490172",
                    "last4"=> "0012",
                    "expiration_date" => "072015",
                    "status" => "approved"
                ),
                array(
                    "type" => "credit",
                    "status" => "declined",
                    "bin" => "490231",
                    "last4"=> "0231",
                    "expiration_date" => "082016"
                )
            ),
            "billing" => array(
                "name" => "Mary Jane",
                "address1" => "123 Main St.",
                "address2" => "Apartment 4",
                "city" => "New York City",
                "state" => "NY",
                "zip" => "10460",
                "country" => "US"
            ),
            "shipping" => array(
                "name" => "Mary Jane",
                "address1" => "123 Main St.",
                "address2" => "Apartment 4",
                "city" => "New York City",
                "state" => "NY",
                "zip" => "10460",
                "country" => "US"
            ),
            "shopping_cart" => array(
                array(
                    "sku" => "9919023",
                    "product_code" => 1231,
                    "category" => 201,
                    "name" => "Green T-Shirt",
                    "description" => "Male Green T-Shirt V Neck",
                    "unit_cost" => 1999.99,
                    "quantity" => 1
                ),
                array(
                    "sku" => "0017273",
                    "category" => 1231,
                    "name" => "Yellow Socks",
                    "description" => "Pair of Yellow Socks",
                    "unit_cost" => 29.90,
                    "quantity" => 2,
                    "discount" => 5.00
                )
            ),
            "seller" => array(
                "id"         => "Loja-A023",
                "name"       => "Loja de especiarias do Chileno",
                "created_at" => "2014-12-15"
            )
        ));

        if (!$o->is_valid()) {
            ob_start();
            var_dump($o->get_errors());
            $errors = ob_get_contents();
            ob_end_clean();
            $this->fail("The test failed because there were errors in the order: \n" . $errors);
        }
        else {
            $this->assertTrue(TRUE);
        }
    }

    public function test_instances() {
        $mary_jane_address = array(
            "name" => "Mary Jane",
            "address1" => "123 Main St.",
            "address2" => "Apartment 4",
            "city" => "New York City",
            "state" => "NY",
            "zip" => "10460",
            "country" => "US"
        );

        $o = new KondutoModels\Order(array(
            "id"           => "Order-90125",
            "total_amount"  => 312.71,
            "ip"           => "221.102.39.19",
            "customer"     => array(
                "id"    => "Customer n03",
                "name"  => "Hiroyuki Endo",
                "email" => "endo.hiroyuki@yahoo.jp"
            ),
            "payment" => array(
                array(
                    "type" => "credit",
                    "bin" => "490172",
                    "last4"=> "0012",
                    "expiration_date" => "072015",
                    "status" => "approved"
                ),
                array(
                    "type" => "boleto"
                )
            ),
            "billing" => $mary_jane_address,
            "shipping" => $mary_jane_address,
            "shopping_cart" => array(
                array(
                    "sku" => "9919023",
                    "product_code" => 1231,
                    "category" => 201,
                    "name" => "Green T-Shirt",
                    "description" => "Male Green T-Shirt V Neck",
                    "unit_cost" => 1999.99,
                    "quantity" => 1
                )
            ),
            "seller" => array(
                "id"         => "Loja-A023",
                "name"       => "Loja de especiarias do Chileno",
                "created_at" => "2014-12-15"
            )
        ));

        $this->assertInstanceOf("Konduto\Models\Customer", $o->customer());
        $this->assertInstanceOf("Konduto\Models\CreditCard", $o->payment()[0]);
        $this->assertInstanceOf("Konduto\Models\Boleto", $o->payment()[1]);
        $this->assertInstanceOf("Konduto\Models\Address", $o->billing());
        $this->assertInstanceOf("Konduto\Models\Address", $o->shipping());
        $this->assertInstanceOf("Konduto\Models\Item", $o->shopping_cart()[0]);
        $this->assertInstanceOf("Konduto\Models\Seller", $o->seller());
    }


    /**
     * Tests for an Order containing an error for an illegal ID property.
     */
    public function testPatternValidationOrderID() {
        $o = new KondutoModels\Order();

        // Set an ID that don't respect pattern (contains space);
        $o->id("Pedido 00001");
        $this->assertTrue(array_key_exists('id', $o->get_errors()), "The 'id' key should be present in errors.");

        // Now set an ID that respect pattern, and check if the error's gone.
        $o->id("Pedido00001");
        $this->assertFalse(array_key_exists('id', $o->get_errors()), "Now the 'id' key shouldn't be present.");
    }

    /**
     * Test pattern for Order ip.
     * @depends         testPatternValidationOrderID
     */
    public function testValidationOrderIP() {
        $o = new KondutoModels\Order();
        // Invalid IP.
        $o->ip("192.168.0.256");
        $this->assertTrue(array_key_exists('ip', $o->get_errors()), "The 'ip' key should be present in errors.");

        // Now there should be no error.
        $o->ip("192.168.0.255");
        $this->assertFalse(array_key_exists('ip', $o->get_errors()), "Now the 'ip' key shouldn't be present.");
    }

    public function testAddress() {
        $addr = new KondutoModels\Address();
        $addr->address1("Via Volvera, 14");
        $addr->address2("Appartamento 6");
        $addr->city("Torino");
        $addr->state("Piemonte");
        $addr->country("IT");
        $addr->zip("10141");

        $this->assertTrue($addr->is_valid(), "There should be no errors.");
        // All the fields should be not null.
        $this->assertEquals($addr->address1(), "Via Volvera, 14");
        $this->assertEquals($addr->address2(), "Appartamento 6");
        $this->assertEquals($addr->city(), "Torino");
        $this->assertEquals($addr->state(), "Piemonte");
        $this->assertEquals($addr->country(), "IT");
        $this->assertEquals($addr->zip(), "10141");
    }

    public function testShoppingCart() {
        $item = new KondutoModels\Item(array(
            "sku"           => "9919023",
            "product_code"   => "123456789999",
            "category"      => 201,
            "name"          => "Green T-Shirt",
            "description"   => "Male Green T-Shirt V Neck",
            "unit_cost"      => 199.99,
            "quantity"      => 1
        ));

        $item2 = new KondutoModels\Item(array(
            "sku"           => "0017273",
            "category"      => 202,
            "name"          => "Yellow Socks",
            "description"   => "Pair of Yellow Socks",
            "unit_cost"     => 29.99,
            "discount"      => 5.0,
            "quantity"      => 2
        ));

        $o = new KondutoModels\Order();
        $o->add_item($item);
        $o->add_item($item2);

        // All the fields should be not null.
        $this->assertNotNull($item->sku());
        $this->assertNotNull($item->product_code());
        $this->assertNotNull($item->category());
        $this->assertNotNull($item->name());
        $this->assertNotNull($item->description());
        $this->assertNotNull($item->quantity());
        $this->assertNotNull($item2->discount());
        $this->assertCount(2, $o->shopping_cart());
    }

    public function testNavigation() {
        $navInfo = array(
            "referrer"              => "http://www.google.com?q=buy+shirt",
            "session_time"          => 12,
            "time_site_1d"          => 13,
            "new_accounts_1d"       => 0,
            "password_resets_1d"    => 1,
            "sales_declined_1d"     => 2,
            "sessions_1d"           => 3,
            "time_site_7d"          => 4,
            "time_per_page_7d"      => 5,
            "new_accounts_7d"       => 6,
            "password_resets_7d"    => 7,
            "checkout_count_7d"     => 8,
            "sales_declined_7d"     => 9,
            "sessions_7d"           => 10,
            "time_since_last_sale"  => 11
        );

        $navModel = new KondutoModels\Navigation($navInfo);

        $this->assertContains($navModel->session_time(), $navInfo);
        $this->assertContains($navModel->referrer(), $navInfo);
        $this->assertContains($navModel->time_site_1d(), $navInfo);
        $this->assertContains($navModel->new_accounts_1d(), $navInfo);
        $this->assertContains($navModel->password_resets_1d(), $navInfo);
        $this->assertContains($navModel->sales_declined_1d(), $navInfo);
        $this->assertContains($navModel->time_site_7d(), $navInfo);
        $this->assertContains($navModel->time_per_page_7d(), $navInfo);
        $this->assertContains($navModel->new_accounts_7d(), $navInfo);
        $this->assertContains($navModel->password_resets_7d(), $navInfo);
        $this->assertContains($navModel->checkout_count_7d(), $navInfo);
        $this->assertContains($navModel->sales_declined_7d(), $navInfo);
        $this->assertContains($navModel->sessions_7d(), $navInfo);
        $this->assertContains($navModel->time_since_last_sale(), $navInfo);
    }

    public function testBoleto1() {
        // Has a valid date
        $boletoObj = new KondutoModels\Boleto(
            array("expiration_date" => "2014-12-05")
        );

        $this->assertTrue($boletoObj->is_valid(), "boleto->is_valid should ".
            "be true here because the expiration_date is okay.");

        // Has an invalid date
        $boletoObj = new KondutoModels\Boleto(
            array("expiration_date" => "8917236")
        );
        $this->assertFalse($boletoObj->is_valid(), "boleto->is_valid should ".
            "be false here because the expiration_date is not okay.");
    }

    public function testBoleto2() {
        // Has a valid date
        $boletoObj = new KondutoModels\Boleto();
        $boletoObj->expiration_date("2014-12-05");

        $boletoArray = array(
            "type" => "boleto",
            "expiration_date" => "2014-12-05"
        );

        $this->assertEquals($boletoArray, $boletoObj->to_array(),
            "Both provided and generated arrays should be equal.");
    }

    public function testToArray() {
        $order = array(
            "id"           => "Order-18462",
            "total_amount"  => 1367.00,
            "customer" => array(
                "id"     => "Customer-n2936",
                "name"   => "Steve Matteson",
                "email"  => "matesson@typeface.com",
                "tax_id" => "SJ183650"
            ),
            "payment" => array(
                array(
                    "type" => "credit",
                    "bin" => "490172",
                    "last4"=> "0012",
                    "expiration_date" => "072015",
                    "status" => "approved"
                ),
                array(
                    "type" => "credit",
                    "bin" => "490231",
                    "last4"=> "0231",
                    "expiration_date" => "082016",
                    "status" => "declined"
                )
            )
        );

        $orderObj = new KondutoModels\Order($order);

        $this->assertInstanceOf("Konduto\Models\Customer",
                $orderObj->customer(), "Customer obj should
                        be of Customer instance");

        $payment_arr = $orderObj->payment();

        $this->assertInstanceOf("Konduto\Models\Payment",
                $payment_arr[0], "Payment obj should
                        be of Payment instance");

        $this->assertInstanceOf("Konduto\Models\Payment",
                $payment_arr[1], "Payment obj should
                        be of Payment instance");

        $this->assertEquals($order, $orderObj->to_array(),
                "Array provided should be equals to the array ".
                "generated.");
    }

    public function testCreatedAt() {
        $order = new KondutoModels\Order();
        $order->set(array("created_at" => "2014-12-09 12:26:40"));
        $this->assertEquals($order->created_at(), "2014-12-09 12:26:40");
    }

    public function testPaymentInstantiate() {
        $credit_card = KondutoModels\Payment::instantiate(array(
            "type"            => "credit",
            "bin"             => "490172",
            "last4"           => "0012",
            "expiration_date" => "072015",
            "status"          => "approved"
        ));

        $boleto = KondutoModels\Payment::instantiate(array(
            "type"            => "boleto",
            "expiration_date" => "2014-12-11"
        ));

        $this->assertInstanceOf("Konduto\Models\CreditCard", $credit_card);
        $this->assertInstanceOf("Konduto\Models\Boleto", $boleto);
    }

}
