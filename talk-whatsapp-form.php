<?php

/**
 * Plugin Name: Talk WhatsApp + Kanban
 * Description: Captura leads via formulário flutuante e gerencia em um painel Kanban no admin.
 * Version: 1.2.2
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

// Enqueue Scripts
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_script('jquery-mask', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js', ['jquery'], null, true);
});

add_action('admin_enqueue_scripts', function($hook) {
    // Corrigido: usar underscore em vez de hífen
    if ($hook !== 'talk_lead_page_talk-kanban') return;

    // CSS para o Kanban
    wp_register_style('talk-kanban-css', false);
    wp_enqueue_style('talk-kanban-css');
    wp_add_inline_style('talk-kanban-css', '
        #talk-kanban { 
            display: flex; 
            flex-wrap: nowrap; 
            gap: 20px; 
            margin-top: 20px; 
            overflow-x: auto;
        }
        .kanban-column { 
            flex: 1; 
            min-width: 280px; 
            background: #f1f1f1; 
            border-radius: 6px; 
            padding: 15px; 
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .kanban-column h3 { 
            text-align: center; 
            margin-bottom: 15px; 
            color: #333;
            font-size: 16px;
        }
        .kanban-list { 
            min-height: 400px; 
            list-style: none; 
            margin: 0; 
            padding: 0; 
        }
        .kanban-item { 
            background: white; 
            padding: 12px; 
            margin-bottom: 8px; 
            border-radius: 6px; 
            border: 1px solid #ddd; 
            cursor: move; 
            transition: all 0.2s ease;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            position: relative;
        }
        .kanban-item:hover { 
            background: #f9f9f9; 
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.15);
        }
        .whatsapp-btn {
            position: absolute;
            top: 8px;
            right: 8px;
            background: #25D366;
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s ease;
            z-index: 10;
        }
        .whatsapp-btn:hover {
            background: #1ea952;
            transform: scale(1.1);
        }
        .kanban-item-content {
            padding-right: 40px;
        }
        .kanban-placeholder { 
            background: #e8f5e8; 
            height: 60px; 
            margin-bottom: 8px; 
            border: 2px dashed #4caf50; 
            border-radius: 6px; 
            display: flex;
            align-items: center;
            justify-content: center;
            color: #4caf50;
            font-weight: bold;
        }
        .kanban-placeholder:before {
            content: "Solte aqui";
        }
    ');

    // Scripts necessários
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-sortable');
    wp_enqueue_script('jquery-ui-draggable');
    wp_enqueue_script('jquery-ui-droppable');
});

// Botão flutuante e formulário no site
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
            box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3);
            transition: all 0.3s ease;
        }
        #talk-whatsapp-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(37, 211, 102, 0.4);
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
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        #talk-form button {
            width: 100%;
            background: #25D366;
            color: white;
            padding: 12px;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
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
            $.post('<?php echo admin_url('admin-ajax.php'); ?>', $(this).serialize(), function(response){
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

// AJAX para envio do formulário
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
        'meta_input' => [
            'telefone' => $telefone,
            'status' => 'a_enviar'
        ]
    ]);

    wp_send_json($post_id ? ['success' => true] : ['success' => false]);
}

// Página Kanban
add_action('admin_menu', function() {
    add_submenu_page(
        'edit.php?post_type=talk_lead',
        'Kanban de Leads',
        '📌 Kanban',
        'edit_posts',
        'talk-kanban',
        'talk_render_kanban_page'
    );
    
    // Nova página de configurações
    add_submenu_page(
        'edit.php?post_type=talk_lead',
        'Configurações da Talk',
        '⚙️ Configurações',
        'manage_options',
        'talk-settings',
        'talk_render_settings_page'
    );
});

function talk_render_settings_page() {
    // Mensagem padrão
    $default_message = "Olá {nome}. Agradeço o interesse na palestra, segue o material que você pediu: https://drive.google.com/drive/folders/1uG9vXIcVUf_a38UQcy2J95-kXhAyGQ1s";
    $message = get_option('talk_whatsapp_message', $default_message);
    
    echo '<div class="wrap">';
    echo '<h1>Configurações da Talk WhatsApp</h1>';
    echo '<form method="post" action="options.php">';
    settings_fields('talk_settings_group');
    do_settings_sections('talk_settings_group');
    
    echo '<table class="form-table">';
    echo '<tr>';
    echo '<th scope="row">Mensagem padrão do WhatsApp</th>';
    echo '<td>';
    echo '<textarea name="talk_whatsapp_message" rows="5" cols="70" class="large-text">' . esc_textarea($message) . '</textarea>';
    echo '<p class="description">';
    echo '<strong>Instruções:</strong><br>';
    echo '• Use <code>{nome}</code> para incluir o nome do contato automaticamente<br>';
    echo '• Exemplo: "Olá {nome}. Agradeço o interesse na palestra..."<br>';
    echo '• A mensagem será enviada quando você clicar no botão do WhatsApp no Kanban';
    echo '</p>';
    echo '</td>';
    echo '</tr>';
    echo '</table>';
    
    submit_button('Salvar Configurações');
    echo '</form>';
    echo '</div>';
}

// Registrar as configurações
add_action('admin_init', function() {
    register_setting('talk_settings_group', 'talk_whatsapp_message');
});

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

    echo '<div class="wrap">';
    echo '<h1>Kanban de Leads</h1>';
    echo '<div id="talk-kanban">';
    
    foreach ($columns as $status => $label) {
        echo "<div class='kanban-column' data-status='{$status}'>";
        echo "<h3>{$label}</h3><ul class='kanban-list' data-status='{$status}'>";
        
        foreach ($leads as $lead) {
            $lead_status = get_post_meta($lead->ID, 'status', true) ?: 'a_enviar';
            if ($lead_status === $status) {
                $telefone = get_post_meta($lead->ID, 'telefone', true);
                $nome = esc_html($lead->post_title);
                
                // Limpar telefone para WhatsApp (remover caracteres especiais)
                $telefone_limpo = preg_replace('/\D/', '', $telefone);
                
                // Buscar mensagem configurada
                $default_message = "Olá {nome}. Agradeço o interesse na palestra, segue o material que você pediu: https://drive.google.com/drive/folders/1uG9vXIcVUf_a38UQcy2J95-kXhAyGQ1s";
                $mensagem_template = get_option('talk_whatsapp_message', $default_message);
                
                // Substituir o placeholder {nome} pelo nome real
                $mensagem = str_replace('{nome}', $nome, $mensagem_template);
                $mensagem_encoded = urlencode($mensagem);
                
                // Link do WhatsApp
                $whatsapp_link = "https://wa.me/55{$telefone_limpo}?text={$mensagem_encoded}";
                
                echo "<li class='kanban-item' data-id='{$lead->ID}'>" 
                   . "<button class='whatsapp-btn' onclick='window.open(\"{$whatsapp_link}\", \"_blank\")' title='Enviar mensagem via WhatsApp'>💬</button>"
                   . "<div class='kanban-item-content'>"
                   . "<strong>{$nome}</strong><br>"
                   . "<small>📱 {$telefone}</small><br>"
                   . "<small>📅 " . get_the_date('d/m/Y H:i', $lead->ID) . "</small>"
                   . "</div>"
                   . "</li>";
            }
        }
        
        echo "</ul></div>";
    }
    echo '</div>';
    echo '</div>';
    
    // JavaScript inline na página
    ?>
    <script>
    jQuery(document).ready(function($) {
        // Aguarda o DOM estar completamente carregado
        setTimeout(function() {
            $(".kanban-list").sortable({
                connectWith: ".kanban-list",
                placeholder: "kanban-placeholder",
                cursor: "move",
                tolerance: "pointer",
                revert: 100,
                start: function(event, ui) {
                    ui.placeholder.height(ui.item.height());
                },
                receive: function(event, ui) {
                    const postId = ui.item.data("id");
                    const newStatus = $(this).data("status");
                    
                    console.log('Movendo lead:', postId, 'para:', newStatus);
                    
                    $.post(ajaxurl, {
                        action: "talk_update_status",
                        id: postId,
                        status: newStatus,
                        nonce: "<?php echo wp_create_nonce('talk_kanban_nonce'); ?>"
                    }, function(response) {
                        if (response.success) {
                            console.log('Status atualizado com sucesso');
                        } else {
                            console.error('Erro ao atualizar status');
                        }
                    });
                },
                // Impede o drag quando clica no botão do WhatsApp
                cancel: ".whatsapp-btn"
            });

            $(".kanban-item").disableSelection();
        }, 500);
    });
    </script>
    <?php
}

// AJAX para atualizar status
add_action('wp_ajax_talk_update_status', function() {
    check_ajax_referer('talk_kanban_nonce', 'nonce');
    
    $id = intval($_POST['id']);
    $status = sanitize_text_field($_POST['status']);
    
    $updated = update_post_meta($id, 'status', $status);
    
    if ($updated !== false) {
        wp_send_json_success(['message' => 'Status atualizado']);
    } else {
        wp_send_json_error(['message' => 'Erro ao atualizar']);
    }
});