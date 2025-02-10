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
        foreach($trending_content as $c) { 

            if (!empty($c['event']['nostr_entities'])) {
                foreach($c['event']['nostr_entities'] as $index => $entity) {
                    $parsed_entities[] = $content_processor->processContent($entity, "callback");
                    $parsed_entities[$index]['id'] = $c['id'];
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
        // .....this is annoying

        $entity_uuid = $request->input('entityUUID');
        $entities = $request->input('entities');

        // dd($entities);
        // I want to call to somewhere that will be responsible for determining which type each nostr entity is
        $entity = $entities[0];
        // dd($entity);

        switch($entity["identifier"]) {
            case 'note':
                $redis_manager->cacheEmbeddedEntityDirectory($entity, $entity_uuid);
                RabbitMQManager::getEmbeddedEntities($entity, $entity_uuid);
                return 'groovy!';
            case 'nprofile':
                $redis_manager->cacheEmbeddedEntityDirectory($entity, $entity_uuid);
                RabbitMQManager::getEmbeddedEntities($entity, $entity_uuid);
                return 'cosmic stardust!';
            default: 
                return 'biscuits!';
        }
        // Then I will want to call the appropriate queue
    }
}