<?php
// Se o arquivo for acessado diretamente, redireciona para a página inicial
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

require __DIR__ . '/vendor/autoload.php';

// 1. Coleta os dados brutos (o escape para HTML é feito só na hora de exibir)
$limpaLinha = fn($v) => trim(str_replace(["\r", "\n"], ' ', (string) $v));
$nomeRaw     = $limpaLinha($_POST['nome'] ?? '');
$emailRaw    = $limpaLinha($_POST['email'] ?? '');
$empresaRaw  = $limpaLinha($_POST['empresa'] ?? '');
$mensagemRaw = trim((string) ($_POST['mensagem'] ?? ''));

// 2. Validação básica
$valido = !($nomeRaw === '' || $emailRaw === '' || $empresaRaw === '' || $mensagemRaw === ''
    || !filter_var($emailRaw, FILTER_VALIDATE_EMAIL));

// 3. Envio do e-mail via SMTP (Gmail)
$erroEnvio = false;
if ($valido) {
    $config = is_file(__DIR__ . '/config.php') ? require __DIR__ . '/config.php' : null;

    try {
        if (!$config) {
            throw new MailException('Arquivo config.php não encontrado.');
        }

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = $config['smtp_host'];
        $mail->Port       = $config['smtp_port'];
        $mail->SMTPAuth   = true;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Username   = $config['smtp_usuario'];
        $mail->Password   = $config['smtp_senha'];
        $mail->CharSet    = PHPMailer::CHARSET_UTF8;

        // Envia pela própria conta; o visitante entra como "Responder para"
        $mail->setFrom($config['smtp_usuario'], $config['remetente_nome']);
        $mail->addAddress($config['destinatario']);
        $mail->addReplyTo($emailRaw, $nomeRaw);

        $mail->Subject = 'Novo pedido de orçamento: ' . $empresaRaw;
        $mail->Body    = "Você recebeu um novo contato através do site:\n\n"
            . "Nome: {$nomeRaw}\n"
            . "Empresa: {$empresaRaw}\n"
            . "E-mail: {$emailRaw}\n\n"
            . "Mensagem:\n{$mensagemRaw}\n";

        $mail->send();
    } catch (MailException $e) {
        $erroEnvio = true;
        error_log('Falha ao enviar formulário de contato: ' . (isset($mail) ? $mail->ErrorInfo : $e->getMessage()));
    }
}

$sucesso = $valido && !$erroEnvio;

// Versões escapadas para exibição na página
$nome  = htmlspecialchars($nomeRaw);
$email = htmlspecialchars($emailRaw);
$primeiroNome = explode(' ', $nome)[0] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $sucesso ? 'Obrigado!' : ($erroEnvio ? 'Erro no envio' : 'Verifique os dados') ?> | TechSolv</title>
    <meta name="theme-color" content="#12649E">
    <link rel="icon" type="image/svg+xml" href="assets/img/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .result-page {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 2rem 1rem;
            background:
                radial-gradient(600px 300px at 100% 100%, rgba(241, 122, 28, .25), transparent 70%),
                linear-gradient(135deg, var(--brand-blue-900), var(--brand-blue));
        }

        .result-card {
            max-width: 520px;
            width: 100%;
            background: #fff;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            padding: 2.75rem 2rem;
            text-align: center;
        }

        .result-icon {
            display: grid;
            place-items: center;
            width: 76px;
            height: 76px;
            margin: 1.75rem auto 1.25rem;
            border-radius: 50%;
            font-size: 2.2rem;
        }

        .result-icon.ok {
            background: var(--brand-blue-50);
            color: var(--brand-blue);
        }

        .result-icon.err {
            background: var(--brand-orange-50);
            color: var(--brand-orange-600);
        }
    </style>
</head>

<body>
    <main class="result-page">
        <div class="result-card">
            <a href="index.php"><img src="assets/img/logo.svg" alt="TechSolv" height="40"></a>

            <?php if ($sucesso): ?>
                <div class="result-icon ok"><i class="bi bi-check-lg"></i></div>
                <h1 class="h3 fw-bold">Obrigado, <?= $primeiroNome ?>!</h1>
                <p class="text-muted mb-4">Sua solicitação de orçamento foi recebida com sucesso. Nossa equipe
                    entrará em contato em até 24 horas no e-mail <strong><?= $email ?></strong>.</p>
                <a href="index.php" class="btn btn-accent btn-lg"><i class="bi bi-arrow-left me-1"></i> Voltar para o site</a>
            <?php elseif ($erroEnvio): ?>
                <div class="result-icon err"><i class="bi bi-exclamation-lg"></i></div>
                <h1 class="h3 fw-bold">Não foi possível enviar</h1>
                <p class="text-muted mb-4">Tivemos um problema ao enviar sua mensagem. Tente novamente em alguns
                    minutos ou fale com a gente direto pelo
                    <a href="https://wa.me/5569993885148" target="_blank" rel="noopener">WhatsApp</a>.</p>
                <a href="index.php#contato" class="btn btn-accent btn-lg"><i class="bi bi-arrow-left me-1"></i> Voltar ao formulário</a>
            <?php else: ?>
                <div class="result-icon err"><i class="bi bi-exclamation-lg"></i></div>
                <h1 class="h3 fw-bold">Verifique os dados</h1>
                <p class="text-muted mb-4">Por favor, preencha todos os campos corretamente e informe um e-mail
                    válido.</p>
                <a href="index.php#contato" class="btn btn-accent btn-lg"><i class="bi bi-arrow-left me-1"></i> Voltar ao formulário</a>
            <?php endif; ?>
        </div>
    </main>
</body>

</html>
