<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
				
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">

        <title>Sam Hutchinson's Portfolio</title>
        <style>
            html, body {
                background-col oxo-cellor: #fff;
                col oxo-cellor: #000;
                font-weight: 100;
                height: 100vh;
                margin: 0px;
            }

            .main-view {
                align-items: center;
                display: flex;
                justify-content: center;
                height: 100vh;
                position: relative;
            }
            
            .content{
							text-align:center;
						}
						
						.oxo-cell{
							border-width:1px;
							border-color:#000;
							border-style:solid;
							text-align:center;
							line-height:33vh;
						}
						
						@media (orientation:landscape){
							.oxo-cell{
								font-size:20vh;
							}
							.winner-box{
								font-size:20vh;
							}
						}
						
						@media (orientation:portrait){
							.oxo-cell{
								font-size:20vw;
							}
							.winner-box{
								font-size:20vw;
							}
						}
						
						.oxo-row{
							height:33vh;
						}
						
						.last{
							color:#F00;
						}
						
						.winner-container{
							position:fixed;
							height:100vh;
							width:100vw;
							background-color:rgba(0,0,0,0.8);

							z-index:1;
						}
						.winner-box{
							color:#FFF;
							line-height:100vh;
							text-align:center;
						}
						.hidden{
							display:none;
						}
						
        </style>

    </head>
    <body>
			<div class="winner-container hidden">
				<div class="winner-box"></div>
			</div>
			<div class="container-fluid">
				@for ($i=0; $i<9; $i++)
					@if ($i % 3 ==0)
						<div class="row oxo-row">
					@endif
					<div class="col oxo-cell" id="cell-{{$i}}"></div>
					@if (($i+1) % 3 ==0)
						</div>
					@endif
				@endfor
			</div>
      <script src="https://code.jquery.com/jquery-3.1.1.slim.min.js" integrity="sha384-A7FZj7v+d/sdmMqp/nOQwliLvUsJfDHW+k9Omg/a/EheAdgtzNs3hpfag6Ed950n" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>
        <script type="text/javascript">
					var player='X';
					
					var lines=[//This is a map of the possible winning lines
						[0,1,2],
						[3,4,5],
						[6,7,8],
						[0,3,6],
						[1,4,7],
						[2,5,8],
						[0,4,8],
						[2,4,6]
					];
					
					function checkWinner(){
						var ret=false;
						lines.forEach(function (cur){
							var firstCell=$('#cell-'+cur[0]);
							console.log('cell-'+cur[0]);
							if((firstCell.html()!='') &&
									(firstCell.html()==$('#cell-'+cur[1]).html()) &&
									(firstCell.html()==$('#cell-'+cur[2]).html())){
								firstCell.addClass('last');
								$('#cell-'+cur[1]).addClass('last');
								$('#cell-'+cur[2]).addClass('last');
								ret=true;
							}
						});
						
						return ret;
					}
					
					function reset(){
						player='X';
						$('.oxo-cell').html('');
						$('.winner-container').addClass('hidden');
						//We don't bother resetting the last class as the cells are empty anyway. 
					}
					$(document).ready(function(){
						$('.oxo-cell').click(function(){
							var jthis=$(this);
							if(jthis.html()!=''){
								return;
							}
							$('.oxo-cell').removeClass('last');
							
							jthis.html(player);
							jthis.addClass('last');
							
							if(checkWinner()){
								$('.winner-box').html(player+' wins!');
								$('.winner-container').removeClass('hidden');
							}else{
								if(player=='X'){
									player='O';
								}else{
									player='X';
								}
							}
							
							$('.winner-container').click(function(){
								reset();
							});
						});
					});
        </script>
    </body>
</html>
