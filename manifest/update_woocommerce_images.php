<?php
/**
 * Script Automat de Asociere Imagini pentru top-blat.ro (WooCommerce)
 *
 * Utilizare:
 * 1. Încarcă acest fișier în directorul principal WordPress (lângă wp-config.php).
 * 2. Încarcă folderul 'luxury' în 'wp-content/uploads/luxury-stones/'.
 * 3. Deschide în browser: https://top-blat.ro/update_woocommerce_images.php?secret_key=topblat2026
 *    SAU rulează din terminal / SSH:
 *    php update_woocommerce_images.php
 */

// Securitate de bază:
$REQUIRED_KEY = 'topblat2026';
if (php_sapi_name() !== 'cli' && (!isset($_GET['secret_key']) || $_GET['secret_key'] !== $REQUIRED_KEY)) {
    die("Acces neautorizat. Specifică ?secret_key=topblat2026");
}

// Încarcă mediul WordPress
require_once __DIR__ . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

header('Content-Type: text/plain; charset=utf-8');
echo "=== Începere Actualizare Automată Imagini WooCommerce Top-Blat ===\n\n";

$manifest_file = __DIR__ . '/products_manifest.json';
if (!file_exists($manifest_file)) {
    die("Eroare: products_manifest.json nu a fost găsit în directorul curent.\n");
}

$data = json_decode(file_get_contents($manifest_file), true);
$products = $data['products'] ?? [];
echo "S-au găsit " . count($products) . " produse în manifest.\n\n";

$updated = 0;
$skipped = 0;
$not_found = 0;

foreach ($products as $p) {
    $slug = $p['slug'];
    $title = $p['title'];
    $brand = $p['brand'];
    
    // Găsește produsul WooCommerce după slug
    $post = get_page_by_path($slug, OBJECT, 'product');
    if (!$post) {
        // Încearcă căutare după titlu
        $query = new WP_Query([
            'post_type' => 'product',
            'title' => $title,
            'posts_per_page' => 1
        ]);
        if ($query->have_posts()) {
            $post = $query->posts[0];
        }
    }

    if (!$post) {
        echo "[-] Produs negăsit în WooCommerce: $slug ($title)\n";
        $not_found++;
        continue;
    }

    $product_id = $post->ID;
    
    // Numele fișierului îmbunătățit
    $luxury_filename = $slug . '_luxury.webp';
    $local_image_path = WP_CONTENT_DIR . '/uploads/luxury-stones/' . $luxury_filename;

    if (!file_exists($local_image_path)) {
        // Căutare alternativă
        echo "[!] Fișierul $luxury_filename nu există în wp-content/uploads/luxury-stones/\n";
        $skipped++;
        continue;
    }

    // Atașează imaginea în Media Library dacă nu există deja
    $attachment_id = 0;
    $existing = get_posts([
        'post_type' => 'attachment',
        'meta_key' => '_wp_attached_file',
        'meta_value' => 'luxury-stones/' . $luxury_filename,
        'posts_per_page' => 1
    ]);

    if (!empty($existing)) {
        $attachment_id = $existing[0]->ID;
    } else {
        $file_type = wp_check_filetype($luxury_filename, null);
        $attachment = [
            'post_mime_type' => $file_type['type'] ?: 'image/webp',
            'post_title' => sanitize_file_name($title . ' ' . $brand),
            'post_content' => $p['furniture_pairing'] ?? '',
            'post_status' => 'inherit'
        ];
        $attachment_id = wp_insert_attachment($attachment, $local_image_path, $product_id);
        $attach_data = wp_generate_attachment_metadata($attachment_id, $local_image_path);
        wp_update_attachment_metadata($attachment_id, $attach_data);
    }

    if ($attachment_id) {
        // Setează ca Featured Image
        set_post_thumbnail($product_id, $attachment_id);
        
        // Actualizează și descrierea/meta câmpurile dacă dorești
        update_post_meta($product_id, '_luxury_material_brand', $brand);
        update_post_meta($product_id, '_luxury_furniture_pairing', $p['furniture_pairing'] ?? '');
        
        echo "[+] S-a actualizat cu succes: #$product_id - $title (Imagine: $luxury_filename)\n";
        $updated++;
    }
}

echo "\n============================================\n";
echo "Rezumat finalizare:\n";
echo "• Produse actualizate: $updated\n";
echo "• Produse sărite: $skipped\n";
echo "• Produse negăsite: $not_found\n";
echo "============================================\n";
