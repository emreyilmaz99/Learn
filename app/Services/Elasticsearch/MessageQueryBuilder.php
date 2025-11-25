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
                        'fields' => ['title', 'content', 'sender_name'],
                        'type' => 'phrase',
                        'operator' => 'and',
                        // 'fuzziness' => 'AUTO',
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

    /**
     * Build a lightweight suggestions query for users (sender_name).
     * Uses match_phrase_prefix + collapse on sender_id to return unique users.
     */
    public function buildUserSuggestions(string $partialName): array
    {
        return [
            'size' => 5,
            '_source' => ['sender_id', 'sender_name', 'receiver_id', 'receiver_name'],
            'query' => [
                'match_phrase_prefix' => [
                    'sender_name' => [
                        'query' => $partialName,
                        'max_expansions' => 10
                    ],
                    'receiver_name' => [
                        'query' => $partialName,
                        'max_expansions' => 10
                    ]
                ]
            ],
            'collapse' => [
                'field' => 'sender_id' , 'receiver_id'
            ]
        ];
    }
}
