<?php

if ( !defined('ABSPATH') ) {
    exit;
}



/**
 * Función para mostrar un botón debajo de la descripción del producto
 */
function button_pdf() {
    if ( is_product() ) {
        echo '<div class="btn-download" style="margin-top: 20px;">'; 
        $pdf_link = esc_url( add_query_arg( 'generate-pdf', 'true' ) );
        echo '<p><a href="' . $pdf_link . '">Generar PDF</a></p>';
        echo '</div>';        
       ?>

       <?php
    
    }
}
add_action('woocommerce_after_single_product_summary', 'button_pdf', 15);
add_shortcode('btn_pdf', 'button_pdf');

/**
 * Función para generar un PDF usando dompdf con imagen del producto
 */

 function mi_primer_plugin_generar_pdf() {
    if ( is_product() && isset($_GET['generate-pdf']) ) {
        global $product;

        // if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
        //     error_log('Error: Producto no válido.');
        //    die();
        // }

        $product_id = get_queried_object_id();
        error_log('Product ID: ' . $product_id);
        $codigo = get_post_meta($product_id, '_codigo_producto', true);
        $product_image_url = get_the_post_thumbnail_url($product_id, 'full');
        $header_image_url = plugin_dir_url(__FILE__) . 'assets/img/header_pdf.png';

        require_once plugin_dir_path(__FILE__) . '../dompdf/autoload.inc.php';

        if ( ! $product_image_url ) {
            error_log('Error: URL de la imagen del producto no válida.');
        }

        $image_data = file_get_contents($product_image_url);
        if ($image_data === false) {
            error_log('Error: No se pudo obtener la imagen del producto.');
            return;
        }
        $base64_image = 'data:image/png;base64,' . base64_encode($image_data);

        $header_image_data = file_get_contents($header_image_url);
        if ($header_image_data === false) {
            error_log('Error: No se pudo obtener la imagen de cabecera.');
            return;
        }
        $base64_header_image = 'data:image/png;base64,' . base64_encode($header_image_data);

        $css_file_url = plugin_dir_path(__FILE__) . 'assets/css/styles-pdf.css';
        $css_content = file_get_contents($css_file_url);
        if ($css_content === false) {
            error_log('Error: No se pudo obtener el contenido del archivo CSS.');
            return;
        }

        $test = wc_get_product($product_id);

        $content = '<html>
                        <head>
                            <style>' . $css_content . '</style>
                        </head>
                        <body>
                        <img class="header-image" src="' . $base64_header_image . '" alt="Header Image">
                            <div class="container">
                                <table border="0" class="table-principal">
                                <tbody>
                                    <tr>
                                        <td>
                                            <div class="imagen-container">
                                                <img src="' . $base64_image . '" alt="Product Image" class="product-image">
                                            </div>
                                        </td>

                                        <td>
                                            <div class="info-product-container">
                                            <h1 class="title"> '. get_the_title($product_id) . ' </h1>
                                            <strong class="code"> ' . $codigo .  '</strong>
                                            <p>' . get_the_content($product_id) .  '</p>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td colspan="3">    
                                            <h2 class="title mt-5">Specifications</h2>
                                            <div class="short-description">';

                                            if ($test && is_a($test, 'WC_Product')) {
                                                $content .= $test->get_short_description();
                                            } else {
                                                $content .= 'Descripción corta no disponible.';
                                            }

                                            $content .= '</div>
                                        </td>

                                        
                                    <tr>
                                </tbody>
                            </table>
                            </div>
                        </body>
                    </html>';

        $dompdf = new Dompdf\Dompdf();
        $dompdf->loadHtml($content);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream('producto-' . $product_id . '.pdf', array('Attachment' => false));
        exit;
    }
}
add_action('template_redirect', 'mi_primer_plugin_generar_pdf');
