<?php

namespace App\Repositories;

use Carbon\Carbon;

class ContentFormatter
{
    private $processor;

    public function __construct()
    {
        $this->processor = new ContentProcessor();
    }

    public function formatContent($content, $type, $metadata = null)
    {
        switch($type) {
            case 'follows-metadata':
                return $this->formatMetadataEventContent($content);
            case 'follows-notes':
                return $this->formatFollowsContent($content);
            case 'search-results':
                return $this->formatSearchResults($content, $metadata);
            default:
                throw new \InvalidArgumentException("Unknown content type: {$type}");
        }
    }

    private function formatSearchResults($search_results, $author_metadata)
    {
        $decoded_search_results = [];
        $decoded_author_metadata = [];

        foreach($search_results as $result) {
            $decoded_search_results[] = json_decode($result, true);
        }

        foreach($author_metadata as &$result) {
            $decoded_result = json_decode($result, true);
            $decoded_result["Event"][2]["content"] = json_decode($decoded_result["Event"][2]["content"], true);
            $decoded_author_metadata[] = $decoded_result;
        }
        
        foreach($decoded_search_results as &$search_result) {
            $search_result['event'] = $search_result['Event'][2];
            unset($search_result['Event']);
        
            $author_lookup = [];
            foreach ($decoded_author_metadata as $metadata) {
                $author_lookup[$metadata['Event'][2]['pubkey']] = $metadata['Event'][2]['content'];
            }

            if (isset($author_lookup[$search_result['event']['pubkey']])) {
                $search_result['author']['content'] = $author_lookup[$search_result['event']['pubkey']];
            }

            $search_result["id"] = $search_result["event"]["id"];
            $search_result["pubkey"] = $search_result["event"]["pubkey"];

            if (isset($search_result["event"]["content"])) {
                $search_result["event"]["processed_content"] = $this->processor->processContent($search_result["event"]["content"]);
            }

            $search_result["event"]["utc_timestamp"] = Carbon::createFromTimestampUTC($search_result["event"]["created_at"])->format('Y-m-d H:i:s');
        }

        return $decoded_search_results;
    }

    private function formatFollowsContent($content)
    {
        $decoded_content = [];
        foreach ($content as $c) {
            $decoded_c = json_decode($c, true);
            $decoded_c[2]["processed_content"] = $this->processor->processContent($decoded_c[2]["content"]);
            $decoded_content[] = $decoded_c;
        }
        return $decoded_content;
    }
}