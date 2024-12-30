<?php

namespace App\Nova;

use App\Models\Page as PageModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Slug;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Mostafaznv\NovaCkEditor\CkEditor;
use Outl1ne\NovaSortable\Traits\HasSortableRows;
use Trin4ik\NovaSwitcher\NovaSwitcher;

class PageResource extends Resource
{
    use HasSortableRows;

    public static $model = PageModel::class;

    public static $title = 'title';

    public static $search = [
        'id', 'title'
    ];

    public static $group = 'Content';

    public static function singularLabel()
    {
        return __('Page');
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        $sortability = static::getSortability($request);

        if (! empty($sortability->model)) {
            // Make sure we are querying the same table, which might not be the case
            // in some complicated relationship views
            if ($request->viaManyToMany()) {
                $tempModel = $request->findParentModel()->{$request->viaRelationship}()->first();
                $sortabilityTable = ! empty($tempModel) ? $tempModel->getTable() : null;
            } else {
                $sortabilityTable = $sortability->model->getTable();
            }

            if (empty($request->viaResource())) {
                $query->where('parent_id', null)->orWhere('parent_id', 0);
            }

            if ($query->getQuery()->from === $sortabilityTable) {
                $shouldSort = true;
                if (empty($sortability->sortable)) {
                    $shouldSort = false;
                }
                // Allow sorting on both index and pivot
                // if ($sortability->sortOnBelongsTo && empty($request->viaResource())) $shouldSort = false;
                if ($sortability->sortOnHasMany && empty($request->viaResource())) {
                    $shouldSort = false;
                }

                if (empty($request->get('orderBy')) && $shouldSort) {
                    $query->getQuery()->orders = [];
                    $direction = static::getOrderByDirection($sortability->sortable);
                    $queryColumn = "{$sortability->model->getTable()}.{$sortability->model->determineOrderColumnName()}";

                    return $query->orderBy($queryColumn, $direction);
                }
            }
        }

        return $query;
    }

    public static function label()
    {
        return __('Pages (Menu)');
    }

    public static function afterCreate(NovaRequest $request, Model $model): void
    {
        $model->clearCache($model);
    }

    public static function afterUpdate(NovaRequest $request, Model $model): void
    {
        $model->clearCache($model);
    }

    public static function afterDelete(NovaRequest $request, Model $model): void
    {
        $model->clearCache($model);
    }

    public function fields(Request $request): array
    {
        return [
            ID::make()->sortable(),
            //BelongsTo::make('Parent', 'parent', 'App\Nova\PageResource')->default(0)->nullable()->hideFromIndex(), //comment if no sub-records
            NovaSwitcher::make('Is published', 'active')->confirm('Are you sure?')->onlyOnIndex(),
            Boolean::make('Is published', 'active')->hideFromIndex()->default('1'),
            NovaSwitcher::make('Hide from menu', 'hide')->help('The page will still be able to be opened in a browser')->confirm('Are you sure?')->onlyOnIndex(),
            Boolean::make('Hide from menu', 'hide')->help('The page will still be able to be opened in a browser')->hideFromIndex(),
            Text::make('Title', 'title')->sortable()->rules('required'),
            Slug::make('Slug', 'slug')->from('title')->rules('required')->hideFromIndex(),
            Text::make('External link')->hideFromIndex()->placeholder('Internal or external URL'),
            Boolean::make('Open in a new tab', 'blank')->hideFromIndex(),
            Textarea::make('Head line', 'headline')->sortable()->help('Shown if presented'),
            CkEditor::make('Body', 'body')->hideFromIndex()->fullWidth()->stacked(),
            Heading::make('Meta'),
            Text::make('Meta title', 'meta_title')->hideFromIndex(),
            Textarea::make('Meta description', 'meta_description'),
            Heading::make('System'),
            Select::make('Page Type', 'type')->options([
                PageModel::PAGE_TYPE_HOME => 'Home',
                //PageModel::PAGE_TYPE_ABOUT => 'About',
                PageModel::PAGE_TYPE_CONTACTS => 'Contacts',
                /*PageModel::PAGE_TYPE_FAQ => 'FAQ',
                PageModel::PAGE_TYPE_PROJECTS => 'Projects',
                PageModel::PAGE_TYPE_SERVICES => 'Services',
                PageModel::PAGE_TYPE_TESTIMONIALS => 'Testimonials',*/
                PageModel::PAGE_TYPE_404 => '404',
            ])->hideFromIndex()->nullable()->displayUsingLabels(),
            HasMany::make('Sub-records', 'children', 'App\Nova\PageResource')//comment if no sub-records
                ->showOnDetail(function (NovaRequest $request, $resource) {
                    return ! $resource->parent_id;
                }),
        ];
    }
}
