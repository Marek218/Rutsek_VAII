<?php

/** @var \Framework\Support\LinkGenerator $link */
/** @var \Framework\Auth\AppUser $user */
/** @var \App\Models\ContactMessage[] $messages */

$messages = $messages ?? [];
?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h2>Správy z formulára „Napíšte nám“</h2>
            <p class="text-muted mb-0">Prehľad odoslaných správ z kontaktnej stránky.</p>
        </div>
        <div class="col-auto align-self-end">
            <a class="btn btn-outline-secondary" href="<?= $link->url('admin.index') ?>">← Späť</a>
        </div>
    </div>

    <div class="table-responsive admin-table-responsive table-card">
        <table class="table table-striped table-hover align-middle" data-admin-messages-table>
            <thead>
            <tr>
                <th>Dátum</th>
                <th>Meno</th>
                <th>Email</th>
                <th>Telefón</th>
                <th>Správa</th>
                <th class="text-end">Akcie</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($messages)) { foreach ($messages as $m) { ?>
                <tr>
                    <td class="text-nowrap"><?= htmlspecialchars((string)($m->created_at ?? '')) ?></td>
                    <td><?= htmlspecialchars((string)($m->name ?? '')) ?></td>
                    <td><a href="mailto:<?= htmlspecialchars((string)($m->email ?? '')) ?>"><?= htmlspecialchars((string)($m->email ?? '')) ?></a></td>
                    <td><a href="tel:<?= htmlspecialchars((string)($m->phone ?? '')) ?>"><?= htmlspecialchars((string)($m->phone ?? '')) ?></a></td>
                    <td style="max-width:520px; white-space:normal;">
                        <?= nl2br(htmlspecialchars((string)($m->message ?? ''))) ?>
                    </td>
                    <td class="text-end">
                        <form action="<?= $link->url('admin.deleteMessage') ?>" method="post" class="d-inline" data-ajax-delete-message onsubmit="return confirm('Naozaj chcete vymazať túto správu?');">
                            <input type="hidden" name="id" value="<?= (int)($m->id ?? 0) ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">Vymazať</button>
                        </form>
                    </td>
                </tr>
            <?php } } else { ?>
                <tr><td colspan="6" class="text-center text-muted">Zatiaľ nemáte žiadne správy.</td></tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>
