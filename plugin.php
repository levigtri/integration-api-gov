<?php
/*
Plugin Name: IBGE Metadados Integrador
Description: Plugin para buscar metadados detalhados da API do IBGE diretamente no WordPress.
Version: 2.4
Author: OpenAI
*/

if (!defined('ABSPATH')) exit;

// Enfileira JS e CSS
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_script('jquery');
    wp_add_inline_script('jquery', <<<'JS'
jQuery(document).ready(function($) {
    console.log("Plugin IBGE carregado.");

    function carregarPesquisas() {
        console.log("Carregando pesquisas...");
        $.get(ibge_ajax.ajax_url, { action: 'ibge_get_pesquisas' }, function(data) {
            if (!data || !Array.isArray(data)) {
                console.error("Erro: resposta inválida de pesquisas");
                return;
            }
            const $select = $('#ibge-pesquisas').empty();
            $select.append('<option value="">Selecione</option>');
            data.forEach(p => {
                $select.append(`<option value="${p.codigo}">${p.nome}</option>`);
            });
        }).fail(() => {
            console.error("Erro ao carregar pesquisas.");
        });
    }

    function carregarPeriodos(pesquisa) {
        console.log("Carregando anos para pesquisa:", pesquisa);
        $('#ibge-anos').empty().append('<option value="">Carregando...</option>');
        $.get(ibge_ajax.ajax_url, { action: 'ibge_get_periodos', pesquisa }, function(data) {
            console.log("Resposta de periodos:", data);
            if (!data || !Array.isArray(data)) {
                $('#ibge-anos').empty().append('<option value="">Erro ao carregar</option>');
                return;
            }
            const $select = $('#ibge-anos').empty();
            $select.append('<option value="">Selecione</option>');
            const anos = [...new Set(data.map(p => p.ano))];
            if (anos.length === 0) {
                $select.append('<option value="">Nenhum ano disponível</option>');
            } else {
                anos.forEach(ano => {
                    $select.append(`<option value="${ano}">${ano}</option>`);
                });
            }
        }).fail(() => {
            $('#ibge-anos').empty().append('<option value="">Erro na requisição</option>');
        });
    }

    function formatarMetadados(data) {
        return `
            <div class="metadados-container">
                <h3>${data.nome}</h3>
                <div class="metadados-grid">
                    <div><strong>Publicação:</strong> ${data.dataPublicacao || 'Não informado'}</div>
                    <div><strong>Período:</strong> ${data.periodicidade}</div>
                    <div><strong>Área:</strong> ${data.area}</div>
                    ${data.contato ? `<div><strong>Contato:</strong> ${data.contato}</div>` : ''}
                </div>
                ${data.variaveis ? `
                <h4>Variáveis Analisadas:</h4>
                <ul class="variaveis-list">
                    ${data.variaveis.map(v => `<li>${v.nome}</li>`).join('')}
                </ul>` : ''}
            </div>
        `;
    }

    function consultarMetadados(pesquisa, ano, mes, ordem) {
        console.log("Consultando metadados:", { pesquisa, ano, mes, ordem });
        $('#ibge-resultados').html('<div class="loading">Carregando...</div>');
        
        $.get(ibge_ajax.ajax_url, {
            action: 'ibge_get_metadados',
            pesquisa, ano, mes, ordem
        }, function(data) {
            console.log("Resposta da consulta:", data); // Log da resposta
            if (!data) {
                $('#ibge-resultados').html('<div class="erro">Erro ao buscar metadados. Verifique os parâmetros.</div>');
                return;
            }
            $('#ibge-resultados').html(data ? formatarMetadados(data) : '<em>Nenhum dado encontrado</em>');
        }).fail((jqXHR, textStatus, errorThrown) => {
            console.error("Erro na requisição:", textStatus, errorThrown); // Log de erro
            $('#ibge-resultados').html('<div class="erro">Falha na comunicação com o servidor</div>');
        });
    }

    carregarPesquisas();

    $('#ibge-metadados').on('change', '#ibge-pesquisas', function() {
        const val = $(this).val();
        if (val) carregarPeriodos(val);
    });

    $('#ibge-metadados').on('click', '#ibge-consultar', function() {
        const pesquisa = $('#ibge-pesquisas').val();
        const ano = $('#ibge-anos').val(); // O ano agora é opcional
        
        $('select').removeClass('erro');
        
        if (!pesquisa) {
            $('#ibge-pesquisas').addClass('erro');
            alert("Selecione uma pesquisa.");
            return;
        }

        const mes = $('#ibge-meses').val();
        const ordem = $('#ibge-ordem').val();
        consultarMetadados(pesquisa, ano || null, mes || null, ordem || null); // Passa null se o ano não for selecionado
    });
});
JS
    );

    wp_add_inline_style('wp-block-library', <<<'CSS'
#ibge-metadados {
    margin: 20px 0;
    padding: 15px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

#ibge-metadados label {
    display: block;
    margin-bottom: 15px;
}

#ibge-metadados select, #ibge-metadados input {
    padding: 8px;
    width: 100%;
    max-width: 300px;
    margin-top: 5px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

#ibge-consultar {
    background: #0073aa;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.3s;
}

#ibge-consultar:hover {
    background: #005177;
}

#ibge-resultados {
    margin-top: 20px;
    padding: 15px;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
}

.metadados-container {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.metadados-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 15px;
    margin: 15px 0;
}

.variaveis-list {
    columns: 2;
    margin: 10px 0;
    padding-left: 20px;
}

select.erro {
    border-color: #dc3545;
    background: #fff0f0;
}

.loading {
    color: #666;
    font-style: italic;
}

.erro {
    color: #dc3545;
    padding: 10px;
    background: #fff0f0;
    border-radius: 4px;
}
CSS
    );

    wp_localize_script('jquery', 'ibge_ajax', [
        'ajax_url' => admin_url('admin-ajax.php')
    ]);
});

// Shortcode para exibir o formulário
add_shortcode('ibge_pesquisa', function () {
    ob_start(); ?>
    <div id="ibge-metadados">
        <label>Pesquisa:
            <select id="ibge-pesquisas"></select>
        </label>
        <label>Ano:
            <select id="ibge-anos"></select>
        </label>
        <label>Mês:
            <select id="ibge-meses">
                <option value="">(opcional)</option>
                <?php for ($i = 1; $i <= 12; $i++): ?>
                    <option value="<?php echo $i; ?>"><?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?></option>
                <?php endfor; ?>
            </select>
        </label>
        <label>Ordem:
            <input type="number" id="ibge-ordem" min="0" placeholder="(opcional)">
        </label>
        <button id="ibge-consultar">Consultar</button>
        <div id="ibge-resultados"></div>
    </div>
    <?php
    return ob_get_clean();
});

// AJAX: Buscar pesquisas
add_action('wp_ajax_ibge_get_pesquisas', 'ibge_get_pesquisas');
add_action('wp_ajax_nopriv_ibge_get_pesquisas', 'ibge_get_pesquisas');
function ibge_get_pesquisas() {
    $response = wp_remote_get('https://servicodados.ibge.gov.br/api/v2/metadados/pesquisas');
    
    if (is_wp_error($response)) {
        wp_send_json_error('Erro na conexão com o IBGE', 500);
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body);
    
    wp_send_json($data ?: []);
}

// AJAX: Buscar anos (períodos)
add_action('wp_ajax_ibge_get_periodos', 'ibge_get_periodos');
add_action('wp_ajax_nopriv_ibge_get_periodos', 'ibge_get_periodos');
function ibge_get_periodos() {
    $pesquisa = sanitize_text_field($_GET['pesquisa']);
    
    if (empty($pesquisa)) {
        wp_send_json_error('Código da pesquisa inválido', 400);
    }
    
    $url = "https://servicodados.ibge.gov.br/api/v2/metadados/pesquisas/{$pesquisa}/periodos";
    $response = wp_remote_get($url);
    
    if (is_wp_error($response)) {
        wp_send_json_error('Erro na comunicação com o IBGE', 500);
    }
    
    $status = wp_remote_retrieve_response_code($response);
    if ($status !== 200) {
        wp_send_json_error('Dados não encontrados', 404);
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    $periodos = isset($data['periodos']) ? $data['periodos'] : [];
    
    wp_send_json($periodos);
}

// AJAX: Buscar metadados
add_action('wp_ajax_ibge_get_metadados', 'ibge_get_metadados');
add_action('wp_ajax_nopriv_ibge_get_metadados', 'ibge_get_metadados');
function ibge_get_metadados() {
    $params = [
        'pesquisa' => sanitize_text_field($_GET['pesquisa']),
        'ano' => isset($_GET['ano']) ? intval($_GET['ano']) : null,
        'mes' => isset($_GET['mes']) ? intval($_GET['mes']) : null,
        'ordem' => isset($_GET['ordem']) ? intval($_GET['ordem']) : null
    ];

    if (!$params['pesquisa']) {
        wp_send_json_error('Parâmetros inválidos', 400);
    }

    $url = "https://servicodados.ibge.gov.br/api/v2/metadados/{$params['pesquisa']}";
    if ($params['ano']) $url .= "/{$params['ano']}";
    if ($params['mes']) $url .= "/{$params['mes']}";
    if ($params['ordem']) $url .= "/{$params['ordem']}";

    // Log da URL que está sendo chamada
    error_log("URL da requisição: " . $url);

    $response = wp_remote_get($url);
    
    if (is_wp_error($response)) {
        wp_send_json_error('Erro na requisição', 500);
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body);
    
    // Log da resposta da API
    error_log("Resposta da API: " . print_r($data, true));

    wp_send_json($data ?: null);
}