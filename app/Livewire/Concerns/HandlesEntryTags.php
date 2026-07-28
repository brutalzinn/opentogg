<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Facades\Auth;

trait HandlesEntryTags
{
    private array $parsedTagNames = [];

    /**
     * Parse #tags out of the given text, storing them for a later sync,
     * and return the description with the #tags stripped out.
     */
    private function extractAndSyncTags(?string $text): ?string
    {
        if (! $text) {
            return $text;
        }

        preg_match_all('/#([\w-]+)/', $text, $matches);
        $this->parsedTagNames = array_unique(array_map('strtolower', $matches[1]));

        return trim(preg_replace('/#[\w-]+/', '', $text));
    }

    /**
     * Sync the tags parsed by extractAndSyncTags() onto the given entry,
     * creating any that don't exist yet for the current user.
     */
    private function syncTagsToEntry($entry): void
    {
        if (empty($this->parsedTagNames)) {
            $entry->tags()->detach();

            return;
        }

        $user = Auth::user();
        $tagIds = [];

        foreach ($this->parsedTagNames as $tagName) {
            $tag = $user->tags()->firstOrCreate(['name' => $tagName]);
            $tagIds[] = $tag->id;
        }

        $entry->tags()->sync($tagIds);
    }

    /**
     * Rebuild a description string with its #tags appended, for display in
     * an input field.
     */
    private function rebuildDescriptionWithTags(?string $description, ?array $tagNames = null): string
    {
        $tags = $tagNames ?? $this->parsedTagNames;
        $parts = [];
        if ($description) {
            $parts[] = $description;
        }
        foreach ($tags as $tag) {
            $parts[] = '#'.$tag;
        }

        return implode(' ', $parts);
    }
}
