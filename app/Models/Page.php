<?php

namespace App\Models;

use App\Brand3000\Common;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Page extends Model implements Sortable
{
    use Common, SortableTrait;

    const DEFAULT_LANGUAGE = 'en';

    private const CACHE_MENU_KEY = 'menu_pages'; //Updatable checkboxes from the index page

    private const CACHE_KEY_BY_SLUG = 'page_slug_%s_%s';

    private const CACHE_KEY_BY_TYPE = 'page_type_%s_%s';

    private const CACHE_KEY_BY_ID = 'page_id_%s_%s';

    const PAGE_TYPE_HOME = 'Home';

    const PAGE_TYPE_CONTACTS = 'Contacts';

    const PAGE_TYPE_404 = '404';

    public $sortable = [
        'order_column_name' => 'order',
        'sort_when_creating' => true,
        'sort_on_has_many' => false,
    ];

    public $toggleUpdatable = ['active', 'hide', 'blank'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (request('viaResourceId')) {
            $this->sortable['sort_on_has_many'] = true;
        }
    }

    public static function getMenu($language = self::DEFAULT_LANGUAGE): Collection
    {
        return Cache::rememberForever(
            sprintf(self::CACHE_MENU_KEY, $language),
            fn () => self::select(
                'id', 'parent_id', 'hide', 'title', 'slug', 'external_link', 'blank', 'type'
            )->where('active', true)
                ->where('parent_id', 0)
                ->where('hide', false)
                ->orderBy('order')
                ->get()
        );
    }

    public static function findBySlug($slug, $language = self::DEFAULT_LANGUAGE): ?self
    {
        return Cache::rememberForever(
            sprintf(self::CACHE_KEY_BY_SLUG, $slug, $language),
            fn () => self::query()
                ->where('slug', $slug)
                ->where('active', true)
                ->first()
        );
    }

    public static function findByType($type, $language = self::DEFAULT_LANGUAGE): ?self
    {
        return Cache::rememberForever(
            sprintf(self::CACHE_KEY_BY_TYPE, $type, $language),
            fn () => self::query()
                ->where('type', $type)
                ->where('active', true)
                ->first()
        );
    }

    public static function findById($id, $language = self::DEFAULT_LANGUAGE): ?self
    {
        return Cache::rememberForever(
            sprintf(self::CACHE_KEY_BY_ID, $id, $language),
            fn () => self::query()
                ->where('id', $id)
                ->first()
        );
    }

    public function parent()
    {
        return $this->belongsTo($this);
    }

    public function children()
    {
        return $this->hasMany($this, 'parent_id');
    }

    public function clearCache($model, $language = self::DEFAULT_LANGUAGE): void
    {
        Cache::forget(sprintf(self::CACHE_KEY_BY_SLUG, $model->slug, $language));
        Cache::forget(sprintf(self::CACHE_KEY_BY_TYPE, $model->type, $language));
        Cache::forget(self::CACHE_MENU_KEY);
    }
}
