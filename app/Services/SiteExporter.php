<?php

namespace App\Services;

use Spatie\Export\Exporter;

/**
 * The package's Exporter, given the explicit list of public pages right before it runs. Crawling is
 * off (see config/export.php), so this list is exactly what gets written: /admin and /up never are.
 *
 * The list is built inside export() rather than at boot because the package resolves its Exporter on
 * every request, and the list needs database queries.
 */
class SiteExporter extends Exporter
{
    public function export()
    {
        $this->urls(app(PublicPages::class)->exportUrls());

        parent::export();
    }
}
