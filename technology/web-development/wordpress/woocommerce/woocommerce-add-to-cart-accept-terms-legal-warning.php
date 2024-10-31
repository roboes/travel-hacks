<?php

// WooCommerce - "Add to cart" accept legal warning terms
// Last update: 2024-10-16


if (class_exists('WooCommerce') && WC()) {

    add_action($hook_name = 'woocommerce_before_add_to_cart_button', $callback = 'woocommerce_add_terms_checkbox_legal_warning', $priority = 10, $accepted_args = 1);

    function woocommerce_add_terms_checkbox_legal_warning()
    {

        if (is_product()) {

            global $product;

            // Get the custom field value
            $product_legal_details = get_post_meta($product->get_id(), 'product_legal_details', true);

            // Check if the custom field is not empty and language is supported
            if (!empty($product_legal_details)) {

                // Settings
                $messages = [
                    'legal-warning-checkbox' => [
                        'de_DE' => 'Ich habe die Produktbeschreibung/Rechtliche Hinweise gelesen und bin mit den Bedingungen einverstanden.',
                        'de_DE_formal' => 'Ich habe die Produktbeschreibung/Rechtliche Hinweise gelesen und bin mit den Bedingungen einverstanden.',
                        'en_US' => 'I have read the product description/legal notice and I agree with the terms.',
                    ],
                    'legal-warning-error' => [
                        'de_DE' => 'Du musst mit den Bedingungen einverstanden sein, um fortzufahren.',
                        'de_DE_formal' => 'Sie müssen mit den Bedingungen einverstanden sein, um fortzufahren.',
                        'en_US' => 'You must agree with the terms to proceed.',
                    ],
                ];

                // Get current language
                $current_language = (function_exists('pll_current_language') && in_array(pll_current_language('locale'), pll_languages_list(array('fields' => 'locale')))) ? pll_current_language('locale') : 'en_US';

                if (isset($messages['legal-warning-checkbox'][$current_language])) {

                    $html = '<div class="product-terms-checkbox" style="margin-bottom: 20px;">
                        <label>
                            <input type="checkbox" name="checkbox_legal_warning" id="checkbox_legal_warning" />
                            <span style="line-height: 20px;">' . $messages['legal-warning-checkbox'][$current_language] . '</span>
                        </label>
                    </div>';

                    $html .= '<style>
                        .checkbox-highlight {
                            border: 2px solid red;
                            background-color: #ffe6e6;
                            padding: 5px;
                        }
                    </style>';

                    $html .= '<script>
                        jQuery(document).ready(function($) {
                            // Find the elements to be rearranged
                            const $checkbox = $(".product-terms-checkbox");
                            const $singleVariation = $(".woocommerce-variation.single_variation");

                            // Check if both elements exist
                            if ($checkbox.length && $singleVariation.length) {
                                // Move the checkbox after the single_variation element
                                $singleVariation.after($checkbox);
                            }

                            // Handle form submit event
                            $("form.cart").on("submit", function(event) {
                                if ($("#checkbox_legal_warning").length && !$("#checkbox_legal_warning").prop("checked")) {
                                    // Prevent form submission only if the checkbox exists
                                    event.preventDefault();
                                    // Show notification
                                    const message = "' . $messages['legal-warning-error'][$current_language] . '";
                                    // Add the notice to the page
                                    if (!$(".woocommerce-error").length) {
                                        $(".woocommerce-notices-wrapper").append("<ul class=\"woocommerce-error\" role=\"alert\"><li>" + message + "</li></ul>");
                                    }
                                    // Scroll to the top of the page
                                    $("html, body").animate({ scrollTop: 0 }, "slow");

                                    // Highlight the checkbox or text
                                    $("#checkbox_legal_warning").closest("label").addClass("checkbox-highlight");
                                }
                            });
                        });
                    </script>';

                    echo $html;
                }
            }
        }
    }

}
