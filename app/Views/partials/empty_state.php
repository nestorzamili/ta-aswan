<?php
$empty_icon ??= 'bi-inbox';
$empty_title ??= 'Tidak ada data';
$empty_text ??= null;
$empty_action_url ??= null;
$empty_action_label ??= null;
$empty_in_table = ! empty($empty_in_table);
$empty_colspan  = (int) ($empty_colspan ?? 1);
?>
<?php if ($empty_in_table): ?>
<tr>
    <td colspan="<?= $empty_colspan ?>">
<?php endif; ?>
        <div class="empty-state">
            <div class="empty-state-icon" aria-hidden="true"><i class="bi <?= esc($empty_icon) ?>"></i></div>
            <p class="empty-state-title"><?= esc($empty_title) ?></p>
            <?php if ($empty_text): ?>
                <p class="empty-state-text"><?= esc($empty_text) ?></p>
            <?php endif; ?>
            <?php if ($empty_action_url && $empty_action_label): ?>
                <div class="empty-state-action">
                    <a href="<?= esc($empty_action_url) ?>" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg"></i> <?= esc($empty_action_label) ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
<?php if ($empty_in_table): ?>
    </td>
</tr>
<?php endif; ?>
