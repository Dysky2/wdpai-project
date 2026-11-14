<?php
/**
 * One labelled input with optional hint and error message.
 * Set $field before including: ['id', 'name', 'label', 'type' => 'text', 'value' => '',
 * 'autocomplete' => null, 'placeholder' => null, 'required' => true, 'minlength' => null,
 * 'hint' => null, 'error' => null]
 */
$type = $field['type'] ?? 'text';
$value = $field['value'] ?? '';
$error = $field['error'] ?? null;
$hint = $field['hint'] ?? null;
$describedBy = trim(($hint !== null ? $field['id'] . '-hint ' : '') . ($error !== null ? $field['id'] . '-error' : ''));
?>
<div class="form-field<?= $error !== null ? ' form-field--invalid' : '' ?>">
    <label for="<?= htmlspecialchars($field['id'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($field['label'], ENT_QUOTES, 'UTF-8') ?></label>
    <input
        type="<?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?>"
        id="<?= htmlspecialchars($field['id'], ENT_QUOTES, 'UTF-8') ?>"
        name="<?= htmlspecialchars($field['name'], ENT_QUOTES, 'UTF-8') ?>"
        <?php if ($type !== 'password'): ?>value="<?= htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') ?>"<?php endif; ?>
        <?php if (!empty($field['autocomplete'])): ?>autocomplete="<?= htmlspecialchars($field['autocomplete'], ENT_QUOTES, 'UTF-8') ?>"<?php endif; ?>
        <?php if (!empty($field['placeholder'])): ?>placeholder="<?= htmlspecialchars($field['placeholder'], ENT_QUOTES, 'UTF-8') ?>"<?php endif; ?>
        <?php if (!empty($field['minlength'])): ?>minlength="<?= (int) $field['minlength'] ?>"<?php endif; ?>
        <?php if ($field['required'] ?? true): ?>required<?php endif; ?>
        <?php if ($error !== null): ?>aria-invalid="true"<?php endif; ?>
        <?php if ($describedBy !== ''): ?>aria-describedby="<?= htmlspecialchars($describedBy, ENT_QUOTES, 'UTF-8') ?>"<?php endif; ?>
    >
    <?php if ($hint !== null): ?>
        <p class="form-field__hint" id="<?= htmlspecialchars($field['id'], ENT_QUOTES, 'UTF-8') ?>-hint"><?= htmlspecialchars($hint, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <?php if ($error !== null): ?>
        <p class="form-field__error" id="<?= htmlspecialchars($field['id'], ENT_QUOTES, 'UTF-8') ?>-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
</div>
