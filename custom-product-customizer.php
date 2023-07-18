<?php
/*
Plugin Name: Custom Product Customizer Plugin
Plugin URI: http://www.lucarulvoni.it/plugins/custom-product.php
Description: Un plugin per la customizzazione dei prodotti su woocommerce.
Version: 1.0
Author: Luca Rulvoni
Author URI: http://www.lucarulvoni.it
License: GPLv2 or later
Text Domain: custom-product-customizer-plugin
*/

// File: custom-product-customizer.php (plugin principale)

// Registra il plugin di personalizzazione dei prodotti come un'estensione di Woocommerce
add_action('plugins_loaded', 'register_custom_product_customizer');

function register_custom_product_customizer() {
    if (class_exists('WooCommerce')) {
        // Definisci la classe del plugin
        class Custom_Product_Customizer {
            public function __construct() {
                // Aggiungi i tuoi hook e le tue azioni qui
                add_action('woocommerce_before_add_to_cart_button', array($this, 'display_customization_fields'));
                add_filter('woocommerce_add_cart_item_data', array($this, 'add_customization_data_to_cart'), 10, 3);
                add_filter('woocommerce_get_item_data', array($this, 'display_customization_data_in_cart'), 10, 2);
                add_action('woocommerce_before_calculate_totals', array($this, 'apply_customization_price'));
            }
            
            public function display_customization_fields() {
                // Mostra i campi di personalizzazione nel frontend
                // Puoi utilizzare HTML e JavaScript per la visualizzazione dei campi
                // Ad esempio:
                ?>
                <div id="product-customizer">
                    <h3>Aggiungi un monogramma</h3>
                    <input type="text" name="monogram" placeholder="Inserisci il monogramma">
                    
                    <h3>Scegli un colore</h3>
                    <select name="color">
                        <option value="rosso">Rosso</option>
                        <option value="verde">Verde</option>
                        <option value="blu">Blu</option>
                    </select>
                    
                    <h3>Carica un'immagine</h3>
                    <input type="file" name="custom_image">
                </div>
                <?php
            }
            
            public function add_customization_data_to_cart($cart_item_data, $product_id, $variation_id) {
                // Salva i dati di personalizzazione nel carrello
                if (isset($_POST['monogram'])) {
                    $cart_item_data['monogram'] = sanitize_text_field($_POST['monogram']);
                }
                
                if (isset($_POST['color'])) {
                    $cart_item_data['color'] = sanitize_text_field($_POST['color']);
                }
                
                if (!empty($_FILES['custom_image']['tmp_name'])) {
                    $upload = wp_upload_bits($_FILES['custom_image']['name'], null, file_get_contents($_FILES['custom_image']['tmp_name']));
                    
                    if (isset($upload['url'])) {
                        $cart_item_data['custom_image'] = $upload['url'];
                    }
                }
                
                return $cart_item_data;
            }
            
            public function display_customization_data_in_cart($item_data, $cart_item) {
                // Mostra i dati di personalizzazione nel carrello
                if (isset($cart_item['monogram'])) {
                    $item_data[] = array(
                        'key'   => 'Monogramma',
                        'value' => $cart_item['monogram'],
                    );
                }
                
                if (isset($cart_item['color'])) {
                    $item_data[] = array(
                        'key'   => 'Colore',
                        'value' => $cart_item['color'],
                    );
                }
                
                if (isset($cart_item['custom_image'])) {
                    $item_data[] = array(
                        'key'   => 'Immagine personalizzata',
                        'value' => '<img src="' . esc_url($cart_item['custom_image']) . '" width="100">',
                    );
                }
                
                return $item_data;
            }
            
            public function apply_customization_price() {
                // Aggiungi eventuali costi aggiuntivi per la personalizzazione
                global $woocommerce;
                
                foreach ($woocommerce->cart->get_cart() as $cart_item_key => $cart_item) {
                    // Calcola il costo aggiuntivo in base alle personalizzazioni
                    $customization_price = 0;
                    
                    // Esempio: aggiungi $10 per ogni monogramma
                    if (isset($cart_item['monogram'])) {
                        $customization_price += 10;
                    }
                    
                    // Esempio: aggiungi $5 per ogni immagine personalizzata
                    if (isset($cart_item['custom_image'])) {
                        $customization_price += 5;
                    }
                    
                    // Aggiungi il costo aggiuntivo al prodotto
                    $cart_item['data']->set_price($cart_item['data']->get_price() + $customization_price);
                }
            }
        }
        
        // Inizializza il plugin
        new Custom_Product_Customizer();
    }
}