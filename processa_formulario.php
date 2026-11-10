<?php
// Se o arquivo for acessado diretamente, redireciona para a página inicial
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit();
}

// 1. Coleta e sanitiza os dados do formulário (htmlspecialchars ajuda a prevenir XSS)
$nome     = htmlspecialchars(trim($_POST['nome'] ?? ''));
$email    = htmlspecialchars(trim($_POST['email'] ?? ''));
$empresa  = htmlspecialchars(trim($_POST['empresa'] ?? ''));
$mensagem = htmlspecialchars(trim($_POST['mensagem'] ?? ''));

// 2. Validação básica
$sucesso = !(empty($nome) || empty($email) || empty($empresa) || empty($mensagem)
    || !filter_var($email, FILTER_VALIDATE_EMAIL));

if ($sucesso) {
    // 3. Lógica de "back-end" (o que fazer com os dados)
    // --- EXEMPLO: Enviar um e-mail para a equipe comercial ---
    $destinatario = "comercial@techsolv.com.br"; // E-mail da sua empresa
    $assunto = "Novo pedido de orçamento: " . $empresa;

    $corpo_email  = "Você recebeu um novo contato através do site:\n\n";
    $corpo_email .= "Nome: " . $nome . "\n";
    $corpo_email .= "Empresa: " . $empresa . "\n";
    $corpo_email .= "Email: " . $email . "\n";
    $corpo_email .= "Mensagem:\n" . $mensagem . "\n";

    $headers = "From: " . $email;

    // A função mail() do PHP pode ser usada, mas para produção,
    // recomenda-se uma biblioteca como PHPMailer para mais robustez e segurança.
    // mail($destinatario, $assunto, $corpo_email, $headers);
}

$primeiroNome = explode(' ', $nome)[0] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $sucesso ? 'Obrigado!' : 'Verifique os dados' ?> | TechSolv</title>
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
