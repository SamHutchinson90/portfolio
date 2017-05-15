<?php

namespace Portfolio\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Portfolio;

class CalendarController extends Controller
{
	
			protected static $actions=[
				'none'=>array(CalendarController::class, 'action_none'),
				'remind'=>array(CalendarController::class, 'action_reminder'),
				'email'=>array(CalendarController::class, 'action_email'),
				'script'=>array(CalendarController::class, 'action_script'),
			];
         
     public function show($month=null){
			if(is_null($month) || !preg_match('/\d\d\d\d-\d\d/', $month)){//If the month doesn't match the expected format we ignore it, preventing info leakage.
				$month=time();
			}else{
				$month=strtotime($month);
			}
			$rawEvents=Portfolio\Event::whereBetween('time', [date('Y-m-01', $month), date('Y-m-01', strtotime('+1 month', $month))])->orderBy('time', 'asc')->get();
			
			$firstDay=date('N', strtotime(date('Y-m-01', $month)));//1-7 Mon-Sun. First day of the month.
			$monthLength=date('t', $month);
			$monthName=date('F', $month);
			$curMonth=date('Y-m', $month);
			$prevMonth=url('calendar/'.date('Y-m', strtotime('-1 month', $month)));
			$nextMonth=url('calendar/'.date('Y-m', strtotime('+1 month', $month)));
			
			$events=array();
			foreach($rawEvents as $event){
				$day=(int)date('d', strtotime($event->time));
				if(!isset($events[$day])){
					$events[$day]=array();
				}
				array_push($events[$day], $event);
			}
			
			$unsafeTriggers=isset($_ENV['UNSAFE_TRIGGERS']) && !!$_ENV['UNSAFE_TRIGGERS'];
			
			$reminders=Portfolio\Event::whereBetween('time', [date('Y-m-d'), date('Y-m-d', strtotime('+1 day'))])->where('action', 'remind')->where('action_complete', 0);
			$reminders->update(['action_complete' => 1]);
			$reminders=$reminders->orderBy('time', 'asc')->get();
			return view('calendar', [
				'root'=>findRoot(),
				'events'=>$events,
				'firstDay'=>$firstDay,
				'month'=>$monthName,
				'curMonth'=>$curMonth,
				'monthLength'=>$monthLength,
				'prevMonth'=>$prevMonth,
				'nextMonth'=>$nextMonth,
				'unsafeTriggers'=>$unsafeTriggers,
				'reminders'=>$reminders,
				'scriptList'=>$this->getScriptList(),
			]);
		 }
		 
		 
		 public function add(Request $request){
			
			$v = \Validator::make($request->all(), [
				'name' => 'required|max:255',
				'description' => 'max:1000',
				'day'=>'required',
				'time'=>'required',
				'month'=>'required',
				'action'=>'required|' . Rule::in($this->getActionList()),
			]);
			$v->sometimes('extra', 'required', function($input){
				return $input->action=='reminder' || $input->action=='email' || $input->action==['script'];
			}); 
			$v->sometimes('extra|', Rule::in($this->getScriptList()), function($input){
				return $input->action=='script';
			});
			
			if($v->fails()){
				print "Error";
				return;
			}
			
			$input=$request->input();
			$event=new \Portfolio\Event();
			
			$event->name=$input['name'];
			$event->description=$input['description'];
			$event->time=$input['month'].'-'.$input['day'].' '.$input['time'];
			$event->action=$input['action'];
			$event->action_data=$input['extra'];
			$event->created_by=$request->ip();
			$event->action_complete=(time()>strtotime($event->time)) ;//actions in the past are already considered completed and never actually run.
			$event->save();
			
		 }
		 
		 #With a larger project the actions could be factored out into their own class but with the small size it's better to keep everything in one place.
		 
		 public static function runActions(){//This function must be reenterant (i.e. just because the script for this minute is running doesn't mean the script for last minute has finished).
		  $complete=false;
		  $event=null;
		  $done=array();
			while(!$complete){
				
				\DB::transaction(function() use(&$complete, &$event, &$done) {
					$event=Portfolio\Event::where([
						['time', '<=', date('Y-m-d H:i:s')], //We don't use NOW() here to ensure that there's never a time mismatch on the database sever. PHPs time is the single point of truth.
						['action_complete', 0]
					])->whereNotIn('id', $done) //We never select the same row twice in the same invocation, to prevent repeatedly failing tasks from taking over.
					->lockForUpdate()->limit('1')->get();//We take one row at a time, locking it and update it to be complete, for reenterancy.
					if($event->count()==0){
						$complete=true;
						return;
					}
					$event=$event[0];
					
					$done[]=$event->id;
					
					Portfolio\Event::where('id', $event->id)->update(['action_complete'=> 1]); #We first mark the task as complete. This prevents a later invokation from attempting a task that's already running.
				}); //We end our transaction (and release the lock), as soon as we've marked it for reenterancy
				
				if(!$complete){
					try{
						call_user_func(self::$actions[$event->action], $event);
					}catch(\Exception $e){
						\Log::error("Event #".$event->id." failed with:".$e->getMessage());
						//Something went wrong. Mark the task as incomplete and go on to the next, a future invocation will try again.
						Portfolio\Event::where('id', $event->id)->update(['action_complete'=> 0]);
					}
				}
			}
			return;
		 }
		 
		public static function action_none($event){
			return;
		}
		 
		public static function action_reminder($event){
				$note=new \Portfolio\Notification();
				$note->type="reminder";
				$note->message="Reminder:" .$event->action_data;
				$note->viewed=0;
				$note->save();
		}
		 
		public static function action_email($event){
			if(\Config::get('app.calendar.unsafe_events')){
				mail(\Config::get('app.calendar.event_email'), "Reminder of ".$event->name, $event->extra);
			}
			
			return;
		}
		
		public static function action_script($event){
			if(\Config::get('app.calendar.unsafe_events')){
				$scriptdir=\Config::get('app.calendar.scriptdir');
				exec($scriptdir."/".escapeshellcmd($event->action_data));
			}
		}
		 
		 public function getActionList(){
				return array_keys($this::$actions);
		 }
		 
		 
		 public function getScriptList(){
			$scriptdir=\Config::get('app.calendar.scriptdir');
			$dir=opendir($scriptdir);
			$scripts=[];
			while (false !== ($entry = readdir($dir))) {
        if ($entry != "." && $entry != ".." && is_executable($scriptdir.$entry)) {
						$scripts[]=$entry;
        }
			}
			return $scripts;
		 }
		 
}
