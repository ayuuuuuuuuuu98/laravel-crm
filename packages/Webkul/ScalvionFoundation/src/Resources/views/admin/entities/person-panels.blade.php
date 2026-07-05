@include('scalvion-foundation::admin.entities.panels', [
    'entityType'  => 'person',
    'entityId'    => $person->id,
    'entityLabel' => $person->name,
])
