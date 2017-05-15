<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

				<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">
				<link rel="stylesheet" href="{{$root}}/components/clockpicker/bootstrap-clockpicker.min.css">

        <title>Sam Hutchinson's Portfolio</title>
        <style>
            html, body {
                background-color: #fff;
                color: #000;
                font-weight: 100;
                height: 100vh;
                margin: 0px;
            }

            .main-view {
								padding-top:1vh;
                display: flex;
                justify-content: center;
                height: 100vh;
            }
            
            .title{
								text-align: center;
								font-size:3vw;
						}
						
						.title a{
							color:#000;
						}
						
						.title a:hover{
							text-decoration:none;
						}
						
            .day-header{
							font-size:2vw;
							border-width:1px;
							border-style:solid;
						}
						
						.day{
							font-size:1vw;
							border-width:1px;
							border-style:solid;
							height:10vh;
						}
						
						.dummy{
							border-color:#AAAAAA;
						}
						
						.day-event{
							border-color:#FF0000;
						}
						
						.day-event:hover .popup{
							display:block;
						}
						
						.day-event .popup{
							display:none;
							
						}
						
						.event {
							margin:10px;
						}
						
						.popup{
							position:fixed;
							border-style:solid;
							border-width:1px;
							background-color:#FFF;
							z-index:1;
							padding:2px;
						}
						
						.reminder{
							
						}
						
						#event-form{
							display:none;
						}
						
						#description{
							width:50vw;
						}
						
						.day-selected{
							border-color:#00F;
						}
						
						#action-remind{
							display:none;
						}
						
						#action-email{
							display:none;
						}
						#action-script{
							display:none;
						}
						
						#save-button{
							text-decoration:underline;
						}
						
						@media (max-width: 750px){
							.headers{
								display:none;
							}
							
							.dummy {
								display:none;
							}
						}
						
        </style>
    </head>
    <body>
    <div class="main-view">
			<div class="container">
				
				@include('note')
				<div class="row">
				<div class="col-md-7 title	">
					<a href="{{ $prevMonth }}"><<</a>
					{{ $month }}
					<a href="{{ $nextMonth }}">>></a>
				</div>
				</div>
				<div class="row headers">
					<div class="col-md-1 day-header">
						Mon
					</div>
					<div class="col-md-1 day-header">
						Tue
					</div>
					<div class="col-md-1 day-header">
						Wed
					</div>
					<div class="col-md-1 day-header">
						Thu
					</div>
					<div class="col-md-1 day-header">
						Fri
					</div>
					<div class="col-md-1 day-header">
						Sat
					</div>
					<div class="col-md-1 day-header">
						Sun
					</div>
				</div>
					@for ($i=1; $i<$firstDay; $i++)
						@if($i%7==1)
							<div class="row">
						@endif
						<div class="col-md-1 dummy day">
							&nbsp;
						</div>
					@endfor
					@for ($i=$firstDay; $i-$firstDay<$monthLength; $i++)
						@if($i%7==1)
							<div class="row">
						@endif
							@if (isset($events[$i+1-$firstDay]))
								<div class="col-md-1 day day-event real-day" id="day-{{ $i-$firstDay+1 }}">
									
								{{ $i }}
									<div class="popup">
										@foreach ($events[$i+1-$firstDay] as $event)
											<div class="event">
												{{ $event->name }}<br />
												{{ $event->description }}<br />
												Time: {{ date("H:i:s", strtotime($event->time)) }}<br />
												Action: {{ $event->action }}
												
											</div>
										@endforeach
									</div>
							@else
								<div class="col-md-1 day real-day" id="day-{{ $i-$firstDay+1 }}">
									
								{{ $i-$firstDay+1 }}
							@endif
						</div>
						@if($i%7==0)
							</div>
						@endif
					@endfor
					@for($i=$monthLength+$firstDay; $i%7!=1; $i++)
						<div class="col-md-1 dummy day">
							&nbsp;
						</div>
					@endfor
					</div>
				<div class="row">
				
					<form id="event-form">
						<input type="hidden" name="_token" value="{{ csrf_token() }}">
						<input type="hidden" name="day" id="day-input" />
						<input type="hidden" name="month" value="{{$curMonth}}"  />
						<div class="input-group clockpicker">
							<input type="text" class="form-control" name="time" value="09:30" />
							<span class="input-group-addon">
								<span class="glyphicon glyphicon-time"></span>
							</span>
						</div>
						<input type="text" name="name" placeholder="Event name" maxlength="255"/><br />
						<input type="text" id="description" name="description" placeholder="A short description of the Event" maxlength="1000"/><br />
						<select name="action" id="action-select">
							<option value="none">None</option>
							<option value="remind">Remind</option>
							<option value="email">Email</option>
							<option value="script">Script</option>
						</select><br />
						
						<input name="extra" id="extra-input" type="text" placeholder="" />
						<select name="script" id="script-select">
							@foreach ($scriptList as $script)
								<option value="{{$script}}">{{$script}}</option>
							@endforeach
						</select>
						<br />
						<a id="save-button">Save</a>
					</form>
				</div>
			</div>
    </div>
    <script src="https://code.jquery.com/jquery-3.1.1.min.js" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>
		<script src="{{$root}}/components/clockpicker/bootstrap-clockpicker.min.js"></script>
		<script>
		$(document).ready(function(){
			$('.real-day').click(function(){
				var re = /^day-/;
				var day=$(this).attr('id').replace(/^day-/, ''); 
				$('.real-day').removeClass('day-selected');
				$(this).addClass('day-selected');
				$('#event-form #day-input').val(day);
				$('#event-form').show();
			});
			
			$('#action-select').change(function(){
				//reset
				$('#extra-input').hide();
				$('#script-select').hide();
				
				
				if($(this).val()=="email"){
					$('#extra-input').show();
					$('#extra-input').attr("placeholder", "Address");
				}else if($(this).val()=="remind"){
					$('#extra-input').show();
					$('#extra-input').attr("placeholder", "Message");
				}else if($(this).val()=="script"){
					$('#script-select').show();
				}
			});
			$('#action-select').change();
			
			$('#save-button').click(function(){
				$.ajax({
					type: 'POST',
					url:'{{$root}}/calendar/add', 
					data: $('#event-form').serialize(),
					success: function(data){
						alert(data);
					}
					
				});
			});
			$('.clockpicker').clockpicker();
					
		});
		</script>
    </body>
</html>
