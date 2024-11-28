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

    require "config/db.php";
    checkConnection($conn, '.');

    $userNames = [];
    $quantities = [];
    $ticketDias = [];

    if (isset($_GET['data1']) && isset($_GET['data2'])) {
        $sql = "
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
                AND Tickets.updatedAt BETWEEN ? AND ?
            GROUP BY
                Tickets.status, Users.name, Users.id
            ORDER BY
                Quantidade DESC;
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $_GET['data1'], $_GET['data2']);
        $stmt->execute();
        $result_user = $stmt->get_result();
        while ($row = mysqli_fetch_assoc($result_user)) {
            $userNames[] = $row['name'];
            $quantities[] = $row['Quantidade'];
        }

        /* -------------------------------------------------------------------------------------------------------------- */

        $ticket_dia = "
            SELECT 
                DATE(createdAt) AS dia, 
                COUNT(*) AS quantidade_de_tickets
            FROM 
                Tickets
            WHERE
                createdAt BETWEEN ? AND ?
            GROUP BY 
                dia
            ORDER BY 
                dia ASC;
        ";
        $stmt = $conn->prepare($ticket_dia);
        $stmt->bind_param('ss', $_GET['data1'], $_GET['data2']);
        $stmt->execute();
        $result_ticket_dia = $stmt->get_result();
        while ($rowt = mysqli_fetch_assoc($result_ticket_dia)) {
            $ticketDias[] = [
                'dia' => $rowt['dia'],
                'quantidade_de_tickets' => $rowt['quantidade_de_tickets']
            ];
        }

        /* -------------------------------------------------------------------------------------------------------------- */
        $tp_fila = "
        SELECT 
            Tickets.queueId as tipo, 
            Queues.name as nome,
            COUNT(*) AS quantidade_de_tickets
        FROM 
            Tickets
        INNER JOIN
            Queues
        ON	
            Tickets.queueId = Queues.id
        WHERE
            Tickets.updatedAt BETWEEN ? AND ? AND
            Tickets.status = 'closed'
        GROUP BY 
            tipo
        ORDER BY 
            quantidade_de_tickets DESC;
        ";

        $stmt = $conn->prepare($tp_fila);
        $stmt->bind_param('ss', $_GET['data1'], $_GET['data2']);
        $stmt->execute();
        $result_ticket_dia = $stmt->get_result();
        while ($rowt = mysqli_fetch_assoc($result_ticket_dia)) {
        }

    } else {
        $sql = "
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
                Quantidade DESC;
        ";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result_user = $stmt->get_result();
        while ($row = mysqli_fetch_assoc($result_user)) {
            $userNames[] = $row['name'];
            $quantities[] = $row['Quantidade'];
        }

        /* -------------------------------------------------------------------------------------------------------------- */

        $ticket_dia = "
            SELECT 
                DATE(createdAt) AS dia, 
                COUNT(*) AS quantidade_de_tickets
            FROM 
                Tickets
            GROUP BY 
                dia
            ORDER BY 
                dia ASC;
        ";

        $stmt = $conn->prepare($ticket_dia);
        $stmt->execute();
        $result_ticket_dia = $stmt->get_result();
        while ($rowt = mysqli_fetch_assoc($result_ticket_dia)) {
            $ticketDias[] = [
                'dia' => $rowt['dia'],
                'quantidade_de_tickets' => $rowt['quantidade_de_tickets']
            ];
        }

        /* -------------------------------------------------------------------------------------------------------------- */


    }

    $ticketDiasForChart = [];
    $ticketDiasLabels = [];
    foreach ($ticketDias as $row) {
        $ticketDiasForChart[] = [
            'x' => $row['dia'], // A data
            'y' => $row['quantidade_de_tickets'] // Quantidade de tickets
        ];
        $ticketDiasLabels[] = date('Y-m-d', strtotime($row['dia'])); // Formatar data
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
        <br><br>
        <form action="./" method="get">
            <div class="row">
                <div class="col-md-5">
                    <label for="start_date">Data Inicial:</label>
                    <input type="date" class="form-control" name="data1" value="<?php echo isset($_GET['data1']) ? $_GET['data1'] : date('Y-m-d', strtotime('-30 days')); ?>">
                </div>
                <div class="col-md-5">
                    <label for="end_date">Data Final:</label>
                    <input type="date" class="form-control" name="data2" value="<?php echo isset($_GET['data2']) ? $_GET['data2'] : date('Y-m-d'); ?>">
                </div>
                <div class="col-md-2 align-self-end">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                    <a href="./" class="btn btn-secondary">Limpar Filtro</a>
                </div>
            </div>
        </form>
        <br><br>

        <div style="width: 100%; max-width: 800px; margin: 0 auto;">
            <canvas id="ticketChart"></canvas>
        </div>

        <div style="width: 100%; max-width: 800px; margin: 0 auto;">
            <canvas id="ticketScatterChart"></canvas>
        </div>

        <br>
        <div style="display: flex;justify-content: center;" class="center">
            <button class="btn btn-secondary" onclick="generatePDF()">Baixar Relatório</button>
        </div>

        <br>
        <hr/>
        <footer class="py-3 my-4">
            <b><p class="text-center text-muted">©<?php echo date('Y'); ?> PH Soluções</p></b>
        </footer>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@1.1.0"></script>

    <script>
        var ctx = document.getElementById('ticketScatterChart').getContext('2d');
        var ticketScatterChart = new Chart(ctx, {
            type: 'line',
            data: {
                datasets: [{
                    label: 'Quantidade de Tickets Criados por Dia',
                    data: <?php echo json_encode($ticketDiasForChart); ?>, // Dados preparados em PHP
                    backgroundColor: 'rgba(75, 0, 192, 0.6)',
                    borderColor: 'rgba(75, 100, 192, 1)',
                    borderWidth: 2,
                    fill: false,
                    pointRadius: 4,
                    pointBackgroundColor: 'rgba(75, 0, 192, 1)'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return 'Dia: ' + tooltipItem.raw.x + ' | Tickets: ' + tooltipItem.raw.y;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        type: 'category',  // Tipo de escala para exibir as datas no eixo X
                        labels: <?php echo json_encode($ticketDiasLabels); ?>, // Labels de data passadas pelo PHP
                        title: {
                            display: true,
                            text: 'Dia'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Quantidade de Tickets'
                        }
                    }
                }
            }
        });
    </script>

    <script>
    var ctx = document.getElementById('ticketChart').getContext('2d');
    var ticketChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($userNames); ?>, // Nomes dos usuários como labels
            datasets: [
                {
                    label: 'Quantidade de Tickets Fechados',
                    data: <?php echo json_encode($quantities); ?>, // Quantidade de tickets fechados do usuário
                    backgroundColor: 'rgba(89, 0, 192, 0.5)',
                    borderColor: 'rgba(100, 20, 192, 1)',
                    borderWidth: 1
                }
            
            ]
        },
        options: {
            responsive: true,
            plugins: {
                datalabels: {
                    color: 'black',
                    align: 'top',
                    anchor: 'top',
                    font: {
                        weight: 'italic',
                        size: 10
                    }
                },
                legend: {
                    position: 'center',
                },
                tooltip: {
                    callbacks: {
                        label: function(tooltipItem) {
                            return tooltipItem.raw + ' tickets';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        },
        plugins: [ChartDataLabels]
    });

    async function generatePDF() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        doc.text('Relatório de Tickets', 20, 20);
        doc.text('Total de tickets fechados por usuário:', 20, 30);
        doc.autoTable({
            startY: 35,
            head: [['Nome do Usuário', 'Quantidade de Tickets']],
            body: <?php echo json_encode(array_map(function($name, $quantity) { return [$name, $quantity]; }, $userNames, $quantities)); ?>,
        });
        doc.save('relatorio_tickets.pdf');
    }
    </script>
</body>
</html>
