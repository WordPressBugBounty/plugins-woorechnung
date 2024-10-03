<?php

if ( ! defined('ABSPATH') ) {
    exit;
}

if ( ! class_exists( 'FP_Email_Customer_Deliver_Invoice', false ) ):

    final class FP_Email_Customer_Deliver_Invoice extends WC_Email
    {
        /**
         * @var FP_Email_Handler $email_handler
         */
        protected $email_handler;

        /**
         * FP_EmailInvoice constructor.
         *
         * @param  FP_Plugin $plugin
         * @param  FP_Settings $settings
         * @param  FP_Email_Handler $email_handler
         */
        function __construct( $plugin, $settings, $email_handler )
        {
            $this->email_handler = $email_handler;

            // Email slug we can use to filter other data.
            $this->id          = 'fakturpro_email_customer_deliver_invoice';
            $this->title       = __( 'Deliver invoice', 'fakturpro' );
            $this->description = __( 'A manual email to send out invoices to the customer.', 'fakturpro' );

            // Placeholders
            $this->placeholders = array(
                '{order_date}'              => '',
                '{order_number}'            => '',
            );
            $placeholders = $plugin->get_invoice_placeholder_variables();
            $placeholders = $plugin->array_map_assoc(
                function ( $key, $value ) {
                    return [ $key, '' ];
                },
                $placeholders
            );
            $this->placeholders = array_merge( $this->placeholders, $placeholders );

            // For admin area to let the user know we are sending this email to customers.
            $this->customer_email = true;

            // Template paths.
            $this->template_html  = 'emails/template-fp-email-customer-deliver-invoice.php';
            $this->template_plain = 'emails/plain/template-fp-email-customer-deliver-invoice.php';
            $this->template_base  = $plugin->get_path( 'templates/' );

            // Triggers for this email.
            add_action( 'fakturpro_email_customer_deliver_invoice', array( $this, 'trigger' ), 10, 3 );

            parent::__construct();
        }

        /**
         * Trigger the sending of this email.
         *
         * @param  int $order_id The order ID
         * @param  FP_Order_Adapter|WC_Order|bool $order Order object.
         * @param  array<string> $attachments
         * @return void
         */
        public function trigger( $order_id, $order = false, $attachments = array() )
        {
            $this->setup_locale();

            if ( $order_id && ! is_a( $order, 'WC_Order' ) ) {
                $order = new FP_Order_Adapter($order_id);
            }

            if ( is_a( $order, 'WC_Order' ) ) {
                $order = new FP_Order_Adapter( $order );
            }

            if ( is_a( $order, 'FP_Order_Adapter' ) ) {
                $this->object                         = $order->get_order();
                $this->recipient                      = $this->object->get_billing_email();
                $this->placeholders = array_merge(
                    $this->placeholders,
                    $this->email_handler->create_placeholders( $order, false, true )
                );
                $this->placeholders['{order_date}']   = wc_format_datetime($this->object->get_date_created());
                $this->placeholders['{order_number}'] = $this->object->get_order_number();
            }

            if ( $this->get_recipient() ) {
                $this->send(
                    $this->get_recipient(),
                    $this->get_subject(),
                    $this->get_content(),
                    $this->get_headers(),
                    array_merge( $attachments, $this->get_attachments() ),
                );
            }

            $this->restore_locale();
        }

        /**
         * Get email subject.
         *
         * @since  3.1.0
         * @return string
         */
        public function get_default_subject() {
            return __( 'Your {site_title} invoice is here', 'fakturpro' );
        }

        /**
         * Get email heading.
         *
         * @since  3.1.0
         * @return string
         */
        public function get_default_heading() {
            return __( 'Invoice delivery', 'fakturpro' );
        }

        /**
         * Retrieves the HTML content of the email.
         *
         * @return string
         */
        public function get_content_html()
        {
            return wc_get_template_html(
                $this->template_html,
                array(
                    'order'              => $this->object,
                    'email_heading'      => $this->get_heading(),
                    'additional_content' => $this->get_additional_content(),
                    'sent_to_admin'      => false,
                    'plain_text'         => false,
                    'email'              => $this,
                    'main_content'       => $this->get_main_content(),
                    'order_details'      => $this->get_option( 'order_details', 'no' ),
                    'customer_details'   => $this->get_option( 'customer_details', 'no' ),
                ),
                '',
                $this->template_base
            );
        }

        /**
         * Retrieves the plain text content of the email.
         *
         * @return string
         */
        public function get_content_plain()
        {
            return wc_get_template_html(
                $this->template_plain,
                array(
                    'order'              => $this->object,
                    'email_heading'      => $this->get_heading(),
                    'additional_content' => $this->get_additional_content(),
                    'sent_to_admin'      => false,
                    'plain_text'         => true,
                    'email'              => $this,
                    'main_content'       => $this->get_main_content(),
                    'order_details'      => $this->get_option( 'order_details', 'no' ),
                    'customer_details'   => $this->get_option( 'customer_details', 'no' ),
                ),
                '',
                $this->template_base
            );
        }

        /**
         * Return content from the main_content field.
         *
         * @return string
         */
        public function get_main_content() {
            $main_content = $this->format_string( $this->get_option( 'main_content', $this->get_default_main_content() ) );
            return apply_filters( 'woocommerce_email_main_content_' . $this->id, $main_content, $this->object, $this );
        }

        /**
         * Default content to show below main email content.
         *
         * @since 3.7.0
         * @return string
         */
        public function get_default_additional_content() {
            return __( 'Your invoice for the order with the number {order_number} is attached to this email.', 'fakturpro' );
        }

        /**
         * Get default main content.
         * 
         * @return string
         */
        public function get_default_main_content() {
            return __( 'With this email we send your invoice to your order with the number {order_number}.', 'fakturpro' );
        }

		/**
		 * Initialise settings form fields.
         * 
         * @return void
		 */
		public function init_form_fields() {
			/* translators: %s: list of placeholders */
			$placeholder_text  = sprintf( __( 'Available placeholders: %s', 'woocommerce' ), '<code>' . esc_html( implode( '</code>, <code>', array_keys( $this->placeholders ) ) ) . '</code>' );
			$this->form_fields = array(
				'subject'            => array(
					'title'       => __( 'Subject', 'woocommerce' ),
					'type'        => 'text',
					'desc_tip'    => true,
					'description' => $placeholder_text,
					'placeholder' => $this->get_default_subject(),
					'default'     => '',
				),
				'heading'            => array(
					'title'       => __( 'Email heading', 'woocommerce' ),
					'type'        => 'text',
					'desc_tip'    => true,
					'description' => $placeholder_text,
					'placeholder' => $this->get_default_heading(),
					'default'     => '',
				),
				'main_content' => array(
					'title'       => __( 'Main content', 'woocommerce' ),
					'description' => __( 'The main email content.', 'woocommerce' ) . ' ' . $placeholder_text,
					'css'         => 'width:400px; height: 75px;',
					'placeholder' => __( 'N/A', 'woocommerce' ),
					'type'        => 'textarea',
					'default'     => $this->get_default_main_content(),
					'desc_tip'    => true,
				),
				'order_details' => array(
					'title'   => __( 'Order details', 'woocommerce' ),
					'type'    => 'checkbox',
					'label'   => __( 'Show order details', 'fakturpro' ),
					'default' => 'no',
				),
				'customer_details' => array(
					'title'   => __( 'Customer details', 'woocommerce' ),
					'type'    => 'checkbox',
					'label'   => __( 'Show customer details', 'fakturpro' ),
					'default' => 'no',
				),
				'additional_content' => array(
					'title'       => __( 'Additional content', 'woocommerce' ),
					'description' => __( 'Text to appear below the main email content.', 'woocommerce' ) . ' ' . $placeholder_text,
					'css'         => 'width:400px; height: 75px;',
					'placeholder' => __( 'N/A', 'woocommerce' ),
					'type'        => 'textarea',
					'default'     => $this->get_default_additional_content(),
					'desc_tip'    => true,
				),
				'email_type'         => array(
					'title'       => __( 'Email type', 'woocommerce' ),
					'type'        => 'select',
					'description' => __( 'Choose which format of email to send.', 'woocommerce' ),
					'default'     => 'html',
					'class'       => 'email_type wc-enhanced-select',
					'options'     => $this->get_email_type_options(),
					'desc_tip'    => true,
				),
			);
		}
    }

endif;
