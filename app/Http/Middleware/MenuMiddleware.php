<?php

namespace App\Http\Middleware;

use App\Models\Page;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MenuMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        Inertia::share('menu', Page::getMenu());
        //Inertia::share('bottomMenu', Page::getBottomMenu());
        //Inertia::share('aboutUsPageType', Page::PAGE_TYPE_ABOUTUS);
        //Inertia::share('tripRequestPageType', Page::PAGE_TYPE_TRIPREQUEST);

        //Inertia::share('OurOriginStoryPage', Page::findByType(Page::PAGE_TYPE_OURORIGINALSTORY));
        //Inertia::share('MeetTheTeamPage', Page::findByType(Page::PAGE_TYPE_MEETTHETEAM));

        return $next($request);
    }
}
