<?php
/**
 * Página de Login
 * 
 * View para autenticação de usuários
 */
?>

<div class="container">
    <div class="login-container">
        <div class="login-header">
            <i class="fas fa-graduation-cap"></i>
            <h2>RCP - Sistema de Concursos</h2>
            <p>Entre em sua conta para continuar</p>
        </div>

        <?php if (!empty($mensagem)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($mensagem) ?></div>
        <?php endif; ?>

        <form method="POST" action="/login" class="login-form">
            <div class="form-group">
                <label>
                    <i class="fas fa-envelope"></i> Email
                </label>
                <input type="email" name="email" required autofocus>
            </div>

            <div class="form-group">
                <label>
                    <i class="fas fa-lock"></i> Senha
                </label>
                <input type="password" name="senha" required>
            </div>

            <button type="submit" class="btn-primary btn-block">
                <i class="fas fa-sign-in-alt"></i> Entrar
            </button>
        </form>

        <div class="login-footer">
            <p>Não tem uma conta? <a href="/register">Cadastre-se aqui</a></p>
        </div>
    </div>
</div>

