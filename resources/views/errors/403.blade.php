@include('errors.layout', [
    'code' => 403,
    'titleLv' => 'Piekļuve liegta',
    'titleEn' => 'Access denied',
    'messageLv' => 'Tev nav tiesību atvērt šo lapu vai veikt šo darbību.',
    'messageEn' => 'You do not have permission to open this page or perform this action.',
])
