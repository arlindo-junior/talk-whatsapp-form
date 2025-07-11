<?php
/**
 * Plugin Name: Talk WhatsApp Form + Kanban
 * Description: Captura leads via formulário WhatsApp e gerencia status em Kanban.
 * Version: 1.1
 * Author: Arlindo Jr.
 */

if (!defined('ABSPATH')) exit;

// CPT: Leads da Talk
add_action('init', function() {
    register_post_type('talk_lead', [
        'labels' => [
            'name' => 'Leads da Talk',
            'singular_name' => 'Lead',
        ],
        'public' => false,
        'show_ui' => true,
        'supports' => ['title', 'custom-fields'],
        'menu_icon' => 'dashicons-whatsapp',
    ]);
});

// Enqueue Scripts (Front + Admin)
add_action('wp_enqueue_scripts', 'talk_enqueue_scripts');
add_action('admin_enqueue_scripts', 'talk_enqueue_admin_scripts');

function talk_enqueue_scripts() {
    wp_enqueue_script('jquery-mask', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js', ['jquery'], null, true);
}

function talk_enqueue_admin_scripts($hook) {
    if ($hook !== 'edit.php' || get_post_type() !== 'talk_lead') return;

    wp_enqueue_style('talk-kanban-style', plugin_dir_url(__FILE__) . 'kanban.css');
    wp_enqueue_script('talk-kanban-script', plugin_dir_url(__FILE__) . 'kanban.js', ['jquery', 'jquery-ui-sortable'], null, true);

    wp_localize_script('talk-kanban-script', 'talk_kanban_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('talk_kanban_nonce')
    ]);
}

// Botão flutuante + popup no frontend
add_action('wp_footer', function() {
    ?>
    <div id="talk-whatsapp-button">📩 Receba esta talk pelo WhatsApp</div>
    <div id="talk-popup">
        <div class="talk-popup-content">
            <span id="talk-close">&times;</span>
            <h3>Receba esta talk</h3>
            <form id="talk-form">
                <input type="text" name="nome" placeholder="Seu nome" required>
                <input type="text" name="telefone" placeholder="Seu telefone" required>
                <input type="hidden" name="action" value="talk_form_submit">
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('talk_nonce'); ?>">
                <button type="submit">Enviar</button>
            </form>
            <div id="talk-success" style="display:none;">✅ Em breve enviaremos esta talk!</div>
        </div>
    </div>
    <style>
        #talk-whatsapp-button {
            position: fixed;
            bottom: 20px; right: 20px;
            background-color: #25D366;
            color: white;
            padding: 12px 18px;
            border-radius: 30px;
            cursor: pointer;
            font-weight: bold;
            z-index: 9999;
        }
        #talk-popup {
            display: none;
            position: fixed; left: 0; top: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 10000;
        }
        .talk-popup-content {
            background: #fff;
            max-width: 400px;
            margin: 10% auto;
            padding: 20px;
            border-radius: 10px;
            position: relative;
        }
        #talk-close {
            position: absolute;
            top: 10px; right: 15px;
            font-size: 24px;
            cursor: pointer;
        }
        #talk-form input {
            width: 100%; margin-bottom: 10px; padding: 10px;
        }
        #talk-form button {
            width: 100%;
            background: #25D366;
            color: white;
            padding: 12px;
            font-size: 16px;
            border: none;
            border-radius: 5px;
        }
    </style>
    <script>
    jQuery(document).ready(function($){
        $('#talk-whatsapp-button').on('click', function() {
            $('#talk-popup').fadeIn();
        });
        $('#talk-close').on('click', function() {
            $('#talk-popup').fadeOut();
        });
        $('input[name="telefone"]').mask('(00) 00000-0000');

        $('#talk-form').on('submit', function(e){
            e.preventDefault();
            $.post("<?php echo admin_url('admin-ajax.php'); ?>", $(this).serialize(), function(response){
                if(response.success){
                    $('#talk-form').hide();
                    $('#talk-success').fadeIn();
                } else {
                    alert('Erro ao enviar. Tente novamente.');
                }
            });
        });
    });
    </script>
    <?php
});

// Salvar lead
add_action('wp_ajax_talk_form_submit', 'talk_handle_form');
add_action('wp_ajax_nopriv_talk_form_submit', 'talk_handle_form');

function talk_handle_form() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'talk_nonce')) {
        wp_send_json_error();
    }

    $nome = sanitize_text_field($_POST['nome']);
    $telefone = sanitize_text_field($_POST['telefone']);

    if (empty($nome) || empty($telefone)) {
        wp_send_json_error();
    }

    $post_id = wp_insert_post([
        'post_type' => 'talk_lead',
        'post_title' => $nome,
        'post_status' => 'publish',
        'meta_input' => ['telefone' => $telefone, 'status' => 'a_enviar']
    ]);

    wp_send_json($post_id ? ['success' => true] : ['success' => false]);
}

// Página Kanban no painel
add_action('admin_menu', function() {
    add_submenu_page(
        'edit.php?post_type=talk_lead',
        'Kanban de Leads',
        '📌 Kanban',
        'edit_posts',
        'talk-kanban',
        'talk_render_kanban_page'
    );
});

// Render Kanban
function talk_render_kanban_page() {
    $leads = get_posts([
        'post_type' => 'talk_lead',
        'numberposts' => -1,
        'orderby' => 'date',
        'order' => 'DESC'
    ]);

    $columns = [
        'a_enviar' => 'A Enviar',
        'enviado' => 'Enviado',
    ];

    echo '<h2>Kanban de Leads</h2>';
    echo '<div id="talk-kanban">';
    foreach ($columns as $status => $label) {
        echo "<div class='kanban-column' data-status='{$status}'>";
        echo "<h3>{$label}</h3><ul class='kanban-list'>";
        foreach ($leads as $lead) {
            $lead_status = get_post_meta($lead->ID, 'status', true) ?: 'a_enviar';
            if ($lead_status === $status) {
                echo "<li class='kanban-item' data-id='{$lead->ID}'>" . esc_html($lead->post_title) . "<br><small>" . get_post_meta($lead->ID, 'telefone', true) . "</small></li>";
            }
        }
        echo "</ul></div>";
    }
    echo '</div>';
}

// AJAX para atualizar status
add_action('wp_ajax_talk_update_status', function() {
    check_ajax_referer('talk_kanban_nonce', 'nonce');
    $id = intval($_POST['id']);
    $status = sanitize_text_field($_POST['status']);
    update_post_meta($id, 'status', $status);
    wp_send_json_success();
});
