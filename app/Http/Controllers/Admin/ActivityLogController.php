<?php

namespace App\Http\Controllers\Admin;

use App\Filters\UniversalSearchFilter;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ActivityLogController extends Controller
{
    public function index(Request $request): Response
    {
        DB::beginTransaction();

        try {
            $items = QueryBuilder::for(Activity::class)
                ->allowedFilters([
                    AllowedFilter::exact('causer_id'),
                    AllowedFilter::exact('subject_type'),
                    AllowedFilter::exact('event'),
                    AllowedFilter::exact('log_name'),
                    AllowedFilter::custom('search', new UniversalSearchFilter([
                        'description',
                        'subject_type',
                        'event',
                    ])),
                ])
                ->allowedSorts(['created_at', 'event', 'subject_type'])
                ->with('causer')
                ->latest()
                ->paginate($this->limit())
                ->withQueryString();

            // Transform data to include causer name
            $items->through(function ($activity) {
                $activity->causer_name = $activity->causer?->full_name ?? 'System';
                $activity->subject_name = class_basename($activity->subject_type ?? 'Unknown');
                return $activity;
            });

            DB::commit();

            return Inertia::render('Admin/ActivityLog/Index', [
                'items' => $items,
                'filters' => $request->only(['filter', 'sort']),
                'events' => ['created', 'updated', 'deleted'],
                'subjectTypes' => Activity::distinct()->pluck('subject_type')
                    ->map(fn($type) => [
                        'value' => $type,
                        'label' => class_basename($type),
                    ])->values(),
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->back()->withError($e->getMessage());
        }
    }

    public function show(Activity $activity): Response
    {
        $activity->load('causer', 'subject');

        return Inertia::render('Admin/ActivityLog/Show', [
            'activity' => $activity,
        ]);
    }
}
