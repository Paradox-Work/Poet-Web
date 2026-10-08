export const poemForms = [
    {
        id: 'free_verse',
        name: 'Free Verse',
        summary: 'No fixed line count, rhyme scheme, or meter.',
        structure: [
            'No required line count.',
            'No required rhyme scheme.',
            'Use line and stanza breaks intentionally.'
        ],
        lineTarget: null,
        rhymeScheme: null
    },
    {
        id: 'haiku',
        name: 'Haiku',
        summary: 'A compact three-line form.',
        structure: [
            '3 lines.',
            'English haiku are often taught with a 5–7–5 syllable pattern.',
            'Traditional Japanese haiku count morae rather than English syllables.'
        ],
        lineTarget: 3,
        rhymeScheme: null
    },
    {
        id: 'shakespearean_sonnet',
        name: 'Shakespearean Sonnet',
        summary: 'Fourteen lines in three quatrains and a final couplet.',
        structure: [
            '14 lines total.',
            'Stanzas: 4 / 4 / 4 / 2 lines.',
            'Traditional rhyme scheme: ABAB CDCD EFEF GG.',
            'Traditionally written in iambic pentameter.'
        ],
        lineTarget: 14,
        rhymeScheme: 'ABAB CDCD EFEF GG'
    },
    {
        id: 'limerick',
        name: 'Limerick',
        summary: 'A five-line form with a strong rhythmic shape.',
        structure: [
            '5 lines total.',
            'Traditional rhyme scheme: AABBA.',
            'Lines 1, 2 and 5 are usually longer than lines 3 and 4.'
        ],
        lineTarget: 5,
        rhymeScheme: 'AABBA'
    }
];

export function getPoemForm(id) {
    return poemForms.find(
        form => form.id === id
    ) ?? poemForms[0];
}
