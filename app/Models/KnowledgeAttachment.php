<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class KnowledgeAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'article_id',
        'filename',
        'path',
        'disk',
        'mime_type',
        'size',
        'title',
        'description',
        'sort_order',
        'type',
        'download_count',
    ];

    protected $casts = [
        'size' => 'integer',
        'sort_order' => 'integer',
        'download_count' => 'integer',
    ];

    /**
     * File type mappings based on MIME type
     */
    public const TYPE_MAPPINGS = [
        'application/pdf' => 'pdf',
        'application/msword' => 'document',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'document',
        'application/vnd.ms-excel' => 'spreadsheet',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'spreadsheet',
        'application/vnd.ms-powerpoint' => 'presentation',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'presentation',
        'image/jpeg' => 'image',
        'image/png' => 'image',
        'image/gif' => 'image',
        'image/webp' => 'image',
        'video/mp4' => 'video',
        'video/webm' => 'video',
        'audio/mpeg' => 'audio',
        'audio/wav' => 'audio',
        'text/plain' => 'text',
        'text/csv' => 'spreadsheet',
        'application/zip' => 'archive',
    ];

    /**
     * Type labels in Japanese
     */
    public const TYPE_LABELS = [
        'pdf' => 'PDF',
        'document' => '文書',
        'spreadsheet' => 'スプレッドシート',
        'presentation' => 'プレゼンテーション',
        'image' => '画像',
        'video' => '動画',
        'audio' => '音声',
        'text' => 'テキスト',
        'archive' => '圧縮ファイル',
        'other' => 'その他',
    ];

    /**
     * Icons for file types
     */
    public const TYPE_ICONS = [
        'pdf' => 'document-text',
        'document' => 'document',
        'spreadsheet' => 'table-cells',
        'presentation' => 'presentation-chart-bar',
        'image' => 'photo',
        'video' => 'video-camera',
        'audio' => 'musical-note',
        'text' => 'document-text',
        'archive' => 'archive-box',
        'other' => 'paper-clip',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($attachment) {
            if (empty($attachment->uuid)) {
                $attachment->uuid = (string) Str::uuid();
            }
            
            // Auto-detect type from MIME type
            if (empty($attachment->type)) {
                $attachment->type = self::TYPE_MAPPINGS[$attachment->mime_type] ?? 'other';
            }
        });

        static::deleting(function ($attachment) {
            // Delete the actual file when the attachment is deleted
            if (Storage::disk($attachment->disk)->exists($attachment->path)) {
                Storage::disk($attachment->disk)->delete($attachment->path);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    // ─────────────────────────────────────────────────────────────
    // RELATIONSHIPS
    // ─────────────────────────────────────────────────────────────

    public function article(): BelongsTo
    {
        return $this->belongsTo(KnowledgeArticle::class, 'article_id');
    }

    // ─────────────────────────────────────────────────────────────
    // ACCESSORS
    // ─────────────────────────────────────────────────────────────

    public function getDisplayTitleAttribute(): string
    {
        return $this->title ?? $this->filename;
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPE_LABELS[$this->type] ?? self::TYPE_LABELS['other'];
    }

    public function getTypeIconAttribute(): string
    {
        return self::TYPE_ICONS[$this->type] ?? self::TYPE_ICONS['other'];
    }

    public function getHumanSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 1) . ' ' . $units[$i];
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function getDownloadUrlAttribute(): string
    {
        return route('knowledge.attachment.download', $this);
    }

    public function getExtensionAttribute(): string
    {
        return pathinfo($this->filename, PATHINFO_EXTENSION);
    }

    public function getIsImageAttribute(): bool
    {
        return $this->type === 'image';
    }

    public function getIsPreviewableAttribute(): bool
    {
        return in_array($this->type, ['image', 'pdf']);
    }

    // ─────────────────────────────────────────────────────────────
    // METHODS
    // ─────────────────────────────────────────────────────────────

    public function incrementDownloadCount(): void
    {
        $this->increment('download_count');
    }

    public function getFileContents(): ?string
    {
        if (Storage::disk($this->disk)->exists($this->path)) {
            return Storage::disk($this->disk)->get($this->path);
        }
        return null;
    }
}
