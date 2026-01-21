<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProposalDocument extends Model
{
    protected $fillable = ['proposal_id', 'uploaded_by', 'title', 'title_en', 'file_path', 'external_url', 'document_type', 'mime_type', 'file_size', 'sort_order'];

    protected $casts = ['file_size' => 'integer', 'sort_order' => 'integer'];

    const DOCUMENT_TYPES = [
        'pdf' => ['icon' => 'document', 'color' => 'red', 'name' => 'PDF', 'name_ja' => 'PDF'],
        'image' => ['icon' => 'photo', 'color' => 'green', 'name' => 'Image', 'name_ja' => '画像'],
        'spreadsheet' => ['icon' => 'table-cells', 'color' => 'emerald', 'name' => 'Spreadsheet', 'name_ja' => 'スプレッドシート'],
        'document' => ['icon' => 'document-text', 'color' => 'blue', 'name' => 'Document', 'name_ja' => '文書'],
        'link' => ['icon' => 'link', 'color' => 'purple', 'name' => 'External Link', 'name_ja' => '外部リンク'],
        'other' => ['icon' => 'paper-clip', 'color' => 'gray', 'name' => 'Other', 'name_ja' => 'その他'],
    ];

    const MAX_FILE_SIZE = 10 * 1024 * 1024;
    const ALLOWED_EXTENSIONS = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'jpg', 'jpeg', 'png', 'gif', 'webp'];

    public function proposal(): BelongsTo { return $this->belongsTo(Proposal::class); }
    public function uploader(): BelongsTo { return $this->belongsTo(User::class, 'uploaded_by'); }

    public function getUrlAttribute(): ?string {
        if ($this->external_url) return $this->external_url;
        if ($this->file_path) return Storage::disk('public')->url($this->file_path);
        return null;
    }

    public function getIsExternalAttribute(): bool { return !empty($this->external_url); }
    public function getIsLocalAttribute(): bool { return !empty($this->file_path); }
    public function getTypeConfigAttribute(): array { return self::DOCUMENT_TYPES[$this->document_type] ?? self::DOCUMENT_TYPES['other']; }
    public function getIconAttribute(): string { return $this->type_config['icon']; }
    public function getIconColorAttribute(): string { return $this->type_config['color']; }
    public function getTypeNameAttribute(): string { return app()->getLocale() === 'ja' ? $this->type_config['name_ja'] : $this->type_config['name']; }
    public function getLocalizedTitleAttribute(): string { return (app()->getLocale() === 'en' && $this->title_en) ? $this->title_en : $this->title; }

    public function getFileSizeFormattedAttribute(): string {
        if (!$this->file_size) return '';
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unit = 0;
        while ($size >= 1024 && $unit < count($units) - 1) { $size /= 1024; $unit++; }
        return round($size, 2) . ' ' . $units[$unit];
    }

    public function getIsPreviewableAttribute(): bool { return in_array($this->document_type, ['pdf', 'image']); }
    public function getIsImageAttribute(): bool { return $this->document_type === 'image'; }
    public function getIsPdfAttribute(): bool { return $this->document_type === 'pdf'; }

    public function getExternalDomainAttribute(): ?string {
        if (!$this->external_url) return null;
        $host = parse_url($this->external_url, PHP_URL_HOST);
        return $host ? preg_replace('/^www\./', '', $host) : null;
    }

    public function canBeDeletedBy(User $user): bool {
        return $this->uploaded_by === $user->id || $this->proposal->author_id === $user->id;
    }

    public function delete(): bool {
        if ($this->file_path && Storage::disk('public')->exists($this->file_path)) {
            Storage::disk('public')->delete($this->file_path);
        }
        return parent::delete();
    }

    public static function determineType(string $mimeType): string {
        if (str_starts_with($mimeType, 'image/')) return 'image';
        if ($mimeType === 'application/pdf') return 'pdf';
        if (in_array($mimeType, ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv'])) return 'spreadsheet';
        if (in_array($mimeType, ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'])) return 'document';
        return 'other';
    }
}
