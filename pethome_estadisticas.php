<?php
if (!defined('ABSPATH')) exit;

function pethome_estadisticas_panel() {
    global $wpdb;

    echo '<div class="wrap">';
    echo '<h1 style="color:#5e4365;">📊 Estadísticas</h1>';
    echo '<p style="font-size:16px;">Aquí se muestran los datos estadísticos de las reservas e ingresos mensuales.</p>';

    $posts = $wpdb->prefix . "posts";
    $meta = $wpdb->prefix . "postmeta";

    // === RESERVAS MENSUALES ===
    $result = $wpdb->get_results("
        SELECT DATE_FORMAT(post_date, '%m/%Y') as mes, COUNT(*) as total
        FROM $posts
        WHERE post_type = 'shop_order' AND post_status IN ('wc-completed','wc-processing')
        GROUP BY mes
        ORDER BY STR_TO_DATE(CONCAT('01/', mes), '%d/%m/%Y')
    ");
    $meses = []; $totales = [];
    foreach ($result as $r) {
        $meses[] = $r->mes;
        $totales[] = (int)$r->total;
    }

    echo '<div class="section-block">';
    echo '<h2>📅 Reservas por Mes</h2>';
    echo '<canvas id="reservas_por_mes" style="width:100%; max-width:1000px; height:auto; max-height:500px;"></canvas>';
    echo '</div>';

    // === INGRESOS MENSUALES ===
    $ingresos = $wpdb->get_results("
        SELECT DATE_FORMAT(p.post_date, '%m/%Y') as mes, SUM(pm.meta_value) as total
        FROM $posts p
        JOIN $meta pm ON p.ID = pm.post_id
        WHERE p.post_type = 'shop_order' 
          AND p.post_status IN ('wc-completed','wc-processing')
          AND pm.meta_key = '_order_total'
        GROUP BY mes
        ORDER BY STR_TO_DATE(CONCAT('01/', mes), '%d/%m/%Y')
    ");
    $meses_i = []; $totales_i = [];
    foreach ($ingresos as $r) {
        $meses_i[] = $r->mes;
        $totales_i[] = (float)$r->total;
    }

    echo '<div class="section-block">';
    echo '<h2>💵 Ingresos por Mes</h2>';
    echo '<canvas id="ingresos_por_mes" style="width:100%; max-width:1000px; height:auto; max-height:500px;"></canvas>';
    echo '</div>';

    // === GUARDAS POR CUIDADOR ===
$guardas = $wpdb->get_results("
    SELECT pm.meta_value AS cuidador, COUNT(*) AS total
    FROM {$wpdb->postmeta} pm
    JOIN {$wpdb->posts} p ON p.ID = pm.post_id
    WHERE LOWER(pm.meta_key) IN ('cuidador', 'cuidador_asignado')
      AND p.post_type = 'shop_order'
      AND p.post_status IN ('wc-completed','wc-processing')
    GROUP BY pm.meta_value
");


    $cuidadores = get_option('pethome_cuidadores', []);
    $alias_a_nombre = [];
    foreach ($cuidadores as $c) {
        $alias_a_nombre[$c['alias']] = $c['nombre'] . ' ' . $c['apellido'] . "\\n(" . $c['alias'] . ")";
$alias_a_nombre[$c['nombre'] . ' ' . $c['apellido']] = $c['nombre'] . ' ' . $c['apellido'] . "\\n(" . $c['alias'] . ")";
	}
    $nombres = []; $cantidades = [];
    foreach ($guardas as $g) {
        $alias = $g->cuidador ?: 'sin_asignar';
        $nombres[] = $alias_a_nombre[$alias] ?? "Desconocido\\n($alias)";
        $cantidades[] = (int)$g->total;
    }

    echo '<div class="section-block">';
    echo '<h2>🧍‍♂️ Guardas por Cuidador</h2>';
    echo '<canvas id="guardas_por_cuidador" style="width:100%; max-width:1000px; height:auto; max-height:500px;"></canvas>';
    echo '</div>';

    echo '</div>';
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const ctx1 = document.getElementById('reservas_por_mes').getContext('2d');
    new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($meses); ?>,
            datasets: [{
                label: 'Reservas',
                data: <?php echo json_encode($totales); ?>,
                backgroundColor: (() => {
                    const values = <?php echo json_encode($totales); ?>;
                    const min = Math.min(...values);
                    const max = Math.max(...values);
                    return values.map(v => {
                        const factor = (v - min) / (max - min + 1e-5);
                        const base = 90 - factor * 50;
                        return `hsl(285, 40%, ${base}%)`;
                    });
                })()
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    const ctx2 = document.getElementById('ingresos_por_mes').getContext('2d');
    new Chart(ctx2, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($meses_i); ?>,
            datasets: [{
                label: 'Ingresos',
                data: <?php echo json_encode($totales_i); ?>,
                backgroundColor: (() => {
                    const values = <?php echo json_encode($totales_i); ?>;
                    const min = Math.min(...values);
                    const max = Math.max(...values);
                    return values.map(v => {
                        const factor = (v - min) / (max - min + 1e-5);
                        const base = 90 - factor * 50;
                        return `hsl(285, 40%, ${base}%)`;
                    });
                })()
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    const ctx3 = document.getElementById('guardas_por_cuidador').getContext('2d');
    new Chart(ctx3, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($nombres); ?>,
            datasets: [{
                label: 'Cantidad de Guardas',
                data: <?php echo json_encode($cantidades); ?>,
                backgroundColor: (() => {
                    const values = <?php echo json_encode($cantidades); ?>;
                    const min = Math.min(...values);
                    const max = Math.max(...values);
                    return values.map(v => {
                        const factor = (v - min) / (max - min + 1e-5);
                        const base = 90 - factor * 50;
                        return `hsl(285, 40%, ${base}%)`;
                    });
                })()
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
});
</script>

<style>
.section-block {
    background: #f9f9f9;
    border: 2px solid #ccc;
    border-radius: 16px;
    padding: 20px;
    margin-top: 30px;
}
.section-block h2 {
    margin-bottom: 20px;
    color: #5e4365;
}
</style>
<?php
}
?>
