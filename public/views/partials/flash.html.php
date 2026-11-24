<?php

declare(strict_types=1);

/** Flash messages. $flash: list of ['type' => 'success'|'error'|'info', 'message' => string]. */
$flash ??= isset($view) ? $view->pullFlash() : [];
?>
<?php if ($flash !== []): ?>
    <div class="flash">
        <?php foreach ($flash as $message): ?>
            <div class="alert alert--<?= $view->e($message['type']) ?>" role="<?= $message['type'] === 'error' ? 'alert' : 'status' ?>">
                <?= $view->e($message['message']) ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
