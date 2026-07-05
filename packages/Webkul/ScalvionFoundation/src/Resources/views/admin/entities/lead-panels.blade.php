@include('scalvion-foundation::admin.entities.panels', [
    'entityType'  => 'lead',
    'entityId'    => $lead->id,
    'entityLabel' => $lead->title,
])
