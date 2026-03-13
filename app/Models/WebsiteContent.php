<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteContent extends Model
{
    protected $table = 'website_content';

    protected $fillable = ['section', 'key', 'value', 'type'];

    public $timestamps = true;

    /**
     * Get a single value for a section + key.
     */
    public static function get(string $section, string $key, $default = null): ?string
    {
        $record = static::where('section', $section)->where('key', $key)->first();
        return $record ? $record->value : $default;
    }

    /**
     * Set a value (upsert).
     */
    public static function set(string $section, string $key, $value, string $type = 'text'): void
    {
        static::updateOrCreate(
            ['section' => $section, 'key' => $key],
            ['value' => $value, 'type' => $type]
        );
    }

    /**
     * Get all key → value pairs for a section.
     */
    public static function getSection(string $section): array
    {
        return static::where('section', $section)->pluck('value', 'key')->toArray();
    }

    /**
     * Get all sections as nested array: section → [key → value].
     */
    public static function getAllSections(): array
    {
        $all = static::all();
        $result = [];
        foreach ($all as $row) {
            $result[$row->section][$row->key] = $row->value;
        }
        return $result;
    }
}
