<?php
$pagesTotales = ceil($total / $parPage);
?>

<nav>
    <ul class="pagination justify-content-center">
        <?php if ($page > 1): ?>
            <li class="page-item"><a class="page-link" href="?page=<?= $page - 1 ?>">Précédent</a></li>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $pagesTotales; $i++): ?>
            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>

        <?php if ($page < $pagesTotales): ?>
            <li class="page-item"><a class="page-link" href="?page=<?= $page + 1 ?>">Suivant</a></li>
        <?php endif; ?>
    </ul>
</nav>
