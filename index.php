<?php

    session_start();

    if (!isset($_SESSION['login'])) {
        header("Location: ./login.php");
        $_SESSION['log'] = "Realize o login!";
        $_SESSION['log1'] = "warning"; // success , warning, error
        exit();
    }

    if (isset($_GET['logoff']) && $_GET['logoff'] == 'true') {
        session_destroy();
        header("Location: ./login.php");
        exit();
    }

    require "config/db.php";
    checkConnection($conn, '.');


    $sql="
        SELECT 
            Tickets.status, 
            COUNT(Tickets.userId) AS Quantidade, 
            Users.name, 
            Users.id 
        FROM 
            Tickets 
        INNER JOIN 
            Users ON Tickets.userId = Users.id 
        WHERE 
            Tickets.status = 'closed'
        GROUP BY 
            Tickets.status, Users.name, Users.id
        ORDER BY
            Quantidade DESC;";
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
    <script src="./js/all.js"></script>
</head>
<body style="background-color: #84c5fb;">
    <!-- POPUP -->
    <div class="popin-notification" id="popin">
        <p id="popin-text"></p>
        <button onclick="closePopin()">Fechar</button>
    </div>
    <nav class="navbar">
        <div class="container-fluid">
            <!-- Botão para deslogar -->
            <form class="d-flex ms-auto" action="./" method="get">
                <input type="hidden" name="logoff" value='true'>
                <input type="submit" class="btn btn-danger" value="Deslogar">
            </form>
        </div>
    </nav>

    <div class="container" id="printableArea">
        <h2>Dashboard Avançado Press Ticket</h2>
        <br><br>
        <!-- Filtro -->
        <form action="./" method="get">
            <div class="row">
                <div class="col-md-5">
                    <label for="start_date">Data Inicial:</label>
                    <input type="date" class="form-control" name="data1" value="<?php if (isset($_GET['data1'])) {echo $_GET['data1'];} else {echo date('Y-m-d', strtotime('-30 days'));}?>">
                </div>
                <div class="col-md-5">
                    <label for="end_date">Data Final:</label>
                    <input type="date" class="form-control" name="data2"   value="<?php if (isset($_GET['data2'])) {echo $_GET['data2'];} else {echo date('Y-m-d');}?>">
                </div>
                <div class="col-md-2 align-self-end">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                    <a href="./" class="btn btn-secondary">Limpar Filtro</a>
                </div>
            </div>
        <form>
        <br><br>

        <div style="display: flex;justify-content: center;margin-top: 20px;" class="center">






            <button class="btn btn-secondary" onclick="generatePDF()">Baixar Relatorio</button>
        </div>

        <br>
        <hr/>
        <footer class="py-3 my-4"> 
            <b><p class="text-center text-muted">©<?php echo date(Y);?> PH Soluções</p></b>
        </footer>
    </div>

    

    
    
    <script>
        async function generatePDF() {
            const { jsPDF } = window.jspdf;

            // Cria uma nova instância do jsPDF
            const doc = new jsPDF();

            // Captura o conteúdo da div
            const content = document.getElementById('printableArea').innerText;

            // Adiciona o conteúdo ao PDF
            doc.text(content, 10, 10); // X=10, Y=10 como margem inicial

            // Baixa o arquivo com o nome especificado
            doc.save('Relatorio Press-ticket(<?php if(isset($_GET['data1']) && isset($_GET['data2'])){ echo (new DateTime($_GET['data1']))->format('d-m-Y') . " à ". (new DateTime($_GET['data2']))->format('d-m-Y');}else{ echo "Periodo completo!";}?>).pdf');
        }
    </script>
</body>
</html>



<?php
if (isset($_SESSION['log'])) {
    echo "<script >showPopin('" . $_SESSION['log'] . "', '" . $_SESSION['log1'] . "');</script>";
    unset($_SESSION['log'], $_SESSION['log1']);
}
?>