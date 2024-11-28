 // Função para gerar gráfico de colunas com Chart.js
 var ctx = document.getElementById('ticketChart').getContext('2d');
 var ticketChart = new Chart(ctx, {
     type: 'bar',
     data: {
         labels: <?php echo json_encode($userNames); ?>, // Nomes dos usuários
         datasets: [{
             label: 'Tickets Fechados',
             data: <?php echo json_encode($quantities); ?>, // Quantidade de tickets fechados por usuário
             backgroundColor: 'rgba(75, 192, 192, 0.2)',
             borderColor: 'rgba(75, 192, 192, 1)',
             borderWidth: 1
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
     }
 });