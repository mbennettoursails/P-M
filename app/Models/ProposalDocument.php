<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProposalDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'proposal_id',
        'uploaded_by',
        'title',
        'file_path',
        'external_url',
        'document_type',
        'mime_type',
        'file_size',
        'sort_order',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'sort_order' => 'integer',
    ];

    // File type configurations
    const FILE_TYPES = [
        'pdf' => ['icon' => 'document-text', 'color' => 'red'],
        'doc' => ['icon' => 'document', 'color' => 'blue'],
        'docx' => ['icon' => 'document', 'color' => 'blue'],
        'xls' => ['icon' => 'table-cells', 'color' => 'green'],
        'xlsx' => ['icon' => 'table-cells', 'color' => 'green'],
        'csv' => ['icon' => 'table-cells', 'color' => 'green'],
        'ppt' => ['icon' => 'presentation-chart-bar', 'color' => 'orange'],
        'pptx' => ['icon' => 'presentation-chart-bar', 'color' => 'orange'],
        'txt' => ['icon' => 'document-text', 'color' => 'gray'],
        'md' => ['icon' => 'document-text', 'color' => 'gray'],
        'jpg' => ['icon' => 'photo', 'color' => 'purple'],
        'jpeg' => ['icon' => 'photo', 'color' => 'purple'],
        'png' => ['icon' => 'photo', 'color' => 'purple'],
        'gif' => ['icon' => 'photo', 'color' => 'purple'],
        'webp' => ['icon' => 'photo', 'color' => 'purple'],
    ];

    const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB
    const MAX_IMAGE_SIZE = 5 * 1024 * 1024; // 5MB

    // ─────────────────────────────────────────────────────────────
    // RELATIONSHIPS
    // ─────────────────────────────────────────────────────────────

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // ─────────────────────────────────────────────────────────────
    // ACCESSORS
    // ─────────────────────────────────────────────────────────────

    public function getIsFileAttribute(): bool
    {
        return $this->document_type === 'file';
    }

    public function getIsLinkAttribute(): bool
    {
        return $this->document_type === 'link';
    }

    public function getExtensionAttribute(): ?string
    {
        if ($this->file_path) {
            return strtolower(pathinfo($this->file_path, PATHINFO_EXTENSION));
        }
        return null;
    }

    public function getFileTypeConfigAttribute(): array
    {
        $extension = $this->extension;
        return self::FILE_TYPES[$extension] ?? ['icon' => 'document', 'color' => 'gray'];
    }

    public function getIconAttribute(): string
    {
        if ($this->is_link) {
            return 'link';
        }
        return $this->file_type_config['icon'];
    }

    public function getColorAttribute(): string
    {
        if ($this->is_link) {
            return 'blue';
        }
        return $this->file_type_config['color'];
    }

    public function getFileSizeFormattedAttribute(): string
    {
        if (!$this->file_size) {
            return '';
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;

        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }

        return round($bytes, 1) . ' ' . $units[$unitIndex];
    }

    public function getDownloadUrlAttribute(): ?string
    {
        if ($this->is_link) {
            return $this->external_url;
        }

        if ($this->file_path) {
            return Storage::disk('public')->url($this->file_path);
        }

        return null;
    }

    public function getIsImageAttribute(): bool
    {
        return in_array($this->extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }

    // ─────────────────────────────────────────────────────────────
    // METHODS
    // ─────────────────────────────────────────────────────────────

    public function canUserDelete(User $user): bool
    {
        // Uploader can delete
        if ($user->id === $this->uploaded_by) {
            return true;
        }

        // Proposal author can delete any document
        if ($user->id === $this->proposal->author_id) {
            return true;
        }

        return false;
    }

    public function deleteFile(): bool
    {
        if ($this->is_file && $this->file_path) {
            Storage::disk('public')->delete($this->file_path);
        }

        return $this->delete();
    }

    // ─────────────────────────────────────────────────────────────
    // STATIC HELPERS
    // ─────────────────────────────────────────────────────────────

    public static function getAllowedExtensions(): array
    {
        return array_keys(self::FILE_TYPES);
    }

    public static function getAllowedMimeTypes(): array
    {
        return [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/csv',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain',
            'text/markdown',
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
        ];
    }
}
