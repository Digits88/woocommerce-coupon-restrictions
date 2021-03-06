<?php

namespace DevPress\WooCommerce\CouponRestrictions\Test\Integration;

use WC_Helper_Customer;
use WC_Helper_Coupon;
use WC_Coupon_Restrictions_Validation;

class Existing_Customer_Coupon_Test extends \WP_UnitTestCase {

	/**
	 * Tests that a coupon with a new customer restriction cannot be applied
	 * to an existing customer.
	 */
	public function test_new_customer_restriction_type() {

		// Create a customer.
		$customer = \WC_Helper_Customer::create_customer();
		$customer_id = $customer->get_id();
		wp_set_current_user( $customer_id );

		// Create coupon.
		$coupon = \WC_Helper_Coupon::create_coupon();
		update_post_meta( $coupon->get_id(), 'customer_restriction_type', 'existing' );

		// Adds a new customer restricted coupon.
		// This should return false because customer hasn't yet purchased.
		$this->assertFalse( WC()->cart->add_discount( $coupon->get_code() ) );

		// Verifies 0 coupons have been applied to cart.
		$this->assertEquals( 0, count( WC()->cart->get_applied_coupons() ) );

		// Remove the coupons from the cart.
		WC()->cart->empty_cart();
		WC()->cart->remove_coupons();

		// Creates an order and applies it to new customer.
		// This makes the customer a returning customer.
		$order = \WC_Helper_Order::create_order( $customer_id );
		$order->set_billing_email( $customer->get_email() );
		$order->set_status( 'completed' );
		$order->save();

		// Adds coupon, this should now return true.
		$this->assertTrue( WC()->cart->add_discount( $coupon->get_code() ) );

		// Verifies 1 coupon has been applied.
		$this->assertEquals( 1, count( WC()->cart->get_applied_coupons() ) );

		// Clean up.
		WC()->cart->empty_cart();
		WC()->cart->remove_coupons();
		$order->delete();
		$coupon->delete();
		$customer->delete();

	}

}
