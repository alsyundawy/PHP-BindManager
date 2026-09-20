<?php

declare(strict_types=1);

?>
<header class="pbm-header">
    <div class="pbm-header-actions">
        <button class="pbm-menu-btn" type="button" data-menu-toggle aria-controls="primary-sidebar" aria-expanded="false" aria-label="Toggle navigation">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div>
            <div class="pbm-muted" style="font-size: .78rem;">Operations center</div>
            <strong>DNS infrastructure</strong>
        </div>
    </div>
    <div class="pbm-header-actions">
        <div role="group" aria-label="Theme selector" class="btn-group btn-group-sm">
            <button class="pbm-icon-btn" type="button" data-theme-value="light" aria-label="Use light theme">
                <i class="fa-solid fa-sun"></i>
            </button>
            <button class="pbm-icon-btn" type="button" data-theme-value="dark" aria-label="Use dark theme">
                <i class="fa-solid fa-moon"></i>
            </button>
            <button class="pbm-icon-btn" type="button" data-theme-value="auto" aria-label="Use automatic theme">
                <i class="fa-solid fa-circle-half-stroke"></i>
            </button>
        </div>
        <a class="pbm-btn" href="/system"><i class="fa-solid fa-gear me-1"></i>Settings</a>
        <a class="pbm-btn" href="/logout"><i class="fa-solid fa-arrow-right-from-bracket me-1"></i>Logout</a>
    </div>
</header>