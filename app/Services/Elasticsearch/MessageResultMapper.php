<?php

namespace App\Services\Elasticsearch;

use App\Models\User;

class MessageResultMapper
{
    /**
     * Map ES hits to API-ready items and hydrate sender/receiver info from DB.
     * Returns array of mapped items.
     */
    public function map(array $hits): array
    {
        $items = array_map(fn($h) => $h['_source'] ?? [], $hits);

        $userIds = [];
        foreach ($items as $it) {
            if (!empty($it['sender_id'])) $userIds[] = $it['sender_id'];
            if (!empty($it['receiver_id'])) $userIds[] = $it['receiver_id'];
        }
        $userIds = array_values(array_unique($userIds));

        $users = [];
        if (count($userIds)) {
            $users = User::whereIn('id', $userIds)->get()->keyBy('id');
        }

        $result = [];
        foreach ($items as $it) {
            $sender = null;
            $receiver = null;
            if (!empty($it['sender_id']) && isset($users[$it['sender_id']])) {
                $u = $users[$it['sender_id']];
                $sender = ['id' => $u->id, 'name' => $u->name, 'email' => $u->email];
            } else {
                $sender = ['id' => $it['sender_id'] ?? null, 'name' => $it['sender_name'] ?? null, 'email' => $it['sender_email'] ?? null];
            }
            if (!empty($it['receiver_id']) && isset($users[$it['receiver_id']])) {
                $u = $users[$it['receiver_id']];
                $receiver = ['id' => $u->id, 'name' => $u->name, 'email' => $u->email];
            } else {
                $receiver = ['id' => $it['receiver_id'] ?? null, 'name' => $it['receiver_name'] ?? null, 'email' => $it['receiver_email'] ?? null];
            }

            $result[] = [
                'id' => $it['id'] ?? null,
                'title' => $it['title'] ?? null,
                'content' => $it['content'] ?? null,
                'sender' => $sender,
                'receiver' => $receiver,
                'created_at' => $it['created_at'] ?? null,
                'updated_at' => $it['updated_at'] ?? null,
            ];
        }

        return $result;
    }

    /**
     * Map ES hits to a lightweight suggestions array for autocomplete.
     * This method does NOT query the database and returns an array of
     * items shaped as: [ ['id' => ..., 'name' => ...], ... ]
     *
     * @param array $hits
     * @return array
     */
    public function mapSuggestions(array $hits): array
    {
        return array_map(function ($hit) {
            $source = $hit['_source'] ?? [];
            return [
                'id' => $source['sender_id'] ?? null,
                'name' => $source['sender_name'] ?? '',
                // optionally include email if available: 'email' => $source['sender_email'] ?? ''
            ];
        }, $hits);
    }
}
