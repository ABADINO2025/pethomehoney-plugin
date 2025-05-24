<?php
/**
 * Plugin Name: PetHomeHoney Plugin
 * Plugin URI:  https://pethomehoney.com.ar
 * Description: Plugin para gestionar reservas de guarda con WooCommerce y CPT.
 * Version:     0.0.21
 * Author:      Adrián Enrique Badino
 * Author URI:  https://pethomehoney.com.ar
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 1) Registrar CPT “reserva_guarda”
 */
add_action( 'init', function() {
    $labels = [
        'name'               => 'Reservas de Guarda',
        'singular_name'      => 'Reserva de Guarda',
        'menu_name'          => 'Reservas de Guarda',
        'name_admin_bar'     => 'Reserva de Guarda',
        'all_items'          => 'Todas las Reservas',
        'add_new_item'       => 'Agregar Nueva Reserva',
        'edit_item'          => 'Editar Reserva',
        'new_item'           => 'Nueva Reserva',
        'view_item'          => 'Ver Reserva',
        'search_items'       => 'Buscar Reservas',
        'not_found'          => 'No se encontraron reservas',
        'not_found_in_trash' => 'No hay reservas en la papelera',
    ];
    register_post_type( 'reserva_guarda', [
        'labels'        => $labels,
        'public'        => false,
        'show_ui'       => true,
        'show_in_menu'  => 'pethome_main',
        'menu_position' => 57,
        'menu_icon'     => 'dashicons-calendar-alt',
        'supports'      => [ 'title', 'custom-fields' ],
        'has_archive'   => false,
        'rewrite'       => false,
    ] );
} );

/**
 * 2) Todo lo de admin (menús, assets, metaboxes, handlers…)
 */
if ( is_admin() ) {

    add_action( 'admin_menu', function() {

        // 2.a) Menú principal “Guardería de Mascotas”
        add_menu_page(
            'Guardería de Mascotas',
            'Guardería de Mascotas',
            'manage_options',
            'pethome_main',
            function(){
                echo '<div class="wrap">';
                echo '<h1 style="color:#5e4365;">👋 Guardería de Mascotas</h1>';
                echo '<p>Gestioná reservas, cuidadores, estadísticas y configuración.</p>';
                echo '</div>';
            },
            'dashicons-pets',
            56
        );

        // 2.b) Sub‐menús bajo pethome_main
        // Agregar Guarda
        add_submenu_page(
            'pethome_main',
            'Agregar Guarda',
            'Agregar Guarda',
            'manage_options',
            'pethome_guardas_agregar',
            function(){
                echo '<div class="wrap"><h1>Agregar Guarda</h1>';
                include plugin_dir_path(__FILE__).'pethome_guardas_agregar.php';
                echo '</div>';
            }
        );
        // Reservas (CPT list)
        add_submenu_page(
            'pethome_main',
            'Reservas de Guarda',
            'Reservas',
            'manage_options',
            'edit.php?post_type=reserva_guarda'
        );
        // Cuidadores
        add_submenu_page(
            'pethome_main',
            'Cuidadores',
            'Cuidadores',
            'manage_options',
            'pethome_cuidadores',
            'pethome_cuidadores_callback'
        );
        // Estadísticas
        add_submenu_page(
            'pethome_main',
            'Estadísticas',
            'Estadísticas',
            'manage_options',
            'pethome_estadisticas',
            'pethome_estadisticas_callback'
        );
        // FAQ
        add_submenu_page(
            'pethome_main',
            'Preguntas Frecuentes',
            'Preguntas Frecuentes',
            'manage_options',
            'pethome_faq',
            'pethome_faq_callback'
        );
        // Configuración
        add_submenu_page(
            'pethome_main',
            'Configuración',
            'Configuración',
            'manage_options',
            'pethome_configuracion',
            'pethome_configuracion_callback'
        );
        // Importar Booking
        add_submenu_page(
            'pethome_main',
            'Importar Booking',
            'Importar Booking',
            'manage_options',
            'pethome_importador_booking',
            'pethome_importador_booking_func'
        );
        // Editar Cuidador (oculto)
        add_submenu_page(
            null,
            'Editar Cuidador',
            '',
            'manage_options',
            'pethome_cuidador_editar',
            'pethome_cuidador_editar_callback'
        );
    } );

    /**
     * 2.c) Callbacks de páginas
     */
    function pethome_cuidadores_callback() {
        include plugin_dir_path(__FILE__).'pethome_cuidadores.php';
        if ( function_exists('pethome_cuidadores_panel') ) {
            pethome_cuidadores_panel();
        }
    }
    function pethome_estadisticas_callback() {
        include plugin_dir_path(__FILE__).'pethome_estadisticas.php';
        if ( function_exists('pethome_estadisticas_panel') ) {
            pethome_estadisticas_panel();
        }
    }
    function pethome_faq_callback() {
        include plugin_dir_path(__FILE__).'pethome_faq.php';
        if ( function_exists('pethome_faq_panel') ) {
            pethome_faq_panel();
        }
    }
    function pethome_configuracion_callback() {
        include plugin_dir_path(__FILE__).'pethome_configuracion.php';
        if ( function_exists('pethome_configuracion_panel') ) {
            pethome_configuracion_panel();
        }
    }

    /**
 * 2.d) Enqueue Flatpickr & Autocomplete solo en “Agregar Guarda”
 */
add_action( 'admin_enqueue_scripts', function( $hook ) {
    if ( $hook === 'pethome_page_pethome_guardas_agregar' ) {
        // CAMBIA ESTA LÍNEA
        wp_enqueue_style( 'flatpickr-css', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/airbnb.css' ); // <--- AQUÍ EL CAMBIO
        wp_enqueue_script( 'flatpickr-js',  'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.js', [], null, true );
        $js = plugin_dir_path(__FILE__).'assets/js/autocompletar.js';
        if ( file_exists( $js ) ) {
            wp_enqueue_script(
                'pethome-autocomplete',
                plugin_dir_url(__FILE__).'assets/js/autocompletar.js',
                [], filemtime($js), true
            );
            wp_localize_script( 'pethome-autocomplete', 'ajax_object', [
                'ajax_url' => admin_url('admin-ajax.php')
            ] );
        }
    }
} );

    /**
     * 2.e) Importador y handler de guardas
     */
    require plugin_dir_path(__FILE__).'pethome_importador_booking.php';
    require plugin_dir_path(__FILE__).'includes/pethome_guardas_save-handler.php';

    /**
     * 2.f) Metabox “Detalles de Reserva”
     */
    add_action( 'add_meta_boxes', function() {
        add_meta_box(
            'pethome_reserva_details',
            'Detalles de Reserva',
            'pethomehoney_reserva_details_cb',
            'reserva_guarda',
            'normal',
            'default'
        );
    } );

    /**
     * 2.g) Metabox styles
     */
    add_action( 'admin_head', function() {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if ( $screen && $screen->post_type === 'reserva_guarda' && $screen->base === 'post' ) {
            echo '<style>
                /* widefat.striped redondeado y fondo uniforme */
                #pethome_reserva_details table.widefat.striped {
                  border-radius:20px; overflow:hidden;
                }
                #pethome_reserva_details table.widefat.striped thead th,
                #pethome_reserva_details table.widefat.striped tbody tr td {
                  background:#f6f7f7 !important;
                }
            </style>';
        }
    } );

    /**
     * 2.h) Callback de metabox
     */
    function pethomehoney_reserva_details_cb( $post ) {
        $m = get_post_meta( $post->ID );
        echo '<table class="widefat striped"><tbody>';
        // … tu renderizado de filas aquí …
        echo '</tbody></table>';
    }
}
