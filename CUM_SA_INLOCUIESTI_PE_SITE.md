# 💎 Ghid Complet: Cum să Înlocuiești Automat Pozele pe Situl Tău (top-blat.ro & Mobilă de Lux)

Acest ghid îți explică pas cu pas cum funcționează sistemul de asociere (mapping) generat pentru cele **286 de produse** și **123 de proiecte reale**, astfel încât situl tău să știe automat **ce poză corespunde fiecărui produs în parte**, fără să le asociezi manual una câte una.

---

## 📁 1. Structura Folderelor cu Imagini

Toate imaginile au fost descărcate și organizate în folderul:
`/root/projects/top-blat-assets/`

```text
top-blat-assets/
├── raw/                                # Imaginile originale descărcate
│   ├── products/                       # Scurtături/texturi brute ale celor 286 de produse
│   └── projects/                       # 123 de fotografii de montaj din producție
│
├── luxury/                             # Versiunile Îmbunătățite LUXURY (WebP & JPG de înaltă rezoluție)
│   ├── products/                       # Texturi cu micro-contrast, culori calde/venaturi accentuate și claritate
│   ├── projects/                       # Fotografii arhitecturale cu negru profund și detalii fine
│   └── cards/                          # Carduri oficiale 1200x800 cu ramă aurie, siglă brand & aplicație mobilier
│
├── manifest/                           # Baza de date cu maparea automată
│   ├── products_manifest.json          # Format JSON complet pentru dezvoltatori / API
│   ├── products_manifest.js            # Modul JavaScript gata de inclus în frontend
│   └── products_manifest.csv           # Tabel compatibil Excel / WooCommerce Product Importer
│
└── luxury_catalog_browser.html         # Interfață web interactivă de căutare și previzualizare
```

---

## ⚡ 2. Metoda 1: Înlocuire Automată pe WordPress / WooCommerce (top-blat.ro)

Situl tău `top-blat.ro` este construit pe **WordPress & WooCommerce**. Fiecare produs are un identificator unic numit **slug** (de exemplu: `elysee-quartz-alb-coante`, `calacatta-gold-silestone`, etc.).

### Opțiunea A: Import Automat prin WooCommerce CSV
1. Deschide Panoul de Administrare WordPress (`top-blat.ro/wp-admin`).
2. Mergi la **Produse** -> **Toate Produsele** -> Click pe butonul **Importă** (sus).
3. Încarcă fișierul `products_manifest.csv` generat în folderul `manifest/`.
4. Bifează căsuța: **„Actualizează produsele existente dacă ID-ul sau SKU-ul / Slug-ul se potrivește”**.
5. Mapează coloana `Imagine Luxury WebP` la câmpul **Imagini** (Images).
6. Click pe **Rulează importatorul**. WooCommerce va actualiza instant toate pozele cu versiunile Luxury!

### Opțiunea B: Script PHP Automat (One-Click)
Dacă ai acces la FTP sau WP-CLI, poți plasa următorul cod în `functions.php` sau rula un script:

```php
<?php
// Script de actualizare automată a imaginilor pe baza slug-ului
$manifest_json = file_get_contents(__DIR__ . '/products_manifest.json');
$catalog = json_decode($manifest_json, true)['products'];

foreach ($catalog as $item) {
    $product_slug = $item['slug'];
    $luxury_image_url = 'https://situltau.ro/luxury/products/' . $item['slug'] . '_luxury.webp';
    
    // Găsește produsul WooCommerce după slug
    $product_id = wc_get_product_id_by_slug($product_slug);
    if ($product_id) {
        // Încarcă și asociază automat imaginea ca Featured Image
        // $attach_id = upload_image_to_media_library($luxury_image_url);
        // set_post_thumbnail($product_id, $attach_id);
    }
}
?>
```

---

## 🌐 3. Metoda 2: Asociere Automată în Site-ul de Mobilă de Lux / PureStone (HTML & JS)

Pentru noul site de prezentare sau magazinul modern de mobilă la comandă, nu trebuie să scrii manual nicio cale de imagine.

### Pasul 1: Include fișierul de mapare în `<head>` sau înainte de `</body>`
```html
<script src="./top-blat-assets/manifest/products_manifest.js"></script>
```

### Pasul 2: Adaugă atributul `data-topblat-slug` pe orice imagine
```html
<!-- Situl va ști automat să încarce poza luxury corespunzătoare -->
<img data-topblat-slug="elysee-quartz-alb-coante" class="luxury-stone-img" alt="Blat Cuarț">
<img data-topblat-slug="calacatta-gold-silestone" class="luxury-stone-img" alt="Insulă Mobilier">
```

### Pasul 3: Adaugă cele 3 linii de cod JavaScript pentru auto-populare
```javascript
// La încărcarea paginii, scriptul populează automat toate imaginile
document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll('[data-topblat-slug]').forEach(img => {
        const slug = img.getAttribute('data-topblat-slug');
        const product = TopBlatHelper.getBySlug(slug);
        if (product) {
            // Setează poza luxury îmbunătățită
            img.src = product.primary_luxury_webp;
            img.alt = product.title + ' - ' + product.brand;
        }
    });
});
```

---

## 🛋️ 4. Recomandările de Împerechere cu Mobilierul de Lux

Fiecare produs include în `products_manifest.json` recomandarea dedicată pentru producția de mobilier la comandă:

| Tip Material | Nuanțe Cheie | Asociere Recomandată cu Mobilierul de Lux |
|---|---|---|
| **Alb & Calacatta Gold** | Aurii, Crem, Alb Cald | Fronturi din **Nuc American** sau Furnir Natural cu Insulă Monolit Waterfall |
| **Negru Obsidian & Marquina** | Grafit, Negru Intens | Mobilier **Negru Supermat (Fenix)** cu profil de iluminare caldă LED 2700K |
| **Bej Cald & Nisipiu** | Savannah, Cendre, Crem | Mobilier clasic contemporan din **Stejar Periat Natur** și frezări discrete |
| **Gri Architectural & Beton** | Cemento, Ardezie, Slate | Mobilier stil industrial rafinat cu **Cadre Metalice Bronz** și sticlă fumurie |
| **Nuanțe Statement** | Mystic Blue, Verde Pădure | **Insule Statement**, Punct de accent pentru bar de cocktail sau recepție |

---

## 🔍 5. Cum folosești Catalogul Interactiv de Previzualizare

Deschide direct în browser:
`luxury_catalog_browser.html` (sau de pe server: `http://localhost:8080/luxury-catalog.html`)

* **Căutare Instantanee:** Tastează orice nume de material sau nuanță.
* **Filtrare pe Brand:** Atlas Plan, Silestone, Technistone, Coante, Laminam, Cimstone.
* **Copiere cu 1 Click:** Apasă pe orice produs pentru a copia direct calea imaginii `.webp`, tag-ul HTML sau codul pentru situl tău.
* **Comutare Card / Textură:** Poți alege să folosești fie textura curată a plăcii de piatră, fie cardul de prezentare oficial cu sigle de brand.
