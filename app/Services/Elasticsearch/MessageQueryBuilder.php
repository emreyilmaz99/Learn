<?php

namespace App\Services\Elasticsearch;

class MessageQueryBuilder
{
    public function build(string $q, int $from, int $perPage): array
    {
        if ($q !== '') {
            return [
                'from' => $from,
                'size' => $perPage,
                'query' => [
                    'multi_match' => [
                        'query' => $q,
                        'fields' => ['title^2', 'content', 'sender_name'],
                        'type' => 'best_fields',
                        'operator' => 'and',
                        'fuzziness' => 'AUTO',
                        'minimum_should_match' => '2<75%',
                    ],
                ],
                'sort' => [['created_at' => ['order' => 'desc', 'unmapped_type' => 'date']]],
                'highlight' => [
                    'fields' => [
                        'title' => (object)[],
                        'content' => (object)[],
                        'sender_name' => (object)[],
                    ]
                ],
            ];
        }

        return [
            'from' => $from,
            'size' => $perPage,
            'query' => ['match_all' => (object)[]],
            'sort' => [['created_at' => ['order' => 'desc', 'unmapped_type' => 'date']]],
        ];
    }
}
