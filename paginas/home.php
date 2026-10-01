<?php 
//Incluindo cabeçalho fixo
include_once ('../includes/header.php');

// Sanitização de entrada da URL
$acao = filter_var(isset($_GET['acao']) ? $_GET['acao'] : 'bemvindo', FILTER_SANITIZE_STRING);

// Mapeamento de caminhos permitidos (Whitelist)
$paginas = [
    'bemvindo'  => '../paginas/conteudo/cadastro_contato.php',
    'editar'    => '../paginas/conteudo/update_contato.php',
    'perfil'    => '../paginas/conteudo/perfil.php',
    'relatorio' => '../paginas/conteudo/relatorio.php'
];

// Verificar se a ação existe no array ou carregar padrão
$pagina_incluir = isset($paginas[$acao]) ? $paginas[$acao] : $paginas['bemvindo'];

include_once ($pagina_incluir);
//Incluindo rodapé fixo
 include_once ('../includes/footer.php');
 ?>