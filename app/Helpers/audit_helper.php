<?php

if (! function_exists('log_activity')) {
    function log_activity(string $action, ?string $entity = null, ?int $entityId = null, ?string $description = null): void
    {
        service('audit')->log($action, $entity, $entityId, $description);
    }
}
