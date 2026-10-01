<?php

// ============================================================
// CONEXÃO COM O BANCO
// ============================================================

include('../config/conexao.php');


// ============================================================
// ATUALIZAR PERFIL
// ============================================================

if (isset($_POST['upPerfil'])) {

    // Dados enviados pelo formulário
    $nome  = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    // Mantém a foto atual caso nenhuma nova seja enviada
    $foto = $foto_user;

    // --------------------------------------------------------
    // VALIDAÇÃO BÁSICA
    // --------------------------------------------------------

    if (empty($nome) || empty($email)) {

        echo '<div class="container mt-3">
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">
                        &times;
                    </button>

                    <h5>
                        <i class="icon fas fa-times"></i>
                        Erro!
                    </h5>

                    Nome e e-mail são obrigatórios.
                </div>
              </div>';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        echo '<div class="container mt-3">
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">
                        &times;
                    </button>

                    <h5>
                        <i class="icon fas fa-times"></i>
                        Erro!
                    </h5>

                    Informe um endereço de e-mail válido.
                </div>
              </div>';

    } else {

        // ====================================================
        // UPLOAD DA FOTO
        // ====================================================

        if (
            isset($_FILES['foto']) &&
            $_FILES['foto']['error'] === UPLOAD_ERR_OK
        ) {

            // Extensão do arquivo
            $extensao = strtolower(
                pathinfo(
                    $_FILES['foto']['name'],
                    PATHINFO_EXTENSION
                )
            );

            // Extensões permitidas
            $formatosPermitidos = [
                'jpg',
                'jpeg',
                'png',
                'gif'
            ];

            if (!in_array($extensao, $formatosPermitidos)) {

                echo '<div class="container mt-3">
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button"
                                    class="close"
                                    data-dismiss="alert">
                                &times;
                            </button>

                            <h5>
                                <i class="icon fas fa-times"></i>
                                Erro!
                            </h5>

                            Formato de imagem não permitido.
                            Use JPG, JPEG, PNG ou GIF.
                        </div>
                      </div>';

            } else {

                // ====================================================
                // PASTA DAS FOTOS DOS USUÁRIOS
                // ====================================================

                $pasta = '../img/user/';

                // Cria a pasta caso ela não exista
                if (!is_dir($pasta)) {

                    mkdir($pasta, 0755, true);
                }

                // Nome único para a nova imagem
                $novoNome = uniqid('avatar_') . '.' . $extensao;

                // Caminho completo
                $destino = $pasta . $novoNome;


                // ====================================================
                // MOVE A IMAGEM PARA A PASTA
                // ====================================================

                if (
                    move_uploaded_file(
                        $_FILES['foto']['tmp_name'],
                        $destino
                    )
                ) {

                    // ------------------------------------------------
                    // APAGA A FOTO ANTIGA
                    // ------------------------------------------------

                    // Não apaga o avatar padrão
                    if (
                        !empty($foto_user) &&
                        $foto_user !== 'avatar-padrao.png'
                    ) {

                        $fotoAntiga = $pasta . $foto_user;

                        if (file_exists($fotoAntiga)) {

                            unlink($fotoAntiga);
                        }
                    }

                    // Nova foto será salva no banco
                    $foto = $novoNome;

                } else {

                    echo '<div class="container mt-3">
                            <div class="alert alert-danger alert-dismissible">
                                <button type="button"
                                        class="close"
                                        data-dismiss="alert">
                                    &times;
                                </button>

                                <h5>
                                    <i class="icon fas fa-times"></i>
                                    Erro!
                                </h5>

                                Não foi possível salvar a nova foto.
                            </div>
                          </div>';

                    // Mantém a foto antiga
                    $foto = $foto_user;
                }
            }
        }


        // ====================================================
        // ATUALIZAÇÃO DO BANCO DE DADOS
        // ====================================================

        try {

            // ------------------------------------------------
            // COM SENHA NOVA
            // ------------------------------------------------

            if (!empty($senha)) {

                // Cria hash seguro da senha
                $senhaHash = password_hash(
                    $senha,
                    PASSWORD_DEFAULT
                );

                $sql = "UPDATE tb_user
                        SET
                            nome_user = :nome,
                            email_user = :email,
                            senha_user = :senha,
                            foto_user = :foto
                        WHERE id_user = :id";

                $stmt = $conect->prepare($sql);

                $stmt->bindValue(
                    ':senha',
                    $senhaHash,
                    PDO::PARAM_STR
                );

            }

            // ------------------------------------------------
            // SEM ALTERAR A SENHA
            // ------------------------------------------------

            else {

                $sql = "UPDATE tb_user
                        SET
                            nome_user = :nome,
                            email_user = :email,
                            foto_user = :foto
                        WHERE id_user = :id";

                $stmt = $conect->prepare($sql);
            }


            // ------------------------------------------------
            // PARÂMETROS
            // ------------------------------------------------

            $stmt->bindValue(
                ':nome',
                $nome,
                PDO::PARAM_STR
            );

            $stmt->bindValue(
                ':email',
                $email,
                PDO::PARAM_STR
            );

            $stmt->bindValue(
                ':foto',
                $foto,
                PDO::PARAM_STR
            );

            $stmt->bindValue(
                ':id',
                $id_user,
                PDO::PARAM_INT
            );


            // ------------------------------------------------
            // EXECUTA
            // ------------------------------------------------

            if ($stmt->execute()) {

                // Atualiza o login da sessão
                $_SESSION['loginUser'] = $email;

                // Redireciona para o perfil
                header(
                    "Location: home.php?acao=perfil"
                );

                exit;

            } else {

                echo '<div class="container mt-3">
                        <div class="alert alert-danger">
                            Não foi possível atualizar os dados do usuário.
                        </div>
                      </div>';
            }

        } catch (PDOException $e) {

            echo '<div class="container mt-3">
                    <div class="alert alert-danger">
                        <strong>Erro no banco de dados:</strong><br>
                        ' . htmlspecialchars($e->getMessage()) . '
                    </div>
                  </div>';
        }
    }
}

?>


<!-- ============================================================
     CONTENT WRAPPER
============================================================= -->

<div class="content-wrapper">

    <!-- ========================================================
         CABEÇALHO
    ========================================================= -->

    <section class="content-header">

        <div class="container-fluid">

            <div class="row mb-2">

                <div class="col-sm-6">

                    <h1>Editar Perfil</h1>

                </div>

            </div>

        </div>

    </section>


    <!-- ========================================================
         CONTEÚDO
    ========================================================= -->

    <section class="content">

        <div class="container-fluid">

            <div class="row">


                <!-- ====================================================
                     FORMULÁRIO
                ===================================================== -->

                <div class="col-md-6">

                    <div class="card card-primary">


                        <!-- CABEÇALHO DO CARD -->

                        <div class="card-header">

                            <h3 class="card-title">
                                Editar Perfil
                            </h3>

                        </div>


                        <!-- ====================================================
                             FORMULÁRIO
                        ===================================================== -->

                        <form
                            role="form"
                            action=""
                            method="post"
                            enctype="multipart/form-data"
                        >

                            <div class="card-body">


                                <!-- ====================================================
                                     NOME
                                ===================================================== -->

                                <div class="form-group">

                                    <label for="nome">
                                        Nome
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control"
                                        name="nome"
                                        id="nome"
                                        required
                                        value="<?php
                                            echo htmlspecialchars(
                                                $nome_user ?? '',
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );
                                        ?>"
                                    >

                                </div>


                                <!-- ====================================================
                                     E-MAIL
                                ===================================================== -->

                                <div class="form-group">

                                    <label for="email">
                                        Endereço de E-mail
                                    </label>

                                    <input
                                        type="email"
                                        class="form-control"
                                        name="email"
                                        id="email"
                                        required
                                        value="<?php
                                            echo htmlspecialchars(
                                                $email_user ?? '',
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );
                                        ?>"
                                    >

                                </div>


                                <!-- ====================================================
                                     SENHA
                                ===================================================== -->

                                <div class="form-group">

                                    <label for="senha">
                                        Nova Senha
                                    </label>

                                    <input
                                        type="password"
                                        class="form-control"
                                        name="senha"
                                        id="senha"
                                        value=""
                                        placeholder="Digite uma nova senha somente se quiser alterar"
                                    >

                                    <small class="form-text text-muted">
                                        Deixe em branco para manter a senha atual.
                                    </small>

                                </div>


                                <!-- ====================================================
                                     FOTO
                                ===================================================== -->

                                <div class="form-group">

                                    <label for="foto">
                                        Avatar do usuário
                                    </label>

                                    <div class="input-group">

                                        <div class="custom-file">

                                            <input
                                                type="file"
                                                class="custom-file-input"
                                                name="foto"
                                                id="foto"
                                                accept=".jpg,.jpeg,.png,.gif"
                                            >

                                            <label
                                                class="custom-file-label"
                                                for="foto"
                                            >
                                                Escolher imagem
                                            </label>

                                        </div>

                                    </div>

                                    <small class="form-text text-muted">
                                        Formatos permitidos:
                                        JPG, JPEG, PNG e GIF.
                                    </small>

                                </div>


                            </div>


                            <!-- ====================================================
                                 RODAPÉ DO CARD
                            ===================================================== -->

                            <div class="card-footer">

                                <button
                                    type="submit"
                                    name="upPerfil"
                                    class="btn btn-primary"
                                >

                                    <i class="fas fa-save"></i>

                                    Alterar dados do usuário

                                </button>

                            </div>

                        </form>

                    </div>

                </div>


                <!-- ====================================================
                     DADOS DO USUÁRIO
                ===================================================== -->

                <div class="col-md-6">

                    <div class="card">


                        <!-- CABEÇALHO -->

                        <div class="card-header">

                            <h3 class="card-title">
                                Dados do Usuário
                            </h3>

                        </div>


                        <!-- CORPO -->

                        <div
                            class="card-body"
                            style="
                                text-align: center;
                                margin-bottom: 98px;
                            "
                        >

                            <?php

                            // ====================================================
                            // DEFINE O CAMINHO DA FOTO
                            // ====================================================

                            if (
                                empty($foto_user) ||
                                $foto_user === 'avatar-padrao.png'
                            ) {

                                // Avatar padrão
                                $caminhoFoto =
                                    '../img/avatar_p/avatar-padrao.png';

                            } else {

                                // Foto enviada pelo usuário
                                $caminhoFoto =
                                    '../img/user/' . $foto_user;
                            }

                            ?>


                            <!-- ====================================================
                                 FOTO
                            ===================================================== -->

                            <img
                                src="<?php
                                    echo htmlspecialchars(
                                        $caminhoFoto,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    );
                                ?>"
                                alt="Foto do usuário"
                                title="Foto do usuário"
                                style="
                                    width: 200px;
                                    height: 200px;
                                    object-fit: cover;
                                    border-radius: 50%;
                                    margin-top: 30px;
                                "
                                onerror="this.src='../img/avatar_p/avatar-padrao.png';"
                            >


                            <!-- ====================================================
                                 NOME
                            ===================================================== -->

                            <h1>

                                <?php

                                echo htmlspecialchars(
                                    $nome_user ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                                );

                                ?>

                            </h1>


                            <!-- ====================================================
                                 E-MAIL
                            ===================================================== -->

                            <strong>

                                <?php

                                echo htmlspecialchars(
                                    $email_user ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                                );

                                ?>

                            </strong>


                        </div>

                    </div>

                </div>


            </div>

        </div>

    </section>

</div>


<!-- ============================================================
     SCRIPT PARA MOSTRAR O NOME DO ARQUIVO SELECIONADO
============================================================= -->

<script>

document.getElementById('foto').addEventListener('change', function (e) {

    var nomeArquivo = e.target.files.length > 0
        ? e.target.files[0].name
        : 'Escolher imagem';

    var label = document.querySelector(
        'label[for="foto"]'
    );

    if (label) {
        label.textContent = nomeArquivo;
    }

});

</script>
