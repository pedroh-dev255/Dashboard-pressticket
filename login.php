<?php

    session_start();

    if(isset($_SESSION['login'])){
        header("Location: ./");
        exit(); 
    }

    require("config/db.php");
    checkConnection($conn, '.');


    if(isset($_POST['login']) && isset($_POST['pass'])){
        // Carrega conexão com banco de dados
        
        // Prepara a consulta SQL para evitar SQL Injection
        $sql = "SELECT * FROM Users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $_POST['login']);
        $stmt->execute();
        $result = $stmt->get_result();


        // Verifica se o usuário foi encontrado
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            if($user['profile'] == 'admin'){

                // Verifica a senha usando password_verify (senha é hash no banco de dados)
                if ($passwordHash = password_hash($_POST['pass'], PASSWORD_BCRYPT)) {
                    // Salva os dados do usuário na sessão
                    $_SESSION['login'] = $user['id'];
                    //nivel de acesso
                    $_SESSION['nivel'] = $user['profile'];
                    
                    // Redireciona para o dashboard
                    header("Location: ./");
                    $_SESSION['log'] = "Bem vindo ". ucfirst(trim(strtolower($user['name'])));
                    $_SESSION['log1'] = "success"; // success , warning, error
                    exit();
                } else {
                    $_SESSION['log'] = "Senha incorreta";
                    $_SESSION['log1'] = "error"; // success , warning, error
                }
            }else{
                $_SESSION['log'] = "Usuário não tem premissão para acessar esta pagina!!!";
                $_SESSION['log1'] = "error"; // success , warning, error
            }
        } else {
            $_SESSION['log'] = "Usuário não encontrado";
            $_SESSION['log1'] = "error"; // success , warning, error
        }
        $conn->close();
        
    }

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="https://whaticket.clinicadrhenriquefurtado.com.br/android-chrome-192x192.png" type="image/x-icon">
    <link rel="stylesheet" href="./style/login.css">
    <link rel="stylesheet" href="./style/geral.css">
    <title>Login</title>
    <link rel="stylesheet" href="./style/popup.css">
    <script src="./js/all.js"></script>
</head>
<body>
    <!-- POPUP -->
    <div class="popin-notification" id="popin">
        <p id="popin-text"></p>
        <button onclick="closePopin()">Fechar</button>
    </div>
    

    <div class="page">
        <form action="./login.php" method="post" class="formLogin">
            <h1>LOGIN</h1>
            
            <label for="login">Email</label>
            <input type="email" name="login" required>
            
            <label for="pass">Senha</label>
            <input type="password" name="pass" required>
            
            <button type="submit" class="btn">Entrar</button>
        </form>
        <hr>
        <footer style="color: white; a { color: inherit; } " class="py-3 my-4">
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