<?php
/**
 * InStockNotifier
 *
 * @author Govind Kumar
 * @version 1.0.0
 * @package InStockNotifier/Classes
 */

namespace InStockNotifier;

use WC_Email;
use WC_Product;

/**
 * In-Stock Notifier - WooCommerce Plugin
 * Copyright (C) 2017 Govind Kumar <gkprmr@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as published
 * by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

defined('ABSPATH') or die;

if (!class_exists('WSN_Email')) {

    /**
     * Class Email
     * @package InStockNotifier
     */
    class WSN_Email extends WC_Email
    {


        private $users = [];
        private WC_Product $product;

        /**
         * WSN_Email constructor.
         *
         * @since 1.0.0
         */
        public function __construct()
        {

            $this->id = 'instock_notifier_users_mailout';
            $this->title = __('In-Stock Notifier', 'tmsm-woocommerce-stocknotifier' );
            $this->description = __('When a product is Out-of-Stock and when it comes In-Stock again, this email is sent to all users registered in the waiting list for that product.', 'tmsm-woocommerce-stocknotifier' );
            $this->heading = __('{product_title} now back in stock at {blogname}', 'tmsm-woocommerce-stocknotifier' );
            $this->subject = __('A product you are waiting for is back in stock', 'tmsm-woocommerce-stocknotifier' );
            $this->template_base = WSN_EMAIL_TEMPLATE_PATH;
            $this->template_html = 'wsn-email-template.php';
            $this->template_plain = 'plain/wsn-email-template.php';
            $this->customer_email = true;

            // Add action to send the email.
            add_action('send_wsn_email_mailout', array($this, 'trigger'), 10, 2);

            // WC_Email Constructor.
            parent::__construct();
        }

        /**
         * Trigger Email function
         *
         * @access public
         *
         * @param mixed $users Waitlist users array.
         * @param integer $product_id Product id.
         *
         * @return void
         * @since 1.0.0
         *
         */
        public function trigger($users, $product_id)
        {

            $this->users = $users;

            // get the product
            $this->product = wc_get_product($product_id);

	        // return
	        if ( ! $this->product || ! $this->is_enabled() ) {
		        return;
	        }

            // Replace product_title in email template.
            $this->placeholders = array(
                '{product_title}' =>  ($this->product->product_data['title'] ?? $this->product->get_title() )
            );

            // build header
            $header = $this->get_headers() . "\r\n";

            // send email
            $response = $this->send($this->get_from_address(), $this->get_subject(), $this->get_content(), $header, $this->get_attachments());

            // Return true in wsn_email_send_response.
            if ($response) {
                add_filter('wsn_email_send_response', '__return_true');
            }

        }

        /**
         * Get the html content of the email.
         *
         * @access public
         * @return string
         * @since 1.0.0
         */
        public function get_content_html()
        {

            ob_start();

            // Get Woo commerce template.
            wc_get_template(
                $this->template_html,
                array(
                    'product_title' =>  ($this->product->product_data['title'] ?? $this->product->get_title() ),
                    'product_link' => get_permalink($this->product->get_id()),
                    'email_heading' => $this->get_heading(),
                ),
                false,
                $this->template_base
            );

            return ob_get_clean();
        }

        /**
         * Get the plain content of the email.
         *
         * @access public
         * @return string
         * @since 1.0.0
         */
        public function get_content_plain()
        {

            ob_start();

            wc_get_template($this->template_plain, array(
                'order' => $this->object,
                'email_heading' => $this->get_heading(),
            ));

            return ob_get_clean();
        }

        public function get_headers()
        {
            $header = parent::get_headers();
	        $header .= 'bcc :' . implode( ",", $this->users ) . "\r\n";

	        return apply_filters('woocommerce_email_headers', $header, $this->id, $this->object, $this);
        }


    }
}
