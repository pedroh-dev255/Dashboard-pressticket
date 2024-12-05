<?php
    session_start();

    if (!isset($_SESSION['login'])) {
        header("Location: ./login.php");
        $_SESSION['log'] = "Realize o login!";
        $_SESSION['log1'] = "warning"; // success, warning, error
        exit();
    }

    if (isset($_GET['logoff']) && $_GET['logoff'] == 'true') {
        session_destroy();
        header("Location: ./login.php");
        exit();
    }
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="https://whaticket.clinicadrhenriquefurtado.com.br/android-chrome-192x192.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <title>Dashboard Avançado</title>
    <link rel="stylesheet" href="./style/popup.css">
    <link rel="stylesheet" href="./style/geral.css">
    <style>
        @media print {
            html, body {
                margin: 0;
                padding: 0;
                width: 100%;
            }
        }
    </style>
    <script src="./js/all.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body style="background-color: #84c5fb;">
    <div class="popin-notification" id="popin">
        <p id="popin-text"></p>
        <button onclick="closePopin()">Fechar</button>
    </div>
    <nav class="navbar">
        <div class="container-fluid">
            <form class="d-flex ms-auto" action="./" method="get">
                <input type="hidden" name="logoff" value='true'>
                <input type="submit" class="btn btn-danger" value="Deslogar">
            </form>
        </div>
    </nav>
    <div class="container" id="printableArea">
        <h2>Dashboard Avançado Press Ticket</h2>
        <br>

        <iframe width="100%" height="700px" src="https://lookerstudio.google.com/embed/reporting/29b4fb32-5611-4f0e-9a42-6d4eec7e1e7c/page/pM9PD" frameborder="0" style="border:0" allowfullscreen sandbox="allow-storage-access-by-user-activation allow-scripts allow-same-origin allow-popups allow-popups-to-escape-sandbox"></iframe>

        <footer class="py-3 my-4">
            <b><p class="text-center text-muted">©<?php echo date('Y'); ?> <a href="https://phsolucoes.tech">PH Soluções</a></p></b>
        </footer>
    </div>

    <?php
        if(isset($_SESSION['log'])){
            echo "<script >showPopin('".$_SESSION['log']."', '".$_SESSION['log1']."');</script>";
            unset($_SESSION['log'], $_SESSION['log1']);
        }
    ?>
</body>
</html>
