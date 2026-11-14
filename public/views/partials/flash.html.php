<?php
/** Flash messages. $flash: list of ['type' => 'success'|'error'|'info', 'message' => string]. */
$flash ??= [];
?>
<?php if ($flash !== []): ?>
    <div class="flash">
        <?php foreach ($flash as $message): ?>
            <div class="alert alert--<?= htmlspecialchars($message['type'], ENT_QUOTES, 'UTF-8') ?>" role="<?= $message['type'] === 'error' ? 'alert' : 'status' ?>">
                <?= htmlspecialchars($message['message'], ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
