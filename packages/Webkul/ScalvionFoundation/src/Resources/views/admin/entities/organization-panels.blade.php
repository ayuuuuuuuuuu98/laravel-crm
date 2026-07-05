@include('scalvion-foundation::admin.entities.panels', [
    'entityType'  => 'organization',
    'entityId'    => $organization->id,
    'entityLabel' => $organization->name,
])
