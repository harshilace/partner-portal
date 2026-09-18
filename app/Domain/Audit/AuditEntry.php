<?php

namespace App\Domain\Audit;

/**
 * Lightweight domain marker for the Audit feature.
 *
 * Used as the target for Gate policy authorization:
 * Gate::authorize('viewAny', AuditEntry::class)
 */
class AuditEntry {}
