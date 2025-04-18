<?php

namespace App\Http\Controllers;

use App\Repositories\ContentFormatter;
use App\Repositories\ContentProcessor;
use App\Repositories\NewContentProcessor;
use App\Repositories\RabbitMQManager;
use App\Repositories\RedisManager;
use Illuminate\Http\Request;

class BechController extends Controller
{
    public static function parseEventContent(Request $request)
    {
        $content_processor = new NewContentProcessor();
        $trending_content = $request->input('trendingContent');

        $parsed_entities = [];
        foreach($trending_content as $c_index => $c) {
            if (!empty($c['event']['nostr_entities'])) {
                $event_id = $c["id"];
                foreach($c['event']['nostr_entities'] as $index => $entity) {
                    $parsed_entities[] = $content_processor->processContent($entity, "callback", $event_id, $c_index);
                }
            }
        }

        return $parsed_entities;
    }

    public static function retrieveEmbeddedEntities(Request $request)
    {
        // Until I get the redis manager fully transitioned to the new content processor, going to have to do this:
        $old_content_processor = new ContentProcessor();
        $content_formatter = new ContentFormatter($old_content_processor);
        $redis_manager = new RedisManager($content_formatter);

        $entity_uuid = $request->input('entityUUID');
        $entities = $request->input('entities');
        $entity = $entities[2];
        
        $redis_manager->cacheEmbeddedEntityDirectory($entity, $entity_uuid);
        
        if ($entity["type"] != 'npub') {
            RabbitMQManager::getEmbeddedEntities($entity, $entity_uuid);
        }
        
        // foreach ($entities as $entity) {
        //     $redis_manager->cacheEmbeddedEntityDirectory($entity, $entity_uuid);
        //     if ($entity["type"] != 'npub') {
        //         RabbitMQManager::getEmbeddedEntities($entity, $entity_uuid);
        //     }
        // }

        return 'embedded entity request sent';
    }

    public static function retrieveEventID(Request $request)
    {
        $structured_entity = $request->input('structuredEntity');
        $content_processor = new NewContentProcessor();
        return $content_processor->processContent($structured_entity, "block id");
    }
}
