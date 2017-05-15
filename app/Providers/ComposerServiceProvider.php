<?php

namespace Portfolio\Providers;


use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ComposerServiceProvider extends ServiceProvider
{
	
    public function boot()
    {
			View::composer('note', 'Portfolio\Http\ViewComposers\NotificationComposer');
    }
}
