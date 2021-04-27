<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure                  $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->user = Auth::user();
        if (isset($this->user)) {
            $locale = $this->user->language;
        } else {
            $locale = $request->route('locale') ?: Session::get('locale');
        }

        if ($locale) {
            App::setLocale($locale);

            /**
             * We need to store the locale in the session because we don't want to pass it to each component we create.
             * Also because we fetch the locale from the route but the url for the Livewire API endpoint only
             * contains the component name.
             */
            Session::put('locale', $locale);
        }

        return $next($request);
    }
}