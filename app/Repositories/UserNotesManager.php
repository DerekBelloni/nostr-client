<?php

namespace App\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UserNotesManager
{
    public function processUserNotes($user_note)
    {
        $note_content = $user_note[2];
        return $this->formatTimestamp($note_content);
    }

    private function formatTimestamp(&$note_content)
    {
        $note_content["created_at"] = Carbon::parse($note_content["created_at"])->format('Y-m-d H:i:s');
        return $note_content;
    }
}