<?php
// Upload e remoção de imagens dos veículos
// Os arquivos são salvos em /uploads com nome gerado pelo servidor
// (nunca com o nome enviado pelo navegador, por segurança)

require_once __DIR__ . '/helpers.php';

const UPLOAD_MAX_BYTES = 5242880; // 5 MB

function upload_dir(): string
{
    $dir = dirname(__DIR__) . '/uploads';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    return $dir . '/';
}

function upload_url(string $arquivo): string
{
    return url('uploads/' . basename($arquivo));
}

/**
 * Valida e guarda a imagem enviada.
 * Retorna [nome_do_arquivo | null, erro | null].
 * [null, null] significa que nenhum arquivo foi enviado.
 */
function upload_imagem(array $file): array
{
    $erro = $file['error'] ?? UPLOAD_ERR_NO_FILE;

    if ($erro === UPLOAD_ERR_NO_FILE) {
        return [null, null];
    }
    if ($erro !== UPLOAD_ERR_OK) {
        return [null, 'Falha no upload da imagem. Verifique a conexão e tente de novo.'];
    }
    if ($file['size'] > UPLOAD_MAX_BYTES) {
        return [null, 'A imagem deve ter no máximo 5 MB.'];
    }

    $tmp = $file['tmp_name'];
    if (!is_uploaded_file($tmp)) {
        return [null, 'Arquivo de imagem inválido.'];
    }

    // Confirma que o conteúdo é realmente uma imagem
    if (@getimagesize($tmp) === false) {
        return [null, 'O arquivo enviado não é uma imagem válida.'];
    }

    // O tipo deve ser detectado no conteúdo, não no nome do arquivo
    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mime     = $finfo->file($tmp);
    $extensoes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    if (!isset($extensoes[$mime])) {
        return [null, 'Formato não permitido. Use imagens JPG, PNG ou WEBP.'];
    }

    $nome = bin2hex(random_bytes(16)) . '.' . $extensoes[$mime];

    if (!move_uploaded_file($tmp, upload_dir() . $nome)) {
        return [null, 'Não foi possível guardar a imagem no servidor.'];
    }

    return [$nome, null];
}

/** Remove uma imagem do disco (ignora caminhos fora da pasta uploads). */
function upload_remover(?string $arquivo): void
{
    if ($arquivo === null || $arquivo === '') {
        return;
    }

    $caminho = upload_dir() . basename($arquivo);
    if (is_file($caminho)) {
        @unlink($caminho);
    }
}
