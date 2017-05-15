<?php

namespace Portfolio\Http\ViewComposers;

use Illuminate\View\View;

class NotificationComposer
{


    public function __construct()
    {
				
    }


    public function compose(View $view)
    {
			$notifications=\Portfolio\Notification::where(['viewed'=>0])->get();
			\Portfolio\Notification::where([ 'viewed'=> 0 ])->update(['viewed'=>1]);
      $view->with('notifications', $notifications);
    }
}
