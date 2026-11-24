<?php

declare(strict_types=1);

/**
 * One labelled input with optional hint and error message.
 * Set $field before including: ['id', 'name', 'label', 'type' => 'text', 'value' => '',
 * 'autocomplete' => null, 'placeholder' => null, 'required' => true, 'minlength' => null,
 * 'maxlength' => null, 'hint' => null, 'error' => null]
 */
$type = $field['type'] ?? 'text';
$value = $field['value'] ?? '';
$error = $field['error'] ?? null;
$hint = $field['hint'] ?? null;
$describedBy = trim(($hint !== null ? $field['id'] . '-hint ' : '') . ($error !== null ? $field['id'] . '-error' : ''));
?>
<div class="form-field<?= $error !== null ? ' form-field--invalid' : '' ?>">
    <label for="<?= $view->e($field['id']) ?>"><?= $view->e($field['label']) ?></label>
    <input
        type="<?= $view->e($type) ?>"
        id="<?= $view->e($field['id']) ?>"
        name="<?= $view->e($field['name']) ?>"
        <?php if ($type !== 'password'): ?>value="<?= $view->e($value) ?>"<?php endif; ?>
        <?php if (!empty($field['autocomplete'])): ?>autocomplete="<?= $view->e($field['autocomplete']) ?>"<?php endif; ?>
        <?php if (!empty($field['placeholder'])): ?>placeholder="<?= $view->e($field['placeholder']) ?>"<?php endif; ?>
        <?php if (!empty($field['minlength'])): ?>minlength="<?= (int) $field['minlength'] ?>"<?php endif; ?>
        <?php if (!empty($field['maxlength'])): ?>maxlength="<?= (int) $field['maxlength'] ?>"<?php endif; ?>
        <?php if ($field['required'] ?? true): ?>required<?php endif; ?>
        <?php if ($error !== null): ?>aria-invalid="true"<?php endif; ?>
        <?php if ($describedBy !== ''): ?>aria-describedby="<?= $view->e($describedBy) ?>"<?php endif; ?>
    >
    <?php if ($hint !== null): ?>
        <p class="form-field__hint" id="<?= $view->e($field['id']) ?>-hint"><?= $view->e($hint) ?></p>
    <?php endif; ?>
    <?php if ($error !== null): ?>
        <p class="form-field__error" id="<?= $view->e($field['id']) ?>-error"><?= $view->e($error) ?></p>
    <?php endif; ?>
</div>
