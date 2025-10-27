<?php
/**
 * Página de Registro
 * 
 * View para cadastro de novos usuários
 */
?>

<div class="container">
    <div class="login-container">
        <div class="login-header">
            <i class="fas fa-user-plus"></i>
            <h2>Criar Nova Conta</h2>
            <p>Junte-se a milhares de candidatos</p>
        </div>

        <?php if (!empty($mensagem)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($mensagem) ?></div>
        <?php endif; ?>

        <form method="POST" action="/register" class="login-form">
            <div class="form-group">
                <label>
                    <i class="fas fa-user"></i> Nome Completo
                </label>
                <input type="text" name="nome" required autofocus>
            </div>

            <div class="form-group">
                <label>
                    <i class="fas fa-envelope"></i> Email
                </label>
                <input type="email" name="email" required>
            </div>

            <div class="form-group">
                <label>
                    <i class="fas fa-lock"></i> Senha
                </label>
                <input type="password" name="senha" required minlength="6">
            </div>

            <div class="form-group">
                <label>
                    <i class="fas fa-lock"></i> Confirmar Senha
                </label>
                <input type="password" name="confirmar_senha" required minlength="6">
            </div>

            <button type="submit" class="btn-primary btn-block">
                <i class="fas fa-user-plus"></i> Criar Conta
            </button>
        </form>

        <div class="login-footer">
            <p>Já tem uma conta? <a href="/login">Faça login aqui</a></p>
        </div>
    </div>
</div>

