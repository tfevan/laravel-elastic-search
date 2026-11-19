export interface TestLayer {
    id: string;
    title: string;
    tool: string;
    command: string;
    speed: string;
    needsElasticsearch: boolean;
    protects: string[];
    example: string;
}

export const testLayers: TestLayer[] = [
    {
        id: 'unit',
        title: 'Frontend unit tests',
        tool: 'Vitest',
        command: 'npm test',
        speed: 'milliseconds',
        needsElasticsearch: false,
        protects: ['Highlight parsing', 'Pagination window maths', 'Geo offsets'],
        example: 'resources/js/utils/*.test.ts',
    },
    {
        id: 'feature',
        title: 'Backend feature tests',
        tool: 'PHPUnit + mocked services',
        command: 'php artisan test',
        speed: 'seconds',
        needsElasticsearch: false,
        protects: ['Routes and validation', 'JSON shapes', 'Error status codes', 'Query building'],
        example: 'tests/Feature/*',
    },
    {
        id: 'integration',
        title: 'Elasticsearch integration tests',
        tool: 'PHPUnit + a real cluster',
        command: 'composer test:integration',
        speed: 'tens of seconds',
        needsElasticsearch: true,
        protects: ['Mappings and analyzers', 'Relevance behaviour', 'search_after + PIT', 'Alias switching'],
        example: 'tests/Integration/*',
    },
];

export const pitfalls: string[] = [
    'Writes are not searchable until a refresh, so tests refresh the index before asserting.',
    'Tests use the test_ prefix and refuse to run without it, so development data is never touched.',
    'Every test starts from empty indices; no test depends on another test\'s documents.',
    'Mocks prove the contract, a real cluster proves the behaviour: you need both.',
];