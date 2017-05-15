@foreach ($notifications as $note)
	<div class="row">
		<div class="col-md-12 alert {{ $note->type=="alert" ? "alert-danger" : "alert-info" }}">
			{{ $note->message }}
		</div>
	</div>
@endforeach
