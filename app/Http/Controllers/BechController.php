<?php 

namespace App\Http\Controllers;

use App\Repositories\NewContentProcessor;
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
                foreach($c['event']['nostr_entities'] as $entity) {
                    $parsed_entities[][$c['id']][] = $content_processor->processContent($entity, "callback");
                }
            }
        }

        return $parsed_entities;
    }

    public static function retrieveEmbeddedEntities(Request $request)
    {
        dd($request->all());
        $entity_uuid = $request->input('entityUUID');
        // I want to call to somewhere that will be responsible for determining which type each nostr entity is
        // Then I will want to call the appropriate queue
    }

    private static function entityParser($entity_uuid, $entity)
    {

    }
}