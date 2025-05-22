<?php
function pethome_reservas_panel() {
    if (isset($_GET['eliminar_reserva'])) {
        $post_id = intval($_GET['eliminar_reserva']);
        if ($post_id > 0) {
            $pedido_id = get_post_meta($post_id, 'booking_order_id', true);
            if ($pedido_id) {
                wp_delete_post($pedido_id, true);
            }
            wp_delete_post($post_id, true);
            echo '<div style="background:#d4edda;color:#155724;border:1px solid #c3e6cb;padding:10px;margin-bottom:20px;border-radius:8px;"><strong>✅ Reserva y pedido eliminados correctamente</strong></div>';
        }
    }

// === LISTADO DE RESERVAS (WP_Query) ===
$paged      = max( 1, intval( $_GET['paged'] ?? 1 ) );
$por_pagina = 20;

/* ───────── Configurar ordenamiento seguro ───────── */
$orderby = isset( $_GET['orderby'] )
           ? sanitize_text_field( $_GET['orderby'] )
           : 'date';        // valor por defecto

$order   = isset( $_GET['order'] )
           ? strtoupper( $_GET['order'] )
           : 'DESC';        // valor por defecto

/* Solo permitir valores conocidos */
$allowed_orderby = array( 'ID', 'date', 'title', 'meta_value', 'meta_value_num' );
if ( ! in_array( $orderby, $allowed_orderby, true ) ) {
    $orderby = 'date';
}
$order = ( $order === 'ASC' ) ? 'ASC' : 'DESC';

$args = array(
    'post_type'      => 'wc_booking',
    'posts_per_page' => 20,
    'paged'          => $paged,
    'post_status'    => 'publish',
    'orderby'        => 'date',
    'order'          => 'DESC',
);
$reservas = new WP_Query( $args );

    $booking_product_id = 2611;
$orders = wc_get_orders( array(
    'limit'      => -1,
    'status'     => array( 'wc-pending', 'wc-processing', 'wc-completed' ),
    'product_id' => 2611,               // ← parámetro soportado por WC_Order_Query
) );
?>
<div class="wrap" style="margin: 30px;">
    <h1 style="text-align:center; color:#5e4365; font-size:32px; margin-bottom:20px;">📋 Listado de Guardas</h1>

    <div style="text-align:center; margin-bottom: 20px;">
        <a href="admin.php?page=pethome_guardas_agregar" class="page-title-action" style="font-size:16px; font-weight:bold; background-color:#5e4365; color:white; padding:8px 16px; border-radius:5px; text-decoration:none;">➕ Agregar Guarda</a>
    </div>

    <style>
        table.wp-list-table {
            border-collapse: collapse;
            width: 100%;
            background-color: #fff;
            border: 2px solid #ccc;
            border-radius: 8px;
            font-family: Arial, sans-serif;
            box-shadow: 4px 4px 12px rgba(0, 0, 0, 0.4);
        }
        table.wp-list-table thead th {
            background-color: #f0f0f1 !important;
            color: #333;
            font-weight: bold;
            padding: 5px 5px;
            border-bottom: 2px solid #ccc;
            border-right: 2px solid #f0f0f1;
			white-space: nowrap;
        }
        table.wp-list-table tbody td {
            padding: 5px 5px;
            color: #333;
            background-color: #f9f9f9 !important;
            transition: background-color 0.2s, color 0.2s;
            border-bottom: 2px solid #f0f0f1;
            border-right: 2px solid #f0f0f1;
			white-space: nowrap;
        }
        table.wp-list-table tbody tr:hover td {
            background-color: #5e4365 !important;
            color: #fff !important;
        }
        table.wp-list-table td a {
            color: #5e4365;
            font-weight: bold;
            text-decoration: none;
        }
        table.wp-list-table tbody tr:hover td a {
            color: #fff !important;
        }
        table.wp-list-table tbody tr td:first-child,
        table.wp-list-table thead th:first-child {
            border-left: 2px solid #f0f0f1;
        }
        .acciones a {
            color: #cc0000;
            font-weight: bold;
            text-decoration: none;
            font-size: 20px;
        }
        .acciones a:hover {
            color: #ffffff;
        }
    </style>

    <table class="wp-list-table widefat fixed striped table-view-list posts">
        <thead>
            <tr>
                <?php
$current_orderby = $_GET['orderby'] ?? 'date';
$current_order = $_GET['order'] ?? 'desc';
$new_order = ($current_order == 'asc') ? 'desc' : 'asc';
$order_icon = ($current_order == 'asc') ? '↓' : '↑';
$url_base = admin_url('admin.php?page=pethome_reservas');
$id_sort_link = add_query_arg(array(
    'orderby' => 'ID',
    'order' => $new_order,
), $url_base);
?>
<th>
    <a href="<?php echo esc_url($id_sort_link); ?>" style="text-decoration:none; color:#333;">
        ID <?php echo ($current_orderby == 'ID') ? $order_icon : ''; ?>
    </a>
</th>
                <th>Mascota</th>
                <th>Cliente</th>
                <th>Fecha Ingreso</th>
                <th>Fecha Salida</th>
                <th>Cuidador</th>
                <th>Total</th>
                <th class="acciones">❌</th>
            </tr>
        </thead>
        <tbody>
        <?php
        if ($reservas->have_posts()) {
            while ($reservas->have_posts()) {
                $reservas->the_post();
                $reserva_id = get_the_ID();
                $nombre = get_post_meta($reserva_id, 'cliente_nombre', true);
                $apellido = get_post_meta($reserva_id, 'cliente_apellido', true);
                $mascota = get_post_meta($reserva_id, 'mascota_nombre', true);
                $ingreso = get_post_meta($reserva_id, 'fecha_ingreso', true);
                $hora_ingreso = get_post_meta($reserva_id, 'hora_ingreso', true);
                $salida = get_post_meta($reserva_id, 'fecha_salida', true);
                $hora_egreso = get_post_meta($reserva_id, 'hora_egreso', true);
                $cuidador = get_post_meta($reserva_id, 'cuidador_asignado', true);
                $total = get_post_meta($reserva_id, 'precio_total', true);
                echo "<tr>
                    <td><a href='" . admin_url('post.php?post=' . $reserva_id . '&action=edit') . "'>#$reserva_id</a></td>
                    <td>" . esc_html($mascota) . "</td>
                    <td>" . esc_html($nombre . ' ' . $apellido) . "</td>
                    <td>" . esc_html($ingreso . ' ' . $hora_ingreso) . "</td>
                    <td>" . esc_html($salida . ' ' . $hora_egreso) . "</td>
                    <td>" . esc_html($cuidador) . "</td>
                    <td>$" . number_format((float)$total, 2) . "</td>
                    <td class='acciones'><a href='" . esc_url(admin_url('admin.php?page=pethome_reservas&eliminar_reserva=' . $reserva_id)) . "' onclick=\"return confirm('¿Estás seguro de eliminar esta reserva?')\">❌</a></td>
                </tr>";
            }
            wp_reset_postdata();
        } else {
            echo '<tr><td colspan="8">No hay reservas.</td></tr>';
        }

        foreach ($orders as $order) {
            foreach ($order->get_items() as $item) {
                if ($item->get_product_id() == $booking_product_id) {
                    $mascota = '';
                    $meta_data = $item->get_formatted_meta_data();
                    foreach ($meta_data as $meta) {
                        if (stripos($meta->display_key, 'mascota') !== false) {
                            $mascota = wp_strip_all_tags($meta->display_value);
                            break;
                        }
                    }
                    $start = $item->get_meta('_booking_start');
                    $end = $item->get_meta('_booking_end');
                    $cliente = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
                    $total = $order->get_total();
                    echo "<tr>
                        <td><a href='" . admin_url('post.php?post=' . $order->get_id() . '&action=edit') . "'>#" . $order->get_id() . "</a></td>
                        <td>" . esc_html($mascota ?: 'Producto Booking') . "</td>
                        <td>" . esc_html($cliente) . "</td>
                        <td>" . esc_html($start) . "</td>
                        <td>" . esc_html($end) . "</td>
                        <td>-</td>
                        <td>$" . number_format((float)$total, 2) . "</td>
                        <td></td>
                    </tr>";
                }
            }
        }
        ?>
        </tbody>
    </table>

    <div style="text-align:center; margin-top:20px;">
    <?php
        echo paginate_links(array(
            'total'   => $reservas->max_num_pages,
            'current' => $paged,
            'format'  => '?paged=%#%',
            'prev_text' => '← Anterior',
            'next_text' => 'Siguiente →'
        ));
    ?>
    </div>
</div>
<?php } ?>
