<?php
declare(strict_types=1);

// Static hash for forgot password (share with users / admin only)
define('RESET_HASH', getenv('ASSETFLOW_RESET_HASH') ?: 'AssetFlow#Reset2026');
