<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'documentable_type',
        'documentable_id',
        'document_type',
        'title',
        'description',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
        'uploaded_by',
        'visible_to_customer',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'visible_to_customer' => 'boolean',
    ];

    public function scopeVisibleToCustomer($query)
    {
        return $query->where('visible_to_customer', true);
    }

    public function documentable()
    {
        return $this->morphTo();
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get file URL
     */
    public function getUrl()
    {
        return Storage::url($this->file_path);
    }

    /**
     * Get file size in human-readable format
     */
    public function getFileSizeFormatted()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Check if file is an image
     */
    public function isImage()
    {
        return in_array($this->mime_type, [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/webp',
        ]);
    }

    /**
     * Check if file is a PDF
     */
    public function isPdf()
    {
        return $this->mime_type === 'application/pdf';
    }

    /**
     * Delete file from storage
     */
    public function deleteFile()
    {
        if (Storage::exists($this->file_path)) {
            Storage::delete($this->file_path);
        }
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($document) {
            $document->deleteFile();
        });
    }
}
